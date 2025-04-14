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

it('can fix basic-scrambled', function () {
    $htmlScrambled = getFixture('basic-scrambled.html');
    $lexer = new Lexer($htmlScrambled);

    $ast = new Parser($lexer->lex());

    $nodes = $ast->parse();

    $printer = new Printer();
    $html = getFixture('basic.html');
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

it('can print fragments', function () {
    $html = getFixture('fragment.html');
    $lexer = new Lexer($html);

    $ast = new Parser($lexer->lex());

    $nodes = $ast->parse();

    $printer = new Printer();
    expect($printer->render($nodes))->toEqual($html);
});

it('can print comments', function () {
    $html = getFixture('comments.html');
    $lexer = new Lexer($html);

    $ast = new Parser($lexer->lex());

    $nodes = $ast->parse();

    $printer = new Printer();
    expect($printer->render($nodes))->toEqual($html);
});

it('can handle all inline tags', function () {
    $html = getFixture('inline-tags.html');
    $lexer = new Lexer($html);
    $nodes = Parser::make($lexer->lex())->parse();
    expect(Printer::make()->render($nodes))->toEqual(
        <<<HTML
<img src="logo.png" alt="logo" />
<img src="logo.png" alt="logo" />
<img src="logo.png" alt="logo" />
<br />
<br />
<br />
HTML
    );
});
