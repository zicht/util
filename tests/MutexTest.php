<?php
/**
 * @copyright Zicht online <http://zicht.nl>
 */

namespace Zicht\Util\Test;

use PHPUnit\Framework\TestCase;
use Zicht\Util\Mutex;

/**
 * @covers Zicht\Util\Mutex
 */
class MutexTest extends TestCase
{
    public function setUp(): void
    {
        $this->fsMock = $this->getMockBuilder('stdClass')->setMethods(array('fopen', 'fclose', 'flock'))->getMock();
        $this->filesystem = array(
            'fopen'     => array($this->fsMock, 'fopen'),
            'fclose'    => array($this->fsMock, 'fclose'),
            'flock'     => array($this->fsMock, 'flock'),
        );
    }

    public function testMutexWillAbortIfFileCannotBeOpened()
    {
        $this->expectException('\RuntimeException');
        $mutex = new Mutex('foo/bar/baz', true, $this->filesystem);

        $this->fsMock->expects($this->once())->method('fopen')->with('foo/bar/baz')->will($this->returnValue(false));
        $this->fsMock->expects($this->never())->method('flock');
        $mutex->run(function() {});
    }

    public function testMutexWillFailIfLockCannotBeAcquired()
    {
        $mutex = new Mutex('foo/bar/baz', true, $this->filesystem);

        $fp = rand(1, 100);
        $this->fsMock->expects($this->once())->method('fopen')->with('foo/bar/baz')->will($this->returnValue($fp));
        $this->fsMock->expects($this->once())->method('flock')->with($fp, LOCK_EX)->will($this->returnValue(false));
        $this->fsMock->expects($this->once())->method('fclose')->with($fp)->will($this->returnValue(true));
        $wasRun = null;
        $result = $mutex->run(function() {}, $wasRun);
        $this->assertFalse($result);
        $this->assertFalse($wasRun);
    }

    public function testMutexWillReleaseLockIfExceptionIsThrown()
    {
        $mutex = new Mutex('foo/bar/baz', true, $this->filesystem);

        $fp = rand(1, 100);
        $this->fsMock->expects($this->once())->method('fopen')->with('foo/bar/baz')->will($this->returnValue($fp));
        $this->fsMock->expects($this->at(1))->method('flock')->with($fp, LOCK_EX)->will($this->returnValue(true));
        $this->fsMock->expects($this->at(2))->method('flock')->with($fp, LOCK_UN)->will($this->returnValue(true));
        $this->fsMock->expects($this->once())->method('fclose')->with($fp)->will($this->returnValue(true));

        $wasRun = $e = null;
        try {
            $mutex->run(function() {
                throw new \Exception("foo");
            }, $wasRun);
        } catch(\Exception $e) {
        }
        $this->assertTrue($wasRun);
        $this->assertInstanceOf('\Exception', $e);
    }


    /**
     * @dataProvider lockingModes
     */
    public function testMutexWillLockFile($blocking, $expectedLockFlags)
    {
        $mutex = new Mutex('foo/bar/baz', $blocking, $this->filesystem);

        $fp = rand(1, 100);
        $this->fsMock->expects($this->once())->method('fopen')->with('foo/bar/baz')->will($this->returnValue($fp));
        $this->fsMock->expects($this->at(1))->method('flock')->with($fp, $expectedLockFlags)->will($this->returnValue(true));
        $this->fsMock->expects($this->at(2))->method('flock')->with($fp, LOCK_UN)->will($this->returnValue(true));

        $wasRun = null;
        $return = $mutex->run(function() {
            return 'return value';
        }, $wasRun);
        $this->assertEquals('return value', $return);
        $this->assertTrue($wasRun, $return);
    }
    public function lockingModes()
    {
        return array(
            array(true, LOCK_EX),
            array(false, LOCK_EX | LOCK_NB)
        );
    }
}