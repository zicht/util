<?php
/**
 * @copyright Zicht online <http://zicht.nl>
 */
namespace Zicht\Util\Test;

use PHPUnit\Framework\TestCase;
use Zicht\Util\Xml;

class XmlTest extends TestCase
{
    /**
     * @dataProvider formattedData
     * @param string $in
     * @param string $expect
     * @return void
     */
    public function testFormat($in, $expect)
    {
        $this->assertEquals($expect, Xml::format($in));
    }


    /**
     */
    public function formattedData()
    {
        return array(
            array(
                '<xml></xml>',
                "<?xml version=\"1.0\"?>\n<xml/>\n"
            ),
            array(
                '<xml />',
                "<?xml version=\"1.0\"?>\n<xml/>\n"
            ),
        );
    }
}