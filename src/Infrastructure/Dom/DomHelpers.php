<?php
declare(strict_types=1);

namespace CodeInSync\Infrastructure\Dom;

use DOMElement;
use DOMNode;
//use DOMNodeList;

final class DomHelpers
{
    public static function getElementsByClass(DOMNode $node, string $tagName, string $className): array
    {
        $elements = [];

        if (!method_exists($node, 'getElementsByTagName')) {
            return $elements;
        }

        foreach ($node->getElementsByTagName($tagName) as $el) {
            if (!$el instanceof DOMElement) continue;

            $classes = preg_split('/\s+/', $el->getAttribute('class'), -1, PREG_SPLIT_NO_EMPTY);
            if (in_array($className, $classes, true)) {
                $elements[] = $el;
            }
        }

        return $elements;
    }
}