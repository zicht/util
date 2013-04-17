<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Util;

use Zicht\Util\Debug;
class DebugTestClass
{
    function __construct($vars)
    {
        foreach ($vars as $key => $value) {
            $this->$key = $value;
        }
    }
}

class DebugTest extends \PHPUnit_Framework_TestCase
{
    function testLines()
    {
        $this->assertEquals(array('', 'a', 'b'), Debug::lines("\na\nb"));
        $this->assertEquals(array('', 'a', 'b', ''), Debug::lines("\na\nb\n\n"));
    }


    function testFormatContext() {
        $this->assertEquals("1. a\n2. b\n3. c\n",   Debug::formatContext("a\nb\nc", 1));
        $this->assertEquals("1. a\n2. b\n",         Debug::formatContext("a\nb", 1));
        $this->assertEquals("1. a\n2. b\n",         Debug::formatContext("a\nb", 2));
        $this->assertEquals("1. a\n",               Debug::formatContext("a\n", 1));
        $this->assertEquals("1. a\n",               Debug::formatContext("a", 1));

        $this->assertEquals("2. b\n3. c\n4. d\n5. e\n",         Debug::formatContext("a\nb\nc\nd\ne", 4));
        $this->assertEquals("2. b\n3. c\n4. d\n5. e\n6. f\n",   Debug::formatContext("a\nb\nc\nd\ne\nf", 4));
        $this->assertEquals("2. b\n3. c\n4. d\n5. e\n6. f\n",   Debug::formatContext("a\nb\nc\nd\ne\nf\ng", 4));
    }


    /**
     * @expectedException \OutOfBoundsException
     * @dataProvider invalidLines
     */
    function testFormatContextWillThrowOutOfBoundsExceptionIfLineNumberIsInvalid($n)
    {
        Debug::formatContext("", $n);
    }
    function invalidLines()
    {
        return array(
            array(-1),
            array(0),
            array(2),
        );
    }

    function testFormatContextWillNotThrowExceptionOnEmptyStringAnd()
    {
        Debug::formatContext("", 1);
    }


    function testIndent()
    {
        $this->assertEquals('    a',            Debug::indent('a'));
        $this->assertEquals("    a\n",          Debug::indent("a\n"));
        $this->assertEquals("    a\n    b",     Debug::indent("a\nb"));
        $this->assertEquals("    a\n    b\n",   Debug::indent("a\nb\n"));
    }


    /**
     * @dataProvider dumpCases
     */
    function testDump($str, $input, $maxDepth = null, $maxValueLen = null)
    {
        $refl = new \ReflectionClass('Zicht\Util\Debug');
        /** @var $params \ReflectionParameter[] */
        $params = $refl->getMethod('dump')->getParameters();

        if (null === $maxDepth) {
            $maxDepth = $params[1]->getDefaultValue();
        }
        if (null === $maxValueLen) {
            $maxValueLen = $params[2]->getDefaultValue();
        }
        $this->assertEquals("$str\n", Debug::dump($input, $maxDepth, $maxValueLen));
    }
    function dumpCases() {
        return array(
            array('<array(0)>(empty)', array()),
            array('<array(2)> (...)', array('a', 'b'), 0),
            array(
                "<array(2)>\n"
                . "[0] = \"a\"\n"
                . "[1] = \"b\"",
                array('a', 'b')
            ),
            array('10', 10),
            array('"blah"', 'blah'),
            array('true', true),
            array('false', false),
            array('null', null),
            array('1.010000', 1.01),
            array('1.010000', 1.01),
            array('resource (stream)', fopen(__FILE__, 'r')),
            array('"b" ... (3 more)', 'blah', null, 1),
            array(
                "<array(2)>\n"
                . "[0] = \"a\"\n"
                . "[1]<array(2)> (...)",
                array('a', array(1, 2)),
                1
            ),
            array(
                "<stdClass>\n"
                . "->a = 1\n"
                . "->b = 2",
                (object) array('a' => 1, 'b' => 2)
            ),
            array(
                "<ZichtTest\\Util\\DebugTestClass>\n"
                . "->a = 1\n"
                . "->b = 2",
                new DebugTestClass(array('a' => 1, 'b' => 2))
            ),
            array(
                "<array(2)>\n"
                . "[0] = \"a\"\n"
                . "[1]<array(2)> (...)",
                array('a', array(1, 2)),
                1
            ),
        );
    }
}