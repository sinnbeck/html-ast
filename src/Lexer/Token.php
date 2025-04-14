<?php

namespace Sinnbeck\HtmlAst\Lexer;

readonly class Token
{
    public function __construct(
        public TokenType $type,
        public string    $value,
    )
    {}
}