<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Util;

use Zicht\Util\File;

class FileTest extends \PHPUnit_Framework_TestCase
{
    function testSanitize()
    {
        $this->assertEquals('file.jpg', File::sanitize('some/file.jpg'));
        $this->assertEquals('some-file.jpg', File::sanitize('some/file.jpg', false));
        $this->assertEquals('some-file.bin', File::sanitize('some/file', false));
        $this->assertEquals('some.bin', File::sanitize('some/file', false, 8));
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    function testSanitizeWillThrowExceptionIfMaxLengthIsNotSane()
    {
        File::sanitize("w00t", true, 3);
    }
}