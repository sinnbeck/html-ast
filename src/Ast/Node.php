<?php

namespace Sinnbeck\HtmlAst\Ast;

class Node
{
    public function __construct(
        public readonly NodeType $type,
        public readonly string $tag = '',
        public readonly array  $attributes = [],
        public readonly array  $children = [],
        public readonly string $content = ''
    )
    {
    }
}
