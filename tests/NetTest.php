<?php
/**
 * @copyright Zicht online <http://zicht.nl>
 */

namespace Zicht\Util\Test;

use PHPUnit\Framework\TestCase;
use Zicht\Util\Net;

class NetTest extends TestCase
{
    /**
     * @dataProvider ipTests
     */
    public function testIsLocalIpv4($is, $ip)
    {
        $this->assertEquals($is, Net::isLocalIpv4($ip));
    }


    public function ipTests()
    {
        return array(
            array(true, '127.0.0.1'),
            array(true, '10.0.0.1'),
            array(true, '192.168.0.12'),
            array(false, '193.168.0.12'),
            array(false, '1.2.3.4'),
            array(false, ':::0'),
            array(false, 'invalid'),
        );
    }
}