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

    expect($nodes[0])
        ->toBeInstanceOf(Node::class)
        ->toHaveKey('type', 'doctype');

    expect($nodes[1])
        ->toBeInstanceOf(Node::class)
        ->toHaveKey('type', 'element')
        ->toHaveKey('tag', 'html')
        ->toHaveKey('children');

    expect($nodes[1]->children[0])
        ->toBeInstanceOf(Node::class)
        ->toHaveKey('type', 'element')
        ->toHaveKey('tag', 'head');

    expect($nodes[1]->children[0]->children[0])
        ->toBeInstanceOf(Node::class)
        ->toHaveKey('type', 'element')
        ->toHaveKey('tag', 'meta');

    expect($nodes[1]->children[1])
        ->toBeInstanceOf(Node::class)
        ->toHaveKey('type', 'element')
        ->toHaveKey('tag', 'body');

    expect($nodes[1]->children[1]->children[0])
        ->toBeInstanceOf(Node::class)
        ->toHaveKey('type', 'element')
        ->toHaveKey('tag', 'div');

});