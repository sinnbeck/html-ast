<?php

use Sinnbeck\HtmlAst\Ast\Parser;
use Sinnbeck\HtmlAst\Lexer\Lexer;
use Sinnbeck\HtmlAst\Printer;

it('can print basic', function () {
    $html = getFixture('basic.html');
    $lexer = new Lexer($html);

    $ast = new Parser($lexer->lex());

    $nodes = $ast->parse();

    $printer = new Printer();
    expect($printer->render($nodes))->toEqual($html);
});

it('can print complex', function () {
    $html = getFixture('complex.html');
    $lexer = new Lexer($html);

    $ast = new Parser($lexer->lex());

    $nodes = $ast->parse();

    $printer = new Printer();
    expect($printer->render($nodes))->toEqual($html);
});