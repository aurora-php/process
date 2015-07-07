<?php

/*
 * This file is part of the 'octris/process' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris;

/**
 * Abstract process class.
 *
 * @copyright   copyright (c) 2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Process
{
    /**
     * Child processes.
     *
     * @type    array
     */
    protected $processes = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        register_shutdown_function(function() {
            foreach ($this->processes as $pid => $process) {
                posix_kill($pid, SIGHUP);
            }
        });
    }

    /**
     * Detach process -- for example to daemonize it.
     */
    public function detach()
    {
        // fork process
        $pid = pcntl_fork();

        if ($pid < 0) {
            throw new \Octris\Process\Exception\ProcessException();
        }

        if ($pid) {
            // end process, but make sure to not send SIGHUP to any already forked processes
            $this->processes = array();
            exit(0);
        }

        // make process the session leader
        posix_setsid();
        usleep(100000);

        // fork again as session leader
        $pid = pcntl_fork();

        if ($pid < 0) {
            throw new \Octris\Process\Exception\ProcessException();
        }

        if ($pid) {
            // end process, but make sure to not send SIGHUP to any already forked processes
            $this->processes = array();
            exit(0);
        }
    }

    /**
     * Fork process.
     *
     * @param   string          $class              Class to fork as child process.
     * @return  \Octris\Proc\ProcessController      Instance of controller for child process.
     */
    public function fork($class)
    {
        if (!is_string($class)) {
            throw new \InvalidArgumentException('Parameter is required to be a class name');
        } elseif (!class_exists($class)) {
            throw new \InvalidArgumentException('Parameter is required to be a name of an existing class');
        } elseif (!is_subclass_of($class, '\Octris\Process\Child')) {
            throw new \InvalidArgumentException('Parameter is required to be a subclass of "\Octris\Process\Child"');
        }

        // create communication channels
        list($ch1, $ch2) = \Octris\Process\Messaging::create();

        // fork process
        $pid = pcntl_fork();

        if ($pid < 0) {
            throw new \Octris\Process\Exception\ProcessException();
        }

        if (!$pid) {
            // child process
            unset($ch2);

            $child = new $class($ch1);
            $child->run();
            exit;
        } else {
            // parent process
            unset($ch1);

            $controller = new \Octris\Process\Controller($ch2, $pid);

            $this->processes[$pid] = $controller;

            return $controller;
        }
    }
}
