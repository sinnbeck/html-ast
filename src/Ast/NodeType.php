<?php

namespace Sinnbeck\HtmlAst\Ast;

enum NodeType
{
    case DOCTYPE;
    case ELEMENT;
    case TEXT;
    case RAW;
}
