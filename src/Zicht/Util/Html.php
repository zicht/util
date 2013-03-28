<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Util;

/**
 * Html utils
 */
class Html
{
    /**
     * Tries to repair a piece of html based on what an SGML parser would do internally (more or less).
     *
     * Use the callback to filter out unwanted elements. If not specified, it will keep all elements and attributes.
     *
     * @param string $html
     * @param callback $allowed
     * @return string
     */
    public static function repair($html, $allowed = null)
    {
        $ret = '';

        for ($i = 0; $i < strlen($html); true) {
            if ($html[$i] == '<') {
                if (preg_match('/^<\/?[a-z][\w-:]*[^>]*?>/i', substr($html, $i), $m)) {
                    $ret .= self::sanitizeTag($m[0], $allowed);
                    $i += strlen($m[0]);
                } else {
                    // assume unclosed tag, escape content.
                    $ret .= '&lt;';
                    $i++;
                }
            } else {
                $ret .= $html[$i];
                $i++;
            }
        }
        foreach (array_reverse(self::sanitizeTag(null, null, true)) as $unclosed) {
            $ret .= '</' . $unclosed . '>';
        }
        return $ret;
    }

    /**
     * Assume all unknown entity references are meant as an &amp; escape.
     *
     * @param string $html
     * @return string
     */
    public static function sanitizeEntityReferences($html)
    {
        return preg_replace(
            '/&(?!(nbsp|amp|quot|apos|copy|lt|gt|#x?\d+|([aeiouyAEIOUY](grave|acute|uml|circ|dash|tilde)));)/',
            '&amp;',
            $html
        );
    }


