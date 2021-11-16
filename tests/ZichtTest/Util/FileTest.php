<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Util;

use PHPUnit\Framework\TestCase;
use Zicht\Util\File;

class FileTest extends TestCase
{
    function testSanitize()
    {
        $this->assertEquals('file.jpg', File::sanitize('some/file.jpg'));
        $this->assertEquals('some-file.jpg', File::sanitize('some/file.jpg', false));
        $this->assertEquals('some-file.bin', File::sanitize('some/file', false));
        $this->assertEquals('some.bin', File::sanitize('some/file', false, 8));
    }


    function testSanitizeWillThrowExceptionIfMaxLengthIsNotSane()
    {
        $this->expectException('\InvalidArgumentException');
        File::sanitize("w00t", true, 3);
    }
}