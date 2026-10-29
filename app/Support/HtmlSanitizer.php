<?php

namespace App\Support;

class HtmlSanitizer
{
    private const ALLOWED = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's',
        'h1', 'h2', 'h3',
        'ul', 'ol', 'li', 'blockquote', 'a',
    ];

    private const DROP = [
        'script', 'style', 'iframe', 'object', 'embed', 'form', 'textarea', 'noscript', 'svg', 'math',
    ];

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $previous = libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $loaded = $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="html-sanitizer-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        $root = $loaded ? $dom->getElementById('html-sanitizer-root') : null;
        if (!$root) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);

            return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        self::sanitizeChildren($root);

        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $result;
    }

    private static function sanitizeChildren(\DOMNode $node): void
    {
        $child = $node->firstChild;

        while ($child) {
            $next = $child->nextSibling;

            if ($child instanceof \DOMElement) {
                $tag = strtolower($child->nodeName);

                if (in_array($tag, self::DROP, true)) {
                    $node->removeChild($child);
                    $child = $next;
                    continue;
                }

                if (!in_array($tag, self::ALLOWED, true)) {
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);
                    $child = $node->firstChild;
                    continue;
                }

                self::cleanAttributes($child);
                self::sanitizeChildren($child);
            } elseif ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE) {
                $node->removeChild($child);
            }

            $child = $next;
        }
    }

    private static function cleanAttributes(\DOMElement $element): void
    {
        $href = null;

        if (strtolower($element->nodeName) === 'a') {
            $candidate = trim($element->getAttribute('href'));
            if (self::isSafeLink($candidate)) {
                $href = $candidate;
            }
        }

        while ($element->attributes->length > 0) {
            $element->removeAttribute($element->attributes->item(0)->nodeName);
        }

        if ($href !== null) {
            $element->setAttribute('href', $href);
        }
    }

    private static function isSafeLink(string $href): bool
    {
        if ($href === '' || preg_match('/[\s\x00-\x1f]/', $href)) {
            return false;
        }

        return (bool) preg_match('#^(https?://|mailto:)#i', $href);
    }
}
