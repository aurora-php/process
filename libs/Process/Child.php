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
 * Abstract child process class.
 *
 * @copyright   copyright (c) 2015-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
abstract class Child extends \Octris\Process
{
    /**
     * Constructor.
     *
     * @param   \Octris\Process\Messaging  $messaging          Messaging channel.
     */
    public function __construct(protected \Octris\Process\Messaging $messaging)
    {
        parent::__construct();
    }

    /**
     * Run process.
     */
    abstract public function run(): void;
}
