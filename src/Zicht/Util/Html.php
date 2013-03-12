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
     * @param string $html
     * @return string
     */
    public static function repair($html)
    {
        $ret = '';
        for($i = 0; $i < strlen($html);) {
            if($html[$i] == '<') {
                if(preg_match('/^<\/?\w+[^>]*?>/', substr($html, $i), $m)) {
                    $ret .= self::sanitizeTag($m[0]);
                    $i += strlen($m[0]);
                } else { // assume unclosed tag, escape content.
                    $ret .= '&lt;';
                    $i ++;
                }
            } else {
                $ret .= $html[$i];
                $i ++;
            }
        }
        foreach(array_reverse(self::sanitizeTag(null, true)) as $unclosed) {
            $ret .= '</' . $unclosed . '>';
        }
        return $ret;
    }


    /**
     * Helper to keep track of the tag stack in Html::repair()
     *
     * @param string $tag
     * @param bool $resetStack
     * @return string|array
     */
    private static function sanitizeTag($tag, $resetStack = false)
    {
        static $stack = array();
        if($resetStack) {
            $ret = $stack;
            $stack = array();
            return $ret;
        }

        $tag = substr($tag, 1, -1); // strip off leading '<' and trailing '>'
        if($tag[0] == '/') {
            preg_match('/^\s*\w+/', substr($tag, 1), $m);
            $closingTag = strtolower($m[0]);
            $localStack = array();

            do {
                if(!count($stack)) {
                    $stack = array_reverse($localStack);
                    // tag was never opened. Drop closing tag
                    return '';
                }
                $top = array_pop($stack);
                $localStack[]= $top;
            } while(strtolower($top) != $closingTag);
            $ret = '';
            foreach($localStack as $needClose) {
                $ret .= '</' . $needClose . '>';
            }

            return $ret;
        } else {
            $ret = '';
            $attributes = array();
            preg_match('/^\S+/', $tag, $m);
            $tagName = strtolower($m[0]);

            $paragraphLevelElements = array('p', 'h6', 'h5', 'h4', 'h3', 'h2', 'h1');
            $inlineElements = array('span', 'b', 'i', 'strong', 'em', 'a');
            if(count($stack) && in_array($tagName, $paragraphLevelElements)) {
                while(in_array(end($stack), array_merge($paragraphLevelElements, $inlineElements))) {
                    $ret .= '</' . array_pop($stack) . '>';
                }
            }

            if(substr($tag, -1) == '/') {
                $isEmpty = true;
            } else {
                $isEmpty = in_array($tagName, array('br', 'img', 'input'));
            }
            for($i = strlen($m[0]); $i < strlen($tag);) {
                $preI = $i;
                while(ctype_space($tag[$i]))
                    $i ++;

                $chunk = substr($tag, $i);

                if(preg_match('/^([\w-]+)\s*=\s*\"([^"]*)\"?/', $chunk, $m)) {
                    $attributes[$m[1]]= $m[2];
                    $i += strlen($m[0]);
                } elseif(preg_match('/^([\w-]+)\s*=\s*\'([^\']*)\'?/', $chunk, $m)) {
                    $attributes[$m[1]]= $m[2];
                    $i += strlen($m[0]);
                } elseif(preg_match('/^([\w-]+)=(\S+)/', $chunk, $m)) {
                    $attributes[$m[1]]= $m[2];
                    $i += strlen($m[0]);
                } elseif(preg_match('/^(\w+)/', $chunk, $m)) {
                    $attributes[$m[1]] = $m[1];
                    $i += strlen($m[0]);
                } else {
                    // drop character
                    $i ++;
                }
                assert('$i != $preI');
            }

            $ret .= '<';
            $ret .= $tagName;
            if(count($attributes)) {
                foreach($attributes as $name => $value) {
                    $ret .= ' ';
                    $ret .= sprintf('%s="%s"', $name, $value);
                }
            }
            if($isEmpty) {
                $ret .= ' /';
            } else {
                $stack[]= $tagName;
            }
            $ret .= '>';
            return $ret;
        }
    }
}



