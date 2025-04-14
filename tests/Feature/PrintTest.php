<?php

use Sinnbeck\HtmlAst\Ast\Parser;
use Sinnbeck\HtmlAst\Lexer\Lexer;
use Sinnbeck\HtmlAst\Printer;

it('can print basic', function () {
    $html = getFixture('basic.html');
    $lexer = new Lexer($html);

    $ast = new Parser($lexer->lex());

    $nodes = $ast->parse();

    $printer = new Printer($nodes);
    expect($printer->render())->toEqual($html);
});

it('can fix basic-scrambled', function () {
    $htmlScrambled = getFixture('basic-scrambled.html');
    $lexer = new Lexer($htmlScrambled);

    $ast = new Parser($lexer->lex());

    $nodes = $ast->parse();

    $printer = new Printer($nodes);
    $html = getFixture('basic.html');
    expect($printer->render())->toEqual($html);
});

it('can indent with tabs', function () {
    $html = getFixture('basic.html');
    $lexer = new Lexer($html);

    $ast = new Parser($lexer->lex());

    $nodes = $ast->parse();

    $printer = new Printer($nodes);
    $html = str_replace('    ', "\t", $printer->render());
    expect($printer->indentWith("\t")->render())->toEqual($html);
});

it('can print complex', function () {
    $html = getFixture('complex.html');
    $lexer = new Lexer($html);

    $ast = new Parser($lexer->lex());

    $nodes = $ast->parse();

    $printer = new Printer($nodes);
    expect($printer->render())->toEqual($html);
});

it('can print fragments', function () {
    $html = getFixture('fragment.html');
    $lexer = new Lexer($html);

    $ast = new Parser($lexer->lex());

    $nodes = $ast->parse();

    $printer = new Printer($nodes);
    expect($printer->render())->toEqual($html);
});

it('can indent everything', function () {
    $html = getFixture('fragment.html');
    $lexer = new Lexer($html);

    $ast = new Parser($lexer->lex());

    $nodes = $ast->parse();

    $printer = new Printer($nodes);
    $indented = '';
    foreach (explode(PHP_EOL, $html) as $line) {
        $indented .= '    '. $line . PHP_EOL;
    }
    expect($printer->render(1))->toEqual(rtrim($indented));
});

it('can print comments', function () {
    $html = getFixture('comments.html');
    $lexer = new Lexer($html);

    $ast = new Parser($lexer->lex());

    $nodes = $ast->parse();

    $printer = new Printer($nodes);
    expect($printer->render())->toEqual($html);
});

it('can handle all inline tags', function () {
    $html = getFixture('inline-tags.html');
    $lexer = new Lexer($html);
    $nodes = Parser::make($lexer->lex())->parse();
    expect(Printer::make($nodes)->render())->toEqual(
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
