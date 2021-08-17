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
 * Class for handling IPC between processes. The communication is handled using
 * socket streams and the data is transferred json encoded using binary mode with
 * null-byte termination.
 *
 * @copyright   copyright (c) 2015-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Messaging
{
    /**
     * Maximum block size to read from socket.
     *
     * @type    int
     */
    const BLOCK_SIZE = 4096;

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
     * @param   mixed               $msg                Message to write.
     */
    public function send(mixed $msg): void
    {
        $this->socketWrite($this->writer, $msg);
    }

    /**
     * Receive message from process.
     *
     * @return  mixed                              Received message or false if no message received.
     */
    public function recv(): mixed
    {
        $sockets = array($this->reader); $null = null;
        $changed = socket_select($sockets, $null, $null, 0);

        if ($changed === false) {
            throw new \Octris\Process\Exception\SocketException();
        } elseif ($changed > 0) {
            return $this->socketRead($this->reader);
        } else {
            return false;
        }
    }

    /**
     * Write to socket.
     *
     * @param   resource            $socket             Socket to write to.
     * @param   mixed               $msg                Message to write.
     */
    protected function socketWrite($socket, mixed $msg): void
    {
        $msg = json_encode($msg) . "\x00";          // add termination character
        $len = strlen($msg);

        do {
            $sent = socket_write($socket, $msg, $len);

            if ($sent === false) {
                throw new \Octris\Process\Exception\SocketException();
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
     * @return  mixed                                   Read value.
     */
    protected function socketRead($socket): mixed
    {
        $msg = '';

        do {
            //$chunk = socket_read($socket, self::BLOCK_SIZE);
            $bytes = socket_recv($socket, $chunk, self::BLOCK_SIZE, MSG_DONTWAIT);
            print $bytes;

            if ($bytes === false) {
                $code = socket_last_error($socket);

                if ($code != 11 && $code != 115) {
                    throw new \Octris\Process\Exception\SocketException();
                }
            } elseif ($bytes > 0) {
                $msg .= rtrim($chunk, "\x00");
            }
        } while($bytes > 0 && substr($chunk, -1) !== "\x00");

        if ($msg !== '') {
            $data = json_decode($msg, true);

            if (($code = json_last_error()) !== JSON_ERROR_NONE) {
                // unable to unserialize message
                throw new \Octris\Process\Exception\MessagingException(json_last_error_msg(), $code);
            }
        } else {
            $data = false;
        }

        return $data;
    }

    /**
     * Create pair of channels.
     */
    public static function create(): array
    {
        if (!socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $sockets_ch1) ||
            !socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $sockets_ch2)) {
            throw new \Octris\Process\Exception\SocketException();
        }

        return array(
            new static($sockets_ch2[0], $sockets_ch1[1]),
            new static($sockets_ch1[0], $sockets_ch2[1])
        );
    }
}
