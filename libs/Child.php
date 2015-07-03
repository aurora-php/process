<?php

/*
 * This file is part of the 'octris/proc' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(ticks=1);

namespace Octris\Proc;

/**
 * Abstract child process class.
 *
 * @copyright   copyright (c) 2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
abstract class Child extends \Octris\Proc\Process
{
    /**
     * Constructor.
     *
     * @param   \Octris\Proc\Messaging  $messaging          Messaging channel.
     */
    protected function __construct(\Octris\Proc\Messaging $messaging)
    {
        parent::__construct($messaging);

        // signal handlers
        pcntl_signal(SIGTERM, function() {
            exit;
        });
    }

    /**
     * Run child process.
     */
    abstract public function run();
}