    /**
     * Helper to keep track of the tag stack in Html::repair()
     *
     * @param string $tag
     * @param array $allowed
     * @param bool $resetStack
     * @return string|array
     */
    private static function sanitizeTag($tag, $allowed = null, $resetStack = false)
    {
        static $stack = array();
        if ($resetStack) {
            $ret = $stack;
            $stack = array();
            return $ret;
        }

        $tag = substr($tag, 1, -1); // strip off leading '<' and trailing '>'
        if ($tag[0] == '/') {
            preg_match('/^\w+/', substr($tag, 1), $m);
            $closingTag = strtolower($m[0]);
            $localStack = array();

            if  (
                null !== $allowed
                && (is_array($allowed) && !array_key_exists($closingTag, $allowed))
                || (is_callable($allowed) && !call_user_func($allowed, $closingTag))
            ) {
                return '<!-- ' . htmlspecialchars($tag) . ' -->';
            }

            do {
                if (!count($stack)) {
                    $stack = array_reverse($localStack);
                    // tag was never opened. Drop closing tag
                    return '';
                }
                $top = array_pop($stack);
                $localStack[] = $top;
            } while (strtolower($top) != $closingTag);
            $ret = '';
            foreach ($localStack as $needClose) {
                $ret .= '</' . $needClose . '>';
            }

            return $ret;
        } else {
            $ret = '';
            $attributes = array();
            preg_match('/^[\w-:]+/', $tag, $m);
            $tagName = strtolower($m[0]);
            if (
                null !== $allowed
                && (is_array($allowed) && !array_key_exists($tagName, $allowed))
                || (is_callable($allowed) && !call_user_func($allowed, $tagName))
            ) {
                return '<!-- ' . htmlspecialchars($tag) . ' -->';
            }

            $paragraphLevelElements = array('p', 'h6', 'h5', 'h4', 'h3', 'h2', 'h1', 'table', 'ul', 'div');
            $inlineElements = array('span', 'b', 'i', 'u', 'span', 'strong', 'em', 'a', 'sup', 'sub');
            $selfClosingElements = array('li', 'td', 'th', 'tr');
            $forcedParents = array('li' => array('ul', 'ol'));

            if (count($stack) && in_array($tagName, $paragraphLevelElements)) {
                while (in_array(end($stack), array_merge($paragraphLevelElements, $inlineElements))) {
                    $ret .= '</' . array_pop($stack) . '>';
                }
            }
            if (count($stack) && in_array($tagName, $selfClosingElements)) {
                while (in_array(end($stack), $inlineElements)) {
                    $ret .= '</' . array_pop($stack) . '>';
                }
                if (end($stack) === $tagName) {
                    $ret .= '</' . array_pop($stack) . '>';
                }
            }

            if (array_key_exists($tagName, $forcedParents) && !in_array(end($stack), $forcedParents[$tagName])) {
                // if the parent would close paragraph or inline elements, do it now.
                if (count($stack) && in_array($forcedParents[$tagName][0], $paragraphLevelElements)) {
                    while (in_array(end($stack), array_merge($paragraphLevelElements, $inlineElements))) {
                        $ret .= '</' . array_pop($stack) . '>';
                    }
                }
                $ret .= '<' . $forcedParents[$tagName][0] . '>';
                $stack[]= $forcedParents[$tagName][0];
            }

            if (substr($tag, -1) == '/') {
                $isEmpty = true;
            } else {
                $isEmpty = in_array($tagName, array('br', 'img', 'input', 'param', 'isindex', 'area'));
            }
            for ($i = strlen($m[0]); $i < strlen($tag); true) {
                $preI = $i;

                $chunk = substr($tag, $i);

                if (preg_match('/^([\w-]+)\s*=\s*\"([^"]*)\"?/', $chunk, $m)) {
                    $attributes[$m[1]] = $m[2];
                    $i += strlen($m[0]);
                } elseif (preg_match('/^([\w-]+)\s*=\s*\'([^\']*)\'?/', $chunk, $m)) {
                    $attributes[$m[1]] = $m[2];
                    $i += strlen($m[0]);
                } elseif (preg_match('/^([\w-]+)=(\S+)/', $chunk, $m)) {
                    $attributes[$m[1]] = $m[2];
                    $i += strlen($m[0]);
                } elseif (preg_match('/^(\w+)/', $chunk, $m)) {
                    $attributes[$m[1]] = $m[1];
                    $i += strlen($m[0]);
                } else {
                    // drop character
                    $i++;
                }
                assert('$i != $preI');
            }

            $ret .= '<';
            $ret .= $tagName;
            if (count($attributes)) {
                foreach ($attributes as $attrName => $value) {
                    $callback = null;
                    if (is_callable($allowed)) {
                        $callback = $allowed;
                    } elseif (is_array($allowed) && isset($allowed[$tagName]) && is_callable($allowed[$tagName])) {
                        $callback = $allowed[$tagName];
                    } elseif (is_array($allowed[$tagName]) && isset($allowed[$tagName][$attrName])) {
                        $callback = $allowed[$tagName][$attrName];
                    }
                    if (null !== $callback) {
                        $value = call_user_func($callback, $tagName, $attrName, $value);
                    }
                    if (false === $value) {
                        continue;
                    }
                    if (!preg_match('/^[a-z_-]/', $attrName)) {
                        // drop malformed attribute name
                        continue;
                    }
                    $ret .= ' ';
                    $ret .= sprintf('%s="%s"', $attrName, str_replace('"', '&quot;', $value));
                }
            }
            if ($isEmpty) {
                $ret .= ' /';
            } else {
                $stack[] = $tagName;
            }
            $ret .= '>';
            return $ret;
        }
    }


    /**
     * @param $node
     * @return bool
     */
    public static function isWhitespace(\DOMNode $node)
    {
        return $node->nodeType === XML_TEXT_NODE && preg_match('/^[\s\x0a\xc2]*$/', $node->nodeValue);
    }

    /**
     * @param $node DOMNode
     */
    public static function isEmptyNode($node)
    {
        $ret = true;
        $elements = array('p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'strong', 'em', 'span', 'b', 'i', 'em', 'a');
        if (in_array(strtolower($node->nodeName), $elements)) {
            foreach ($node->childNodes as $node) {
                if (!self::isEmptyNode($node) && !self::isWhitespace($node)) {
                    $ret = false;
                    break;
                }
            }
        } else {
            $ret = self::isWhitespace($node);
        }
        return $ret;
    }
}