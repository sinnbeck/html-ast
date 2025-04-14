# HTML AST
An HTML AST (Abstract Syntax Tree) parser written in php. Inspired by the AST parser in TempestPHP (written by Brett Roose).

It has a built-in lexer to parse the html, and then a AST parser to convert it into a tree structure.
Finally, it comes with a printer to output properly formatted HTML (indented).

## Usage
```php
use Sinnbeck\HtmlAst\Lexer\Lexer;
use Sinnbeck\HtmlAst\Ast\Parser;
use Sinnbeck\HtmlAst\Printer;

$lexer = Lexer::fromString($html)
$tokens = $lexer->lex();
$ast = Parser::make($tokens);
$nodeTree = $ast->parse();

//and if you want to output the resulting HTML
echo Printer::make()->render($html);
```

## Todo
* [ ] Add line numbers to lexer
* [ ] Add html validator to ensure HTMl structure is valid
* [ ] Add node visitors to allow changing HTML ?