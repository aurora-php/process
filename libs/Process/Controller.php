<?php

declare(strict_types=1);

/*
 * This file is part of the 'octris/process' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Process;

/**
 * Process controller.
 *
 * @copyright   copyright (c) 2015-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Controller
{
    /**
     * Constructor.
     * 
     * @param   \Octris\Process\Messaging   $messaging  Instance of messaging service.
     * @param   int                         $pid        Process ID the controller belongs to.
     */
    public function __construct(protected \Octris\Process\Messaging $messaging, protected int $pid)
    {
    }

    /**
     * Test if the process is alive.
     * 
     * @return   bool                                   Returns true if the child process is still running.
     */
    public function isAlive(): bool
    {
        $result = pcntl_waitpid($this->pid, $status, WNOHANG);
        
        return ($result === 0);
    }
    
    /**
     * Return process ID.
     * 
     * @return  int                                     Process ID.
     */
    public function getPid(): int
    {
        return $this->pid;
    }
    
    /**
     * Send a signal to the child process. The signal can be one of those listed at:
     * http://php.net/manual/de/pcntl.constants.php.
     * 
     * @param   int                         $signal     Signal to send.
     */
    public function kill(int $signal): void
    {
        posix_kill($this->pid, $signal);
    }

    /**
     * Send message to process.
     *
     * @param   string              $msg                Message to write.
     */
    public function send(string $msg): void
    {
        $this->messaging->send($msg);
    }

    /**
     * Receive message from process.
     *
     * @return  string                                  Received message.
     */
    public function recv(): string
    {
        return $this->messaging->recv();
    }
}
