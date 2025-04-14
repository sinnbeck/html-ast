<?php

use Sinnbeck\HtmlAst\Ast\Node;
use Sinnbeck\HtmlAst\Ast\NodeType;
use Sinnbeck\HtmlAst\Ast\Parser;
use Sinnbeck\HtmlAst\Lexer\Lexer;

it('can parse tokens', function () {
    $html = getFixture('basic.html');
    $lexer = new Lexer($html);

    $ast = new Parser($lexer->lex());

    $nodes = $ast->parse();
    expect($nodes)
        ->toHaveCount(2);

    expect($nodes[0])
        ->toBeInstanceOf(Node::class)
        ->toHaveKey('type', NodeType::DOCTYPE);

    expect($nodes[1])
        ->toBeInstanceOf(Node::class)
        ->toHaveKey('type', NodeType::ELEMENT)
        ->toHaveKey('tag', 'html')
        ->toHaveKey('children');

    expect($nodes[1]->children[0])
        ->toBeInstanceOf(Node::class)
        ->toHaveKey('type', NodeType::ELEMENT)
        ->toHaveKey('tag', 'head');

    expect($nodes[1]->children[0]->children[0])
        ->toBeInstanceOf(Node::class)
        ->toHaveKey('type', NodeType::ELEMENT)
        ->toHaveKey('tag', 'meta');

    expect($nodes[1]->children[1])
        ->toBeInstanceOf(Node::class)
        ->toHaveKey('type', NodeType::ELEMENT)
        ->toHaveKey('tag', 'body');

    expect($nodes[1]->children[1]->children[0])
        ->toBeInstanceOf(Node::class)
        ->toHaveKey('type', NodeType::ELEMENT)
        ->toHaveKey('tag', 'div');

    expect($nodes[1]->children[1]->children[0]->children[0])
        ->toBeInstanceOf(Node::class)
        ->toHaveKey('type', NodeType::ELEMENT)
        ->toHaveKey('tag', 'img')
        ->toHaveKey('attributes', [
            'src' => '/gfx/logo.png',
            'alt' => 'logo',
        ]);

    expect($nodes[1]->children[1]->children[1]->children[0])
        ->toBeInstanceOf(Node::class)
        ->toHaveKey('type', NodeType::TEXT)
        ->toHaveKey('content', 'Smaller text');

});

it('can parse script tag', function () {
    $html = getFixture('complex.html');
    $lexer = new Lexer($html);

    $ast = new Parser($lexer->lex());

    $nodes = $ast->parse();
    expect($nodes)->toHaveCount(2);

    expect($nodes[1]->children[1]->children[1])
        ->toBeInstanceOf(Node::class)
        ->toHaveKey('type', NodeType::ELEMENT)
        ->toHaveKey('tag', 'script');

    expect($nodes[1]->children[1]->children[1]->children[0])
        ->toBeInstanceOf(Node::class)
        ->toHaveKey('type', NodeType::RAW);

});