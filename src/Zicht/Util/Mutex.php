<?php
/**
 * @copyright Zicht online <http://zicht.nl>
 */

namespace Zicht\Util;

/**
 * Mutex wrapper class, using an flock to acquire a lock and running a piece of code in mutex
 *
 * Usage:
 * $mutex = new Mutex('/path/to/lockfile', false);
 * $mutex->run(function() {
 *     // do something that must be mutually exclusive.
 * });
 */
class Mutex
{
    /**
     * Construct the mutex
     *
     * @param string $file
     * @param bool $blocking
     * @param array $filesystem Used to mock the filesystem for testing purposes
     */
    public function __construct($file, $blocking = false, array $filesystem = [])
    {
        $this->file = $file;
        $this->flags = LOCK_EX;
        if (!$blocking) {
            $this->flags |= LOCK_NB;
        }
        $this->filesystem = $filesystem + [
                'fopen' => 'fopen',
                'fclose' => 'fclose',
                'flock' => 'flock'
            ];
    }

    /**
     * Run the runnable code, typically a closure.
     *
     * The $ran parameter is set to true when the code was run. the return value of the closure is used as a return
     * value.
     *
     * If the lock file could not be opened, a \RuntimeException is thrown
     * If the lock could not be acquired, false is returned, and the $run parameter will be set to false.
     * If the runner throws an exception, the lock is released and the exception is thrown after that.
     *
     * @param callable $runnable
     * @param null &$ran
     * @return bool|mixed
     *
     * @throws \RuntimeException
     * @throws \Exception
     *
     */
    public function run($runnable, &$ran = null)
    {
        $ran = false;
        $fd = call_user_func($this->filesystem['fopen'], $this->file, 'w');

        if (!$fd) {
            throw new \RuntimeException("Could not open file {$this->file}");
        }

        $hasLock = call_user_func($this->filesystem['flock'], $fd, $this->flags);
        if (!$hasLock) {
            call_user_func($this->filesystem['fclose'], $fd);
            // lock failed
            return false;
        }
        $exception = false;
        $result = null;
        try {
            $result = call_user_func($runnable);
        } catch (\Exception $exception) {
        }

        call_user_func($this->filesystem['flock'], $fd, LOCK_UN);
        call_user_func($this->filesystem['fclose'], $fd);

        $ran = true;
        if ($exception !== false) {
            throw $exception;
        }
        return $result;
    }
}
