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

/**
 * Class for handling IPC between processes. The communication is handled using
 * socket streams and the data is transferred json encoded using binary mode with
 * null-byte termination.
 *
 * @copyright   copyright (c) 2015 by Harald Lapp
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
     * Error types.
     *
     * @type    array
     */
    private static $errors = array(
        JSON_ERROR_NONE => 'No error',
        JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX => 'Syntax error',
        JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
        JSON_ERROR_RECURSION => 'One or more recursive references in the value to be encoded',
        JSON_ERROR_INF_OR_NAN => 'One or more NAN or INF values in the value to be encoded',
        JSON_ERROR_UNSUPPORTED_TYPE => 'A value of a type that cannot be encoded was given'
    );

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
        $this->socketWrite($this->writer, $msg);
    }

    /**
     * Receive message from process.
     *
     * @return  string|bool                            Received message or false if no message received.
     */
    public function recv()
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
    protected function socketWrite($socket, $msg)
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
    protected function socketRead($socket)
    {
        $msg = '';

        do {
            $chunk = socket_read($socket, self::BLOCK_SIZE);

            if ($chunk === false) {
                $code = socket_last_error($socket);

                if ($code != 11 && $code != 115) {
                    throw new \Octris\Process\Exception\SocketException();
                }
            } else {
                $msg .= rtrim($chunk, "\x00");
            }
        } while(substr($chunk, -1) !== "\x00");

        $msg = json_decode($msg, true);

        if (is_null($msg) && ($code = json_last_error()) !== JSON_ERROR_NONE) {
            // unable to unserialize message
            if (isset(self::$errors[$code])) {
                $message = self::$errors[$code];
            } else {
                $message = 'Unknown error';
            }

            throw new \Octris\Process\Exception\MessagingException($message, $code);
        }

        return $msg;
    }

    /**
     * Create pair of channels.
     */
    public static function create()
    {
        if (!socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $sockets_ch1) ||
            !socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $sockets_ch2)) {
            throw new \Octris\Process\Exception\SocketException();
        }

        socket_set_nonblock($sockets_ch2[0]);
        socket_set_nonblock($sockets_ch1[0]);

        return array(
            new static($sockets_ch2[0], $sockets_ch1[1]),
            new static($sockets_ch1[0], $sockets_ch2[1])
        );
    }
}
