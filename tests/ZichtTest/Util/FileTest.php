<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
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
    }
}