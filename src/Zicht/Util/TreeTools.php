<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Util;

/**
 * Utility functions related to tree-like structures
 */
class TreeTools
{
    /**
     * Get a property value by a path of property names or key names.
     *
     * @param string $subject
     * @param array $path
     * @param mixed $default
     * @return null
     */
    public static function getByPath($subject, array $path, $default = null)
    {
        $ptr =& $subject;
        foreach ($path as $key) {
            if (is_object($ptr) && isset($ptr->$key)) {
                $ptr =& $ptr->$key;
            } elseif (is_array($ptr) && isset($ptr[$key])) {
                $ptr =& $ptr[$key];
            } else {
                return $default;
            }
        }
        return $ptr;
    }


    /**
     * Set a property value by a path of property or key names.
     *
     * @param mixed $subject
     * @param array $path
     * @param mixed $value
     * @return mixed
     */
    public static function setByPath(&$subject, array $path, $value)
    {
        $ptr =& $subject;
        foreach ($path as $key) {
            if (is_object($ptr)) {
                if (!isset($ptr->$key)) {
                    $ptr->$key = [];
                }
                $ptr =& $ptr->$key;
            } elseif (is_array($ptr)) {
                if (!isset($ptr[$key])) {
                    $ptr[$key] = [];
                }
                $ptr =& $ptr[$key];
            }
        }
        $ptr = $value;
        return $subject;
    }
}
