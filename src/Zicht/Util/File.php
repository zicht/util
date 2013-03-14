<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Util;

/**
 * File utils.
 */
class File
{
    /**
     * Sanitize a file name, excluding unwanted characters and replacing them with a dash.
     *
     * @param string $fileName
     * @param bool $stripPath
     * @param int $maxLength
     * @return string
     */
    public static function sanitize($fileName, $stripPath = true, $maxLength = 120)
    {
        list($dirname, $name, $ext) = self::split($fileName);

        if (!$stripPath) {
            return self::sanitize(str_replace('/', '-', ltrim($dirname, '/')) . '-' . $name . '.' .$ext);
        }

        if (!$ext) {
            $ext = 'bin';
        }

        return substr(preg_replace('/[^\w.,-]/', '-', $name), 0) . '.' . $ext;
    }


    /**
     * Returns a tuple containing dir, basename (without extension) and extension.
     *
     * Usage:
     * list($dir, $file, $ext) = File::split($myFile);
     *
     * @param string $fileName
     * @return array
     */
    public static function split($fileName)
    {
        $ext = pathinfo($fileName);
        return array(
            $ext['dirname'],
            $ext['filename'],
            empty($ext['extension']) ? '' : $ext['extension']
        );
    }
}