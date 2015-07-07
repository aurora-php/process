<?php

/*
 * This file is part of the 'octris/process' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Process\Exception;

/**
 * Process exception class.
 *
 * @copyright   copyright (c) 2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class ProcessException extends \Exception implements \Octris\Process\IException
{
    /**
     * Constructor. If the parameters are omitted the error will be detected by the
     * internal pcntl error functions.
     */
    public function __construct($message = '', $code = 0, \Exception $previous = NULL)
    {
        if ($code === 0) {
            $code = pcntl_get_last_error();
            $message = pcntl_strerror($code);
        }

        parent::__construct($message, $code, $previous);
    }
}
