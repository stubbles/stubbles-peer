<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\peer
 */
namespace stubbles\peer;
/**
 * Class for operations on socket/stream connections.
 *
 * @api
 * @since  6.0.0
 */
class Stream
{
    /**
     * internal resource pointer
     *
     * @type  resource
     */
    private $resource;
    /**
     * timeout
     *
     * @type  int
     */
    private $timeout;
    /**
     * input stream to read data from stream with
     *
     * @type  \stubbles\streams\InputStream
     */
    private $inputStream;
    /**
     * output stream to read data from stream with
     *
     * @type  \stubbles\streams\OutputStream
     */
    private $outputStream;

    /**
     * constructor
     *
     * @param   resource  $resource  actual socket resource
     * @throws  \InvalidArgumentException
     */
    public function __construct($resource)
    {
        if (!is_resource($resource) || get_resource_type($resource) !== 'stream') {
            throw new \InvalidArgumentException('Given resource is not a socket stream');
        }

        $this->resource = $resource;
        $this->timeout  = (float) ini_get('default_socket_timeout');
    }

    /**
     * destructor
     */
    public function __destruct()
    {
        // on unit tests resource might be closed from outside
        if (is_resource($this->resource)) {
            fclose($this->resource);
        }
    }

    /**
     * set timeout for connections
     *
     * @param   int  $seconds       timeout for connection in seconds
     * @param   int  $microseconds  optional  timeout for connection in microseconds
     * @return  $this
     */
    public function setTimeout(int $seconds, int $microseconds = 0): self
    {
        $this->timeout = (float) ($seconds . '.' . $microseconds);
        stream_set_timeout($this->resource, $seconds, $microseconds);
        return $this;
    }

    /**
     * returns current timeout setting
     *
     * @return  float
     */
    public function timeout(): float
    {
        return $this->timeout;
    }

    /**
     * read from socket
     *
     * @param   int  $length  optional  length of data to read
     * @return  string  data read from socket
     * @throws  \stubbles\peer\ConnectionFailure
     * @throws  \stubbles\peer\Timeout
     */
    public function read(int $length = null): string
    {
        // can not call fgets with null when not specified
        $data = null === $length ? fgets($this->resource) : fgets($this->resource, $length);
        if (false === $data) {
            // fgets() returns false on eof while feof() returned false before
            // but will now return true
            if ($this->eof()) {
                return '';
            }

            if (stream_get_meta_data($this->resource)['timed_out']) {
                throw new Timeout(
                        'Reading of ' . $length . ' bytes failed: timeout of '
                        . $this->timeout . ' seconds exceeded'
                );
            }

            throw new ConnectionFailure(
                    'Reading of ' . $length . ' bytes failed.'
            );
        }

        return $data;
    }

    /**
     * read a whole line from socket
     *
     * @return  string  data read from socket
     */
    public function readLine(): string
    {
        return rtrim($this->read());
    }

    /**
     * read binary data from socket
     *
     * @param   int  $length  length of data to read
     * @return  string  data read from socket
     * @throws  \stubbles\peer\ConnectionFailure
     * @throws  \stubbles\peer\Timeout
     */
    public function readBinary(int $length = 1024): string
    {
        $data = fread($this->resource, $length);
        if (false === $data) {
            if (stream_get_meta_data($this->resource)['timed_out']) {
                throw new Timeout(
                        'Reading of ' . $length . ' bytes failed: timeout of '
                        . $this->timeout . ' seconds exceeded'
                );
            }

            throw new ConnectionFailure(
                    'Reading of ' . $length . ' bytes failed.'
            );
        }

        return $data;
    }

    /**
     * write data to socket
     *
     * @param   string  $data  data to write
     * @return  int  amount of bytes written to socket
     * @throws  \stubbles\peer\ConnectionFailure
     * @throws  \stubbles\peer\Timeout
     */
    public function write(string $data): int
    {
        $length = fputs($this->resource, $data, strlen($data));
        if (false === $length) {
            if (stream_get_meta_data($this->resource)['timed_out']) {
                throw new Timeout(
                        'Writing of ' . strlen($data) . ' bytes failed:'
                        . ' timeout of ' . $this->timeout . ' seconds exceeded'
                );
            }

            throw new ConnectionFailure(
                    'Writing of ' . strlen($data) . ' bytes failed.'
            );
        }

        return $length;
    }

    /**
     * check if we reached end of data
     *
     * @return  bool
     */
    public function eof(): bool
    {
        return feof($this->resource);
    }
}
