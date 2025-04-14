<?php

namespace Sinnbeck\HtmlAst\Lexer;

class Token
{
    public function __construct(
        public readonly TokenType $type,
        public readonly string $value,
    )
    {}
}