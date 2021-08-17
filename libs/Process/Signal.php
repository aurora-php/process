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

// activate async signal handling
pcntl_async_signals(true);

/**
 * Signal handling library.
 *
 * @copyright   copyright (c) 2015-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Signal
{
    /**
     * Registered event handlers.
     *
     * @type    array
     */
    protected static array $handlers = [];

    /*
     * Static class.
     */
    protected function __construct() {}

    /**
     * Call event handlers for specified signal.
     *
     * @param   int             $signal                 Signal to call handlers for.
     */
    protected static function sigHandler(int $signal): void
    {
        if (isset(self::$handlers[$signal])) {
            foreach (self::$handlers[$signal] as $handler) {
                $handler($signal);
            }
        }
    }

    /**
     * Add an event handler for a signal.
     *
     * @param   int             $signal                 Signal to add handler for.
     * @param   callable        $cb                     Signal handler.
     */
    public static function addHandler(int $signal, callable $cb)
    {
        if (!isset(self::$handlers[$signal])) {
            self::$handlers[$signal] = array();

            pcntl_signal($signal, function ($signo) {
                self::sigHandler($signo);
            });
        }

        self::$handlers[$signal][] = $cb;
    }
}
