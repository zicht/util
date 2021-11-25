<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Util;

use PHPUnit\Framework\TestCase;
use Zicht\Util\TreeTools;

class TreeToolsTest extends TestCase
{
    /**
     * @dataProvider dataGet
     * @param $subject
     * @param $path
     * @param $expectedValue
     */
    function testGetByPath($subject, $path, $expectedValue)
    {
        $this->assertEquals($expectedValue, TreeTools::getByPath($subject, $path));
    }

    function dataGet()
    {
        return array(
            array(array('a' => array('b' => 'c')), array('a', 'b'), 'c'),
            array((object)array('a' => (object)array('b' => 'c')), array('a', 'b'), 'c'),
            array(array(), array('a', 'b'), null),
        );
    }

    /**
     * @dataProvider dataSet
     * @param $subject
     * @param $path
     * @param $value
     * @param $expected
     */
    function testSetByPath($subject, $path, $value, $expected)
    {
        $this->assertEquals($expected, TreeTools::setByPath($subject, $path, $value));
        $this->assertEquals($expected, $subject);

    }

    function dataSet()
    {
        return array(
            array(
                array('a' => array('b' => 'c')),
                array('a', 'b'),
                'x',
                array('a' => array('b' => 'x'))
            ),
            array(
                array(),
                array('a', 'b'),
                'x',
                array('a' => array('b' => 'x'))
            )
        );
    }
}