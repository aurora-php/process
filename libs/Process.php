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

namespace Octris;

/**
 * Abstract process class.
 *
 * @copyright   copyright (c) 2015-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Process
{
    /**
     * Child processes.
     *
     * @type    array
     */
    protected array $processes = [];

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

        \Octris\Process\Signal::addHandler(SIGCHLD, function ($signal) {
            while (($pid = pcntl_waitpid(-1, $status, WNOHANG)) > 0) {
                unset($this->processes[$pid]);
            }
        });
    }

    /**
     * Set title of process. Note that this does currently not seem to work on macOS.
     *
     * @param   string          $title              process title.
     */
    public function setProcessTitle(string $title): void
    {
        @cli_set_process_title($title);
    }

    /**
     * Detach process -- for example to daemonize it.
     */
    public function detach(): void
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
     * @return  \Octris\Process\Controller          Instance of controller for child process.
     */
    public function fork(string $class): \Octris\Process\Controller
    {
        if (!class_exists($class)) {
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

            unset($ch1);

            pcntl_exec('/bin/sh', [ '-c', 'true' ]);
        }

        // parent process
        unset($ch1);

        $controller = new \Octris\Process\Controller($ch2, $pid);

        $this->processes[$pid] = $controller;

        return $controller;
    }
}
