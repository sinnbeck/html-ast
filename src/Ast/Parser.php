<?php

namespace Sinnbeck\HtmlAst\Ast;

use Sinnbeck\HtmlAst\Lexer\Token;
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
        if ($token->type === TokenType::DOCTYPE) {
            $this->advance();
            return new Node(NodeType::DOCTYPE, '', [], [], $token->value);
        }
        if ($token->type === TokenType::TEXT) {
            $this->advance();
            return new Node(NodeType::TEXT, '', [], [], $token->value);
        }
        // NEW: Parse a comment token and create a COMMENT node.
        if ($token->type === TokenType::COMMENT) {
            $this->advance();
            return new Node(NodeType::COMMENT, '', [], [], $token->value);
        }
        if ($token->type === TokenType::RAW) {
            $this->advance();
            return new Node(NodeType::RAW, '', [], [], $token->value);
        }
        if ($token->type === TokenType::TAG_OPEN) {
            return $this->parseElement();
        }
        if ($token->type === TokenType::TAG_CLOSE) {
            $this->consumeClosingTag();
            return null;
        }
        $this->advance();

        return null;
    }


    protected function parseElement(): Node
    {
        // Consume the open tag token (e.g. from '<script').
        $openToken = $this->peek();
        $tagName = $openToken->value;
        $this->advance(); // Consumes TAG_OPEN.

        // Parse any attributes.
        $attributes = $this->parseAttributes();

        $nextToken = $this->peek();

        // Handle self-closing tags.
        if ($nextToken && $nextToken->type === TokenType::TAG_SELF_CLOSE) {
            $this->advance();
            return new Node(NodeType::ELEMENT, $tagName, $attributes, []);
        }
        // Otherwise, expect a TAG_END for the opening tag.
        elseif ($nextToken && $nextToken->type === TokenType::TAG_END) {
            $this->advance(); // Consume the TAG_END for the opening tag.

            // Check if this is a raw element (like script or style).
            if (in_array(strtolower($tagName), ['script', 'style'])) {
                $children = [];
                // If a RAW token is present immediately, use it as the element’s content.
                if ($this->peek() && $this->peek()->type === TokenType::RAW) {
                    $rawToken = $this->peek();
                    $this->advance();
                    $children[] = new Node(NodeType::RAW, '', [], [], $rawToken->value);
                }
                // Consume the closing tag tokens (TAG_CLOSE and TAG_END).
                if ($this->peek() && $this->peek()->type === TokenType::TAG_CLOSE) {
                    $this->consumeClosingTag();
                }
                return new Node(NodeType::ELEMENT, $tagName, $attributes, $children);
            }
            // For all other elements, parse their children normally.
            $children = $this->parseChildren();
            return new Node(NodeType::ELEMENT, $tagName, $attributes, $children);
        }

        // Fallback: return an empty element node.
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
            if (in_array($token->type, [
                TokenType::TAG_END,
                TokenType::TAG_SELF_CLOSE,
            ])) {
                break;
            }
            if ($token->type === TokenType::ATTR_NAME) {
                $attrName = $token->value;
                $this->advance();
                $attrValue = null;
                if ($this->peek() && $this->peek()->type === TokenType::ATTR_VALUE) {
                    $attrValue = $this->peek()->value;
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
            if ($token->type === TokenType::TAG_CLOSE) {
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
    }

    protected function peek(): ?Token
    {
        return ($this->position < $this->length) ? $this->tokens[$this->position] : null;
    }

    protected function advance(): void
    {
        $this->position++;
    }
}
