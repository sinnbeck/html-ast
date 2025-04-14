# html-ast

An HTML AST (Abstract Syntax Tree) parser written in PHP.  
Inspired by the AST parser in TempestPHP (by Brett Roose), this library provides a built-in lexer to tokenize HTML strings, an AST parser to convert tokens into a tree structure, and a printer to output well-formatted (indented) HTML.

> **Note:** This package requires PHP 8.2 or higher.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
    - [Lexing](#lexing)
    - [Parsing](#parsing)
    - [Printing](#printing)
- [Testing](#testing)
- [Todo](#todo)
- [Contributing](#contributing)
- [License](#license)

## Features

- **Built-in Lexer:** Tokenizes raw HTML input.
- **AST Parser:** Converts tokenized HTML into an Abstract Syntax Tree for easier analysis and manipulation.
- **HTML Printer:** Renders the AST back into properly indented HTML code.

## Requirements

- PHP version **8.2** or later.
- Composer (for installation via [Packagist](https://packagist.org/)).

## Installation

You can install **html-ast** via Composer. From your project root, run:

```bash
composer require sinnbeck/html-ast
```

Alternatively, if you prefer to clone the repository directly:

```bash
git clone https://github.com/sinnbeck/html-ast.git
cd html-ast
composer install
```

## Usage

The package is organized into three main components: the Lexer, the AST Parser, and the Printer. Below are basic examples of how to use each.

### Lexing

The lexer tokenizes an HTML string. Tokens represent the smallest meaningful elements of the HTML (such as tags, attributes, and text).

```php
use Sinnbeck\HtmlAst\Lexer\Lexer;

// Provide your HTML string
$html = '<div class="container"><p>Hello, world!</p></div>';

// Create a Lexer instance from the string
$lexer = Lexer::fromString($html);

// Lex the HTML string into tokens
$tokens = $lexer->lex();

// Optionally, inspect the tokens:
print_r($tokens);
```

### Parsing

The AST parser converts the token list into a tree structure, where each node represents an HTML element, text node, or comment.

```php
use Sinnbeck\HtmlAst\Ast\Parser;

// Create an AST parser instance with the tokens from the lexer
$ast = Parser::make($tokens);

// Parse tokens into an AST (node tree)
$nodeTree = $ast->parse();

// Optionally, inspect the node tree:
print_r($nodeTree);
```

### Printing

The printer takes an HTML input or the resulting AST and renders it as neatly formatted HTML. This is useful for ensuring consistent formatting after transformations.

```php
use Sinnbeck\HtmlAst\Printer;

// Create a Printer instance and render the HTML string
echo Printer::make()->render($nodeTree);
```

## Testing

The repository includes tests under the `tests` directory, using [Pest PHP](https://pestphp.com/) as the testing framework and Symfony's VarDumper for debugging. To run tests, execute:

```bash
composer test
```

This command runs all tests to ensure the lexing, parsing, and printing functionalities work as expected.

## Todo

* [ ] Add line numbers to tokens (Lexer)
* [ ] Introduce an HTML validator to ensure that the HTML structure conforms to expected standards
* [ ] Implement a node visitor pattern to allow modification or transformation of the AST

## Contributing

Contributions to **html-ast** are welcome. If you would like to contribute, please follow these steps:

1. Fork the repository.
2. Create a feature branch:
   ```bash
   git checkout -b feature/your-feature-name
   ```
3. Make your changes and add tests.
4. Format all files:
    ```bash
    ./vendor/bin/pint`
    ```
5. Commit your changes:
   ```bash
   git commit -am 'Add new feature'
   ```
6. Push the branch:
   ```bash
   git push origin feature/your-feature-name
   ```
7. Open a pull request explaining your changes.

Please adhere to the coding standards and test all changes before submitting a pull request.

## License

This project is licensed under the [MIT License](https://opensource.org/license/MIT)
