<?php

namespace Sinnbeck\HtmlAst\Lexer;

enum TokenType
{
    case DOCTYPE;
    case TAG_OPEN;
    case TAG_CLOSE;
    case TAG_END;
    case TAG_SELF_CLOSE;
    case TEXT;
    case RAW;
    case ATTR_NAME;
    case ATTR_VALUE;
}
