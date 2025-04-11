<?php

namespace Sinnbeck\HtmlAst;

use Sinnbeck\HtmlAst\Ast\Node;

class Printer
{
    protected $indentStr = "    ";
    protected $newline = "\n";
    protected $voidElements = [
        'area',
        'base',
        'br',
        'col',
        'embed',
        'hr',
        'img',
        'input',
        'keygen',
        'link',
        'meta',
        'param',
        'source',
        'track',
        'wbr',
    ];

    public function render(array $nodes, int $level = 0): string
    {
        $html = '';
        foreach ($nodes as $node) {
            $html .= $this->renderNode($node, $level);
        }

        return $html;
    }

    protected function renderNode(Node $node, int $level): string
    {
        if ($node->type === 'doctype') {
            return $node->content . $this->newline;
        }
        $indent = str_repeat($this->indentStr, $level);
        if ($node->type === 'text') {
            return $indent . $node->content . $this->newline;
        }
        if ($node->type === 'element') {
            $tagLower = strtolower($node->tag);
            $html = $indent . "<" . $node->tag;
            foreach ($node->attributes as $name => $value) {
                if ($value !== null) {
                    $html .= " " . $name . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false) . '"';
                } else {
                    $html .= " " . $name;
                }
            }
            // If the element is a void element, render it in self-closing style.
            if (in_array($tagLower, $this->voidElements)) {
                $html .= " />" . $this->newline;

                return $html;
            }
            // Otherwise, render children and a closing tag.
            $hasChildren = false;
            foreach ($node->children as $child) {
                if ($child->type === 'text' && trim($child->content) === '') {
                    continue;
                }
                $hasChildren = true;
                break;
            }
            if (! $hasChildren) {
                $html .= "></" . $node->tag . ">" . $this->newline;
            } else {
                $html .= ">" . $this->newline;
                foreach ($node->children as $child) {
                    $html .= $this->renderNode($child, $level + 1);
                }
                $html .= $indent . "</" . $node->tag . ">" . $this->newline;
            }

            return $html;
        }

        return '';
    }
}
