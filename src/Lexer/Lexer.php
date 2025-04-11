<?php
namespace Sinnbeck\HtmlAst\Lexer;

class Lexer
{
    protected $input;
    protected $length;
    protected $position;
    protected $tokens = [];

    // List of void (self-closing) elements.
    protected $voidElements = [
        'area', 'base', 'br', 'col', 'embed', 'hr', 'img',
        'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr'
    ];

    public function __construct(string $input)
    {
        $this->input    = $input;
        $this->length   = strlen($input);
        $this->position = 0;
    }

    public static function make(string $input): self
    {
        return new self($input);
    }

    public function lex(): array
    {
        while ($this->position < $this->length) {
            if ($this->lookAhead('<!')) {
                if ($this->lookAhead('<!--')) {
                    $this->consumeComment();
                    continue;
                }
                $possibleDoctype = substr($this->input, $this->position, 9);
                if (stripos($possibleDoctype, '<!doctype') === 0) {
                    $this->consumeDoctype();
                    continue;
                }
            }
            if ($this->peek() === '<') {
                $this->consumeTag();
            } else {
                $this->consumeText();
            }
        }
        return $this->tokens;
    }

    protected function peek(int $offset = 0): ?string
    {
        if ($this->position + $offset < $this->length) {
            return $this->input[$this->position + $offset];
        }
        return null;
    }

    protected function lookAhead(string $str): bool
    {
        return substr($this->input, $this->position, strlen($str)) === $str;
    }

    protected function consume(int $length = 1): string
    {
        $result = substr($this->input, $this->position, $length);
        $this->position += $length;
        return $result;
    }

    protected function consumeDoctype(): void
    {
        $start = $this->position;
        while ($this->position < $this->length && $this->peek() !== '>') {
            $this->consume();
        }
        if ($this->peek() === '>') {
            $this->consume();
        }
        $value = substr($this->input, $start, $this->position - $start);
        $this->tokens[] = ['type' => TokenType::DOCTYPE, 'value' => $value];
    }

    protected function consumeComment(): void
    {
        $this->consume(4); // consumes '<!--'
        $end = strpos($this->input, '-->', $this->position);
        if ($end === false) {
            $this->position = $this->length;
        } else {
            $this->position = $end + 3;
        }
    }

    /**
     * Consume text until a '<' is encountered.
     * Only produces a token if the text contains at least one non-whitespace character.
     */
    protected function consumeText(): void
    {
        $start = $this->position;
        while ($this->position < $this->length && $this->peek() !== '<') {
            $this->position++;
        }
        $text = substr($this->input, $start, $this->position - $start);
        if (preg_match('/\S/', $text)) {
            $this->tokens[] = ['type' => TokenType::TEXT, 'value' => trim($text)];
        }
    }

    protected function consumeTag(): void
    {
        // Consume the opening '<'
        $this->consume();
        $tagType = TokenType::TAG_OPEN;
        if ($this->peek() === '/') {
            $tagType = TokenType::TAG_CLOSE;
            $this->consume();
        }
        $this->skipWhitespace();
        // Consume the tag name.
        $tagName = $this->consumeWhile(function ($ch) {
            return preg_match('/[A-Za-z0-9\:_\-]/', $ch);
        });
        if (trim($tagName) === '') { // Skip if tag name is empty (e.g. stray newline)
            while ($this->position < $this->length && $this->peek() !== '>') {
                $this->consume();
            }
            if ($this->peek() === '>') {
                $this->consume();
            }
            return;
        }
        $this->tokens[] = ['type' => $tagType, 'value' => $tagName];
        $this->skipWhitespace();
        // Process attributes.
        while (
            $this->position < $this->length &&
            $this->peek() !== '>' &&
            !$this->lookAhead('/>')
        ) {
            $startPos = $this->position;
            $this->consumeAttribute();
            $this->skipWhitespace();
            if ($this->position === $startPos) {
                $this->consume(); // safeguard to prevent infinite loops
            }
        }
        // Detect if the tag is a void element.
        $lowerTag = strtolower($tagName);
        if (in_array($lowerTag, $this->voidElements)) {
            if ($this->lookAhead('/>')) {
                $this->consume(2);
            } elseif ($this->peek() === '>') {
                $this->consume();
            }
            $this->tokens[] = ['type' => TokenType::TAG_SELF_CLOSE, 'value' => '/>'];
        } else {
            if ($this->lookAhead('/>')) {
                $this->consume(2);
                $this->tokens[] = ['type' => TokenType::TAG_SELF_CLOSE, 'value' => '/>'];
            } elseif ($this->peek() === '>') {
                $this->consume();
                $this->tokens[] = ['type' => TokenType::TAG_END, 'value' => '>'];
            }
        }
        // For raw tags (script, style), capture inner content and consume the closing tag.
        if ($tagType === TokenType::TAG_OPEN && in_array($lowerTag, ['script', 'style'])) {
            $this->consumeRawTextAndClosingTag($lowerTag);
        }
    }

