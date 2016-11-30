<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht online <http://zicht.nl>
 */
namespace Zicht\Util;

/**
 * Utility class for XML related tools.
 */
class Xml
{
    /**
     * Format an XML string using libxml's domdocument formatter
     *
     * @param string $str
     * @return string
     */
    public static function format($str)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($str);
        $dom->formatOutput = true;
        return $dom->saveXML();
    }
}
