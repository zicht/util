<?php
/**
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
     * @param string $defaultExtension
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public static function sanitize($fileName, $stripPath = true, $maxLength = 120, $defaultExtension = 'bin')
    {
        list($dirname, $name, $ext) = self::split($fileName);

        if (!$stripPath) {
            $unslashedFilename = str_replace('/', '-', ltrim($dirname, '/')) . '-' . $name . '.' . $ext;
            return self::sanitize($unslashedFilename, true, $maxLength);
        }

        if (!$ext) {
            $ext = $defaultExtension;
        }
        $maxBaselength = $maxLength - 1 - strlen($ext);
        if ($maxBaselength <= 0) {
            throw new \InvalidArgumentException("Maxlength of {$maxLength} results in an empty filename");
        }

        return substr(preg_replace('/[^\w.,-]/', '-', $name), 0, $maxBaselength) . '.' . $ext;
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
        return [
            $ext['dirname'],
            $ext['filename'],
            empty($ext['extension']) ? '' : $ext['extension']
        ];
    }
}