    /**
     * Capture raw text up to the closing tag, then normalize its indentation.
     */
    protected function consumeRawTextAndClosingTag(string $tagName): void
    {
        $pattern = "</" . $tagName;
        $index = stripos($this->input, $pattern, $this->position);
        if ($index === false) {
            $rawText = substr($this->input, $this->position);
            $this->position = $this->length;
        } else {
            $rawText = substr($this->input, $this->position, $index - $this->position);
            $this->position = $index;
        }
        // Normalize raw text indentation.
        $rawText = $this->normalizeRawIndentation($rawText);
        $this->tokens[] = ['type' => TokenType::RAW, 'value' => $rawText];
        // Consume the closing tag so it is not tokenized.
        if ($this->lookAhead("</" . $tagName)) {
            $this->consume(strlen("</" . $tagName));
            $this->skipWhitespace();
            if ($this->peek() === '>') {
                $this->consume();
            }
        }
    }

    /**
     * Normalize the indentation of raw content:
     * - Split the content into lines.
     * - Remove any leading and trailing blank lines.
     * - Detect the common indent of the first non-empty line and remove that indent from all lines.
     */
    protected function normalizeRawIndentation(string $raw): string
    {
        $lines = preg_split('/\R/', $raw);

        // Remove leading empty lines.
        while (!empty($lines) && trim($lines[0]) === '') {
            array_shift($lines);
        }
        // Remove trailing empty lines.
        while (!empty($lines) && trim(end($lines)) === '') {
            array_pop($lines);
        }
        if (empty($lines)) {
            return '';
        }
        // Determine common indent from the first line.
        if (preg_match('/^( *)/', $lines[0], $matches)) {
            $commonIndent = $matches[1];
        } else {
            $commonIndent = '';
        }
        $indentLength = strlen($commonIndent);
        // Remove the common indent from all lines.
        foreach ($lines as &$line) {
            if (strlen($line) >= $indentLength && strpos($line, $commonIndent) === 0) {
                $line = substr($line, $indentLength);
            }
        }
        unset($line);
        // Rejoin lines with newline.
        return implode("\n", $lines);
    }

    protected function skipWhitespace(): void
    {
        while ($this->position < $this->length && ctype_space($this->peek())) {
            $this->consume();
        }
    }

    protected function consumeWhile(callable $condition): string
    {
        $result = '';
        while ($this->position < $this->length && $condition($this->peek())) {
            $result .= $this->consume();
        }
        return $result;
    }

    protected function consumeAttribute(): void
    {
        $this->skipWhitespace();
        $attrName = $this->consumeWhile(function ($ch) {
            return preg_match('/[A-Za-z0-9\-_]/', $ch);
        });
        if ($attrName !== '') {
            $this->tokens[] = ['type' => TokenType::ATTR_NAME, 'value' => $attrName];
        }
        $this->skipWhitespace();
        if ($this->peek() === '=') {
            $this->consume(); // consume '='
            $this->skipWhitespace();
            $attrValue = $this->consumeAttributeValue();
            $this->tokens[] = ['type' => TokenType::ATTR_VALUE, 'value' => $attrValue];
        }
    }

    protected function consumeAttributeValue(): string
    {
        $quote = $this->peek();
        if ($quote === '"' || $quote === "'") {
            $this->consume(); // consume opening quote
            $value = '';
            while ($this->position < $this->length && $this->peek() !== $quote) {
                $value .= $this->consume();
            }
            $this->consume(); // consume closing quote
            return $value;
        }
        return $this->consumeWhile(function ($ch) {
            return !ctype_space($ch) && $ch !== '>' && $ch !== '/';
        });
    }
}
