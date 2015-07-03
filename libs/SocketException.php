<?php

/*
 * This file is part of the 'octris/proc' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Proc;

/**
 * Socket exception class.
 *
 * @copyright   copyright (c) 2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class SocketException extends \Exception
{
    /**
     * Constructor. If the parameters are omitted the error will be detected by the
     * internal socket error functions.
     */
    public function __construct($message = '', $code = 0, \Exception $previous = NULL)
    {
        if ($code === 0) {
            $code = socket_last_error();
            $message = socket_strerror($code);
        }

        parent::__construct($message, $code, $previous);
    }
}
