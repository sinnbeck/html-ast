<?php

use Sinnbeck\HtmlAst\Ast\Node;
use Sinnbeck\HtmlAst\Ast\Parser;
use Sinnbeck\HtmlAst\Lexer\Lexer;

it('can parse tokens', function () {
    $html = getFixture('basic.html');
    $lexer = new Lexer($html);

    $ast = new Parser($lexer->lex());

    $nodes = $ast->parse();
    expect($nodes)->toHaveCount(2);

    expect($nodes[0])->toBeInstanceOf(Node::class);

});