<?php

use Sinnbeck\HtmlAst\Lexer\Lexer;
use Sinnbeck\HtmlAst\Lexer\TokenType;

it('can read an html file and output a tokens array', function () {
    $html = getFixture('basic.html');
    $lexer  = new Lexer($html);
    expect($lexer->lex())->toHaveCount(69);
});

it('has the same output no matter format of the input', function () {
    $html = getFixture('basic.html');
    $lexer  = Lexer::fromString($html);
    $tokens1 = $lexer->lex();

    $html = getFixture('basic-scrambled.html');
    $lexer  = Lexer::fromString($html);
    $tokens2 = $lexer->lex();
    expect($tokens1)->toEqual($tokens2);
});

it('can read get open and closing div tags', function () {
    $html = getFixture('basic.html');
    $lexer  = new Lexer($html);
    $tokens = $lexer->lex();

    //Opening div tag
    expect($tokens[52])
        ->toHaveKey('value', 'div')
        ->toHaveKey('type', TokenType::TAG_OPEN);

    //end opening div tag
    expect($tokens[55])
        ->toHaveKey('type', TokenType::TAG_END);

    //close div tag
    expect($tokens[58])
        ->toHaveKey('value', 'div')
        ->toHaveKey('type', TokenType::TAG_CLOSE);
});

it('can read get open and closing script tags', function () {
    $html = getFixture('complex.html');
    $lexer  = new Lexer($html);
    $tokens = $lexer->lex();

    //Opening script tag
    expect($tokens[55])
        ->toHaveKey('value', 'script')
        ->toHaveKey('type', TokenType::TAG_OPEN);

    //end opening script tag
    expect($tokens[60])
        ->toHaveKey('type', TokenType::TAG_END);

    //close div tag
    expect($tokens[62])
        ->toHaveKey('value', 'script')
        ->toHaveKey('type', TokenType::TAG_CLOSE);
});