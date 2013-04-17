<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Util;


/**
 * Utility functions for string formatting
 */
class Str
{
    /**
     * Alphanumeric characters
     */
    const ALNUM = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Safe alphanumeric characters, excluding "look-alike" characters.
     */
    const ALNUM_SAFE = '345679abcdefghjkmnpqrstuvwxyABCDEFGHJKMNPQRSTUVWXY';

    /**
     * Alphabetic characters
     */
    const ALPHA = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Numeric characters
     */
    const NUMERIC = '01234567890';

    /**
     * Hexadecimal characters
     */
    const HEX = '1234567890abcdef';

    /**
     * Camelcases an underscored or dashed string
     *
     * @param string $str
     * @param string $split
     * @return string
     */
    public static function camel($str, $split = '-_')
    {
        return preg_replace_callback(
            '/[' . preg_quote($split) . '](.?)/',
            function ($m) {
                return ucfirst($m[1]);
            },
            $str
        );
    }


    /**
     * Converts a camelcased string to dashed notation, CSS style.
     *
     * @param string $str
     * @return string
     */
    public static function dash($str)
    {
        return preg_replace_callback(
            '/[A-Z]/',
            function ($m) {
                return '-' . strtolower($m[0]);
            },
            $str
        );
    }


    /**
     * Converts a camelcased string to underscore notation, property style.
     *
     * @param string $str
     * @return string
     */
    public static function uscore($str)
    {
        return self::infix($str, '_');
    }


    /**
     * Converts a camelcased string to another separator, using 'infix' as the separator.
     *
     * @param string $str
     * @param string $infix
     * @return string
     */
    public static function infix($str, $infix)
    {
        return lcfirst(
            preg_replace_callback(
                '/(?<=.)[A-Z]/',
                function ($m) use($infix) {
                    return $infix . strtolower($m[0]);
                },
                $str
            )
        );
    }


    /**
     * Strips a suffix of a string
     *
     * @param string $str
     * @param string $suffix
     * @return string
     */
    public static function rstrip($str, $suffix)
    {
        if (substr($str, -strlen($suffix)) == $suffix) {
            return substr($str, 0, -strlen($suffix));
        }
        return $str;
    }


    /**
     * Strips a prefix of a string
     *
     * @param string $str
     * @param string $prefix
     * @return string
     */
    public static function lstrip($str, $prefix)
    {
        if (substr($str, 0, strlen($prefix)) == $prefix) {
            return substr($str, strlen($prefix));
        }
        return $str;
    }


    /**
     * Strips a prefix and suffix of  a string
     *
     * @param string $str
     * @param string $prefix
     * @param string $suffix
     * @return string
     */
    public static function strip($str, $prefix, $suffix)
    {
        return self::rstrip(self::lstrip($str, $prefix), $suffix);
    }


    /**
     * Returns a local class name
     *
     * @param string $fqcn
     * @return string
     */
    public static function classname($fqcn)
    {
        $parts = explode('\\', $fqcn);
        return array_pop($parts);
    }


    /**
     * Returns a somewhat "humanized" version of the string.
     *
     * @param string $str
     * @return string
     */
    public static function humanize($str)
    {
        return ucfirst(strtolower(preg_replace('/[\W_]+/', ' ', self::uscore($str))));
    }


    /**
     * Generate a random string with specified length from the specified characters
     *
     * @param int $length
     * @param string $characters
     * @return string
     */
    public static function random($length = 64, $characters = self::ALNUM)
    {
        $len = strlen($characters);
        return join('',
            array_map(
                function() use($characters, $len) {
                    return $characters{rand(0, $len -1)};
                },
                range(0, $length -1)
            )
        );
    }


    /**
     * Replace CRLF with LF characters.
     *
     * @param string $str
     * @return mixed
     */
    public static function dosToUnix($str)
    {
        return str_replace("\r\n", "\n", $str);
    }
}