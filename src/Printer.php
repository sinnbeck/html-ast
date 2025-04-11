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
        // For doctype nodes, output as-is.
        if ($node->type === 'doctype') {
            return $node->content . $this->newline;
        }

        $indent = str_repeat($this->indentStr, $level);

        // For raw nodes, call renderRaw() to reindent them.
        if ($node->type === 'raw') {
            return $this->renderRaw($node->content, $level);
        }

        // For plain text nodes.
        if ($node->type === 'text') {
            return $indent . $node->content . $this->newline;
        }

        // Process element nodes.
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
            // For void elements, render in self-closing style.
            if (in_array($tagLower, $this->voidElements)) {
                $html .= " />" . $this->newline;

                return $html;
            }
            // Determine whether there are significant children.
            $hasChildren = false;
            foreach ($node->children as $child) {
                if (($child->type === 'text' | $child->type === 'raw') && trim($child->content) === '') {
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

    /**
     * Render raw content by reindenting it.
     * The approach is:
     * - Split the raw content into lines.
     * - For the first line, remove any leading whitespace (to avoid double indentation).
     * - Then prepend the printer's base indent (based on the current level) to every line.
     *
     * @param string $raw   The normalized raw content (with no common indent).
     * @param int    $level The current printer indent level.
     *
     * @return string The reindented raw content.
     */
    protected function renderRaw(string $raw, int $level): string
    {
        $baseIndent = str_repeat($this->indentStr, $level);
        $lines = preg_split('/\R/', $raw);

        $newLines = [];
        $first = true;
        foreach ($lines as $line) {
            if ($first) {
                // Remove any leading whitespace from the first line.
                $line = ltrim($line);
                $first = false;
            }
            $newLines[] = $baseIndent . $line;
        }

        return implode($this->newline, $newLines) . $this->newline;
    }
}
