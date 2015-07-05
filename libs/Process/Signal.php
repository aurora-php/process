<?php

/*
 * This file is part of the 'octris/proc' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Process;

declare(ticks=100);

/**
 * Signal handling library.
 *
 * @copyright   copyright (c) 2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Signal
{
    /**
     * Registered event handlers.
     *
     * @type    array
     */
    protected static $handlers = array();

    /*
     * Static class.
     */
    protected function __construct() {}

    /**
     * Call event handlers for specified signal.
     *
     * @param   int             $signal                 Signal to call handlers for.
     */
    protected static function sigHandler($signal)
    {
        if (isset(self::$handlers[$signal])) {
            foreach (self::$handlers[$signal] as $handler) {
                $handler();
            }
        }
    }

    /**
     * Calls handlers of pending signals.
     */
    public static function dispatch()
    {
        pcntl_signal_dispatch();
    }

    /**
     * Add an event handler for a signal.
     */
    public static function addHandler($signal, callable $cb)
    {
        if (!isset(self::$handlers[$signal])) {
            self::$handlers[$signal] = array();

            pcntl_signal($signal, function() use ($signal) {
                self::sigHandler($signal);
            });
        }

        self::$handlers[$signal][] = $cb;
    }
}
