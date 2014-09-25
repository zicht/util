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
    public static $PRIVATE_IPV4_RANGES = array(
        // 10.0.0.0 ... 10.255.255.255
        array(0x0a000000, 0x0affffff),
        // 172.16.0.0 ... 172.31.255.255
        array(0xac100000, 0xac1fffff),
        // 192.168.0.0 ... 192.168.255.255
        array(0xc0a80000, 0xc0a8ffff),
        // 169.254.0.0 ... 169.254.255.255
        array(0xa9fe0000, 0xa9feffff),
        // 127.0.0.0 ... 127.255.255.255
        array(0x7f000000, 0x7fffffff)
    );


    /**
     * Check if the given IPv4 address is local
     *
     * @param string $ip
     * @return bool
     */
    public static function isLocalIpv4($ip)
    {
        $ret = false;

        $ipLong = ip2long($ip);

        if (false !== $ipLong) {
            foreach (self::$PRIVATE_IPV4_RANGES as $range) {
                list($start, $end) = $range;

                // IF IS PRIVATE
                if ($ipLong >= $start && $ipLong <= $end) {
                    $ret = true;
                    break;
                }
            }
        }

        return $ret;
    }
}