<?php

use Sinnbeck\HtmlAst\Lexer\Lexer;

it('can read an html file and output a tokens array', function () {
    $html = getFixture('basic.html');
    $lexer  = new Lexer($html);
    expect($lexer->lex())->toHaveCount(75);
});

it('has the same output no matter format of the input', function () {
    $html = getFixture('basic.html');
    $lexer  = Lexer::make($html);
    $tokens1 = $lexer->lex();

    $html = getFixture('basic-scrambled.html');
    $lexer  = Lexer::make($html);
    $tokens2 = $lexer->lex();
    expect($tokens1)->toEqual($tokens2);
});