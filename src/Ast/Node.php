<?php

namespace Sinnbeck\HtmlAst\Ast;

readonly class Node
{
    public function __construct(
        public NodeType $type,
        public string   $tag = '',
        public array    $attributes = [],
        public array    $children = [],
        public string   $content = ''
    )
    {
    }
}
