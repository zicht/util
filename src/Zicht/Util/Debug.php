<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Util;

/**
 * Debugging / reporting utilities
 */
class Debug
{
    /**
     * Returns all lines in a string as an array.
     *
     * @param string $str
     * @return array
     */
    public static function lines($str)
    {
        preg_match_all('/^.*$/m', Str::dosToUnix($str), $m);
        return $m[0];
    }

    /**
     * Format the context of a line of code in the passed string prefixed with line numbers, e.g.:
     *
     * @param string $str
     * @param int $lineNr
     * @param int $numBefore
     * @param int $numAfter
     * @return string
     *
     * @throws \OutOfBoundsException
     */
    public static function formatContext($str, $lineNr, $numBefore = 2, $numAfter = 2)
    {
        $lines = self::lines($str);
        if (isset($lines[$lineNr -1])) {
            $start = max(0, $lineNr -1 - $numBefore);
            return self::formatLines(array_slice($lines, $start, $numBefore + $numAfter +1), $start +1);
        }
        throw new \OutOfBoundsException("Line {$lineNr} is out of bounds");
    }


    /**
     * Indent a string with the specified indent
     *
     * @param string $str
     * @param string $with
     * @return string
     */
    public static function indent($str, $with = '    ')
    {
        $newline = (substr($str, -1, 1) == "\n") ? "\n" : "";
        return $with . join("\n" . $with, self::lines($str)) . $newline;
    }


    /**
     * Prefix all lines with a line number starting at the given number.
     *
     * @param array $lines
     * @param int $prefixStart
     * @return string
     */
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
        $children = $hierarchyNotation = null;

        if (is_object($var)) {
            $children = get_object_vars($var);
            $hierarchyNotation = '->%s';
        } elseif (is_array($var)) {
            $children = $var;
            $hierarchyNotation = '[%s]';
        }


        if (isset($children)) {
            $ret = self::formatDumpStack($stack, $var);

            if ($maxdepth >= 0 && count($stack) == $maxdepth) {
                $ret .= ' (...)' . "\n";
            } else {
                if (count($children) == 0) {
                    $ret .= "(empty)\n";
                } else {
                    $ret .= "\n";

                    $i = 0;
                    foreach ($children as $name => $value) {
                        array_push($stack, sprintf($hierarchyNotation, $name));
                        $ret .= self::dump($value, $maxdepth, $maxValueLen, $stack);
                        array_pop($stack);
                        $i++;
                    }
                }
            }
        } else {
            $val = self::dumpScalar($var, $maxValueLen);
            if ($stack) {
                $ret = join('', $stack) . ' = ' . $val;
            } else {
                $ret = $val;
            }
            $ret .= "\n";
        }
        return $ret;
    }


    /**
     * Dumps a scalar value.
     *
     * @param mixed $var
     * @param int $maxValueLen
     * @return string
     */
    public static function dumpScalar($var, $maxValueLen)
    {
        switch (true) {
            case is_null($var):
                $val = 'null';
                break;
            case is_bool($var):
                $val = ($var ? 'true' : 'false');
                break;
            case is_integer($var):
                $val = sprintf('%d', $var);
                break;
            case is_float($var):
                $val = sprintf('%F', $var);
                break;
            case is_string($var):
                $val = self::formatString($var, $maxValueLen);
                break;
            case is_resource($var):
                $val = 'resource (' . get_resource_type($var) . ')';
                break;
            default:
                $val = gettype($var) . ' (unknown scalar type)';
                break;
        }
        return $val;
    }


    /**
     * Format a string for dump output
     *
     * @param string $var
     * @param int $maxValueLen
     * @return string
     */
    public static function formatString($var, $maxValueLen)
    {
        $val = '"';
        if (strlen($var) > $maxValueLen) {
            $val .= substr($var, 0, $maxValueLen) . '" ... (' . (strlen($var) - $maxValueLen) . ' more)';
        } else {
            $val .= $var . '"';
        }
        return $val;
    }


    /**
     * Formats a stack prefix
     *
     * @param array $stack
     * @param string $var
     * @return string
     */
    protected static function formatDumpStack($stack, $var)
    {
        $ret = (count($stack) ? join('', $stack) : '');
        $ret .= '<' .  (
                    is_array($var)
                        ? 'array(' . count($var) . ')'
                        : get_class($var)
                ) . '>';

        return $ret;
    }
}
