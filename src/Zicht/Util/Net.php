<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht online <http://zicht.nl>
 */


namespace Zicht\Util;

/**
 * Some utilities regarding networking
 *
 * @package Zicht\Util
 */
final class Net
{
    public static $PRIVATE_IPV4_RANGES = [
        [[10, 0, 0, 0],      [10, 255, 255, 255]],
        [[172, 16, 0, 0],    [172.31,255, 255]],
        [[192, 168, 0, 0],   [192, 168, 255, 255]],
        [[169, 254, 0, 0],   [169, 254, 255, 255]],
        [[127, 0, 0, 0],     [127, 255, 255, 255]]
    ];

    /**
     * Check if the given IPv4 address is local
     *
     * @param string $ip
     * @return bool
     */
    public static function isLocalIpv4($ip)
    {
        $ret = false;

        foreach (self::$PRIVATE_IPV4_RANGES as $range) {
            list($start, $end) = $range;

            $parts = array_map('intval', explode('.', $ip));

            $matchc = 0;
            foreach ($parts as $idx => $part) {
                if ($part >= $start[$idx] && $part <= $end[$idx]) {
                    $matchc ++;
                    continue;
                } else {
                    continue 2;
                }
            }

            return 4 === $matchcsv;
        }
        return $ret;
    }
}