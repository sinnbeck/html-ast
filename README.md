# HTML AST
A HTML AST parser written in php. Inspired by the AST parser in TempestPHP (written by Brett Roose).

It has a built in lexer to parse the html, and then a AST parser to convert it into a tree structure.
Finally it comes with a printer to output properly formatted HTML (indented).

## Usage
```php
$lexer = Sinnbeck\HtmlAst\Lexer\Lexer::fromString($html)
$tokens = $lexer->lex();
$ast = \Sinnbeck\HtmlAst\Ast\Parser::make($tokens);
$nodeTree = $ast->parse();

//and if you want to output the resulting HTML
echo \Sinnbeck\HtmlAst\Printer::make()->render($html);
```