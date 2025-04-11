<?php

namespace Sinnbeck\HtmlAst\Ast;

use Sinnbeck\HtmlAst\Lexer\TokenType;

class Parser
{
    protected $tokens;
    protected $position;
    protected $length;

    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
        $this->position = 0;
        $this->length = count($tokens);
    }

    public static function make(array $tokens): self
    {
        return new self($tokens);
    }

    public function parse(): array
    {
        $nodes = [];
        while ($this->position < $this->length) {
            $node = $this->parseNode();
            if ($node !== null) {
                $nodes[] = $node;
            }
        }

        return $nodes;
    }

    protected function parseNode(): ?Node
    {
        $token = $this->peek();
        if (! $token) {
            return null;
        }
        if ($token['type'] === TokenType::DOCTYPE) {
            $this->advance();

            return new Node(NodeType::DOCTYPE, '', [], [], $token['value']);
        }
        if ($token['type'] === TokenType::TEXT) {
            $this->advance();

            return new Node(NodeType::TEXT, '', [], [], $token['value']);
        }
        if ($token['type'] === TokenType::RAW) {
            $this->advance();

            return new Node(NodeType::RAW, '', [], [], $token['value']);
        }
        if ($token['type'] === TokenType::TAG_OPEN) {
            return $this->parseElement();
        }
        if ($token['type'] === TokenType::TAG_CLOSE) {
            $this->consumeClosingTag();

            return null;
        }
        $this->advance();

        return null;
    }

    protected function parseElement(): Node
    {
        $openToken = $this->peek();
        $tagName = $openToken['value'];
        $this->advance(); // Consume T_TAG_OPEN.
        $attributes = $this->parseAttributes();
        $nextToken = $this->peek();
        if ($nextToken && $nextToken['type'] === TokenType::TAG_SELF_CLOSE) {
            $this->advance();

            return new Node(NodeType::ELEMENT, $tagName, $attributes, []);
        } else if ($nextToken && $nextToken['type'] === TokenType::TAG_END) {
            $this->advance();
            // For raw tags like script or style, if a T_RAW token is present use it as the sole child.
            if (in_array(strtolower($tagName), [
                    'script',
                    'style',
                ])
                && $this->peek() && $this->peek()['type'] === TokenType::RAW) {
                $rawContent = $this->peek()['value'];
                $this->advance();

                return new Node(NodeType::ELEMENT, $tagName, $attributes, [
                    new Node(NodeType::RAW, '', [], [], $rawContent),
                ]);
            }
            $children = $this->parseChildren();

            return new Node(NodeType::ELEMENT, $tagName, $attributes, $children);
        }

        return new Node(NodeType::ELEMENT, $tagName, $attributes, []);
    }

    protected function parseAttributes(): array
    {
        $attributes = [];
        while ($this->position < $this->length) {
            $token = $this->peek();
            if (! $token) {
                break;
            }
            if (in_array($token['type'], [
                TokenType::TAG_END,
                TokenType::TAG_SELF_CLOSE,
            ])) {
                break;
            }
            if ($token['type'] === TokenType::ATTR_NAME) {
                $attrName = $token['value'];
                $this->advance();
                $attrValue = null;
                if ($this->peek() && $this->peek()['type'] === TokenType::ATTR_VALUE) {
                    $attrValue = $this->peek()['value'];
                    $this->advance();
                }
                $attributes[$attrName] = $attrValue;
            } else {
                $this->advance();
            }
        }

        return $attributes;
    }

    protected function parseChildren(): array
    {
        $children = [];
        while ($this->position < $this->length) {
            $token = $this->peek();
            if (! $token) {
                break;
            }
            if ($token['type'] === TokenType::TAG_CLOSE) {
                $this->consumeClosingTag();
                break;
            }
            $child = $this->parseNode();
            if ($child !== null) {
                $children[] = $child;
            }
        }

        return $children;
    }

    protected function consumeClosingTag(): void
    {
        $this->advance(); // Consume TAG_CLOSE.
        if ($this->peek() && $this->peek()['type'] === TokenType::TAG_END) {
            $this->advance();
        }
    }

    protected function peek(): ?array
    {
        return ($this->position < $this->length) ? $this->tokens[$this->position] : null;
    }

    protected function advance(): void
    {
        $this->position++;
    }
}
