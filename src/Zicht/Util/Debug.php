<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Util;

class Debug
{
    public static function lines($str)
    {
        preg_match_all('/^.*$/m', Str::dos2unix($str), $m);
        return $m[0];
    }

    public static function formatContext($str, $lineNr, $numBefore = 2, $numAfter = 2)
    {
        $lines = self::lines($str);
        if (isset($lines[$lineNr -1])) {
            $start = max(0, $lineNr -1 - $numBefore);
            return self::formatLines(array_slice($lines, $start, $numBefore + $numAfter +1), $start +1);
        }
        throw new \OutOfBoundsException("Line {$lineNr} is out of bounds");
    }


    public static function indent($str, $with = '    ')
    {
        $newline = (substr($str, -1, 1) == "\n") ? "\n" : "";
        return $with . join("\n" . $with, self::lines($str)) . $newline;
    }


    public static function formatLines($lines, $prefixStart = 1)
    {
        $max = strlen((string)(count($lines) + $prefixStart));

        $prefixTemplate = "%{$max}d. ";
        $ret = '';
        foreach ($lines as $l => $line) {
            $ret .= sprintf($prefixTemplate, $prefixStart + $l) . $line . "\n";
        }
        return $ret;
    }


    /**
     * Variable dumper.
     * Dumps variables in a PHP-syntax format.
     *
     * @param mixed $var
     * @param int $maxdepth
     * @param int $maxValueLen
     * @param array $stack
     * @return string
     */
    public static function dump($var, $maxdepth = 10, $maxValueLen = 255, $stack = array())
    {
        switch (gettype($var)) {
            case 'array':
            case 'object':
                $ret = '';

                $ret .= (count($stack) ? join('', $stack) : '');
                $ret .= '<' .  (
                            is_array($var)
                                ? 'array(' . count($var) . ')'
                                : get_class($var)
                        ) . '>';

                if ($maxdepth >= 0 && count($stack) == $maxdepth) {
                    $ret .= ' (...)' . "\n";
                } else {
                    if (is_array($var)) {
                        $iter = $var;
                        $notation = '[%s]';
                    } else {
                        $iter = get_object_vars($var);
                        $notation = '->%s';
                    }
                    if (count($iter) == 0) {
                        $ret .= "(empty)\n";
                    } else {
                        $ret .= "\n";

                        $i = 0;
                        foreach ($iter as $name => $value) {
                            array_push($stack, sprintf($notation, $name));
                            $ret .= self::dump($value, $maxdepth, $maxValueLen, $stack);
                            array_pop($stack);
                            $i++;
                        }
                    }
                }
                break;

            default:
                $val = 'null';
                switch (gettype($var)) {
                    case 'bool':
                    case 'boolean':
                        $val = ($var ? 'true' : 'false');
                        break;
                    case 'int':
                    case 'integer':
                        $val = sprintf('%d', $var);
                        break;
                    case 'float':
                    case 'double':
                        $val = sprintf('%F', $var);
                        break;
                    case 'string':
                        $val = '"'
                            . (
                                strlen($var) > $maxValueLen
                                    ? substr($var, 0, $maxValueLen)
                                        . '" ... (' . (strlen($var) - $maxValueLen) . ' more)'
                                    : $var
                            )
                            . '"'
                        ;
                        break;
                    case 'resource':
                        $val = 'resource (' . get_resource_type($var) . ')';
                        break;
                }
                if ($stack) {
                    $ret = join('', $stack) . ' = ' . $val;
                } else {
                    $ret = $val;
                }
                $ret .= "\n";
        }
        return $ret;
    }
}
