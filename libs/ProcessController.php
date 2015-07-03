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
 * Process controller.
 *
 * @copyright   copyright (c) 2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class ProcessController
{
    /**
     * Messaging channel.
     *
     * @type    \Octris\Proc\Messaging
     */
    protected $messaging;

    /**
     * Constructor.
     */
    public function __construct(\Octris\Proc\Messaging $messaging)
    {
        $this->messaging = $messaging;
    }

    /**
     * Send message to process.
     *
     * @param   string              $msg                Message to write.
     */
    public function send($msg)
    {
        $this->messaging->send($msg);
    }

    /**
     * Receive message from process.
     *
     * @return  string                                  Received message.
     */
    public function recv()
    {
        return $this->messaging->recv();
    }
}
