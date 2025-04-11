<?php

namespace Sinnbeck\HtmlAst\Ast;

class Node
{
    public $type;       // "element", "text", "doctype"
    public $tag;        // For element nodes.
    public $attributes; // Associative array of attributes.
    public $children;   // Array of child nodes.
    public $content;    // For text or doctype nodes.

    public function __construct(
        NodeType $type,
        string $tag = '',
        array  $attributes = [],
        array  $children = [],
        string $content = ''
    )
    {
        $this->type = $type;
        $this->tag = $tag;
        $this->attributes = $attributes;
        $this->children = $children;
        $this->content = $content;
    }
}
