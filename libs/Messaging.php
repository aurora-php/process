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
 * Class for handling IPC between processes.
 *
 * @copyright   copyright (c) 2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Messaging
{
    /**
     * Socket handle for receiving messages from process.
     *
     * @type    resource
     */
    protected $reader;

    /**
     * Socket handle for sending messages to process.
     *
     * @type    resource
     */
    protected $writer;

    /**
     * Constructor.
     */
    protected function __construct($reader, $writer)
    {
        $this->reader = $reader;
        $this->writer = $writer;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        socket_close($this->reader);
        socket_close($this->writer);
    }

    /**
     * Send message to process.
     *
     * @param   string              $msg                Message to write.
     */
    public function send($msg)
    {
        self::socketWrite($this->writer, $msg);
    }

    /**
     * Receive message from process.
     *
     * @return  string                                  Received message.
     */
    public function recv()
    {
        return self::socketRead($this->reader);
    }

    /**
     * Write to socket.
     *
     * @param   resource            $socket             Socket to write to.
     * @param   string              $msg                Message to write.
     */
    protected static function socketWrite($socket, $msg)
    {
        $len = strlen($msg);

        do {
            $sent = socket_write($socket, $msg, $len);

            if ($sent === false) {
                throw new \Octris\Proc\SocketException();
            } elseif ($sent < $len) {
                $msg = substr($msg, $sent);
                $len -= $sent;
            } else {
                break;
            }
        } while(true);
    }

    /**
     * Read from socket.
     *
     * @param   resource            $socket             Socket to read from.
     */
    protected static function socketRead($socket)
    {
        $msg = '';

        do {
            $read = socket_read($socket, MAXLINE);

            if ($read === false) {
                throw new \Octris\Proc\SocketError();
            } elseif ($read === '') {
                break;
            } else {
                $msg .= $read;
            }
        } while(true);

        return $msg;
    }

    /**
     * Create pair of channels.
     */
    public static function create()
    {
        if (!socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $sockets_ch1) ||
            !socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $sockets_ch2)) {
            throw new \Octris\Proc\SocketException();
        }

        return array(
            new static($sockets_ch2[0], $sockets_ch1[1]),
            new static($sockets_ch1[0], $sockets_ch2[1])
        );
    }
}
