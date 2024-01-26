<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer;

use InvalidArgumentException;

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
     * @var  resource
     */
    private $resource;
    private float $timeout;

    /**
     * constructor
     *
     * @param   resource  $resource  actual socket resource
     * @throws  InvalidArgumentException
     */
    public function __construct($resource, private bool $usesTls = false)
    {
        if (!is_resource($resource) || get_resource_type($resource) !== 'stream') {
            throw new InvalidArgumentException('Given resource is not a socket stream');
        }

        $this->resource = $resource;
        $this->usesTls  = $usesTls;
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
     * @since  8.0.0
     */
    public function usesTls(): bool
    {
        return $this->usesTls;
    }

    public function setTimeout(int $seconds, int $microseconds = 0): self
    {
        $this->timeout = (float) ($seconds . '.' . $microseconds);
        stream_set_timeout($this->resource, $seconds, $microseconds);
        return $this;
    }

    /**
     * returns current timeout setting
     */
    public function timeout(): float
    {
        return $this->timeout;
    }

    /**
     * read from socket
     *
     * @throws  ConnectionFailure
     * @throws  Timeout
     */
    public function read(?int $length = null): string
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
                    sprintf(
                        'Reading of %d bytes failed: timeout of %d seconds exceeded.',
                        $length,
                        $this->timeout
                    )
                );
            }

            throw new ConnectionFailure(sprintf('Reading of %d bytes failed.', $length));
        }

        return $data;
    }

    public function readLine(): string
    {
        return rtrim($this->read());
    }

    /**
     * @throws  ConnectionFailure
     * @throws  Timeout
     */
    public function readBinary(int $length = 1024): string
    {
        $data = fread($this->resource, $length);
        if (false === $data) {
            if (stream_get_meta_data($this->resource)['timed_out']) {
                throw new Timeout(
                    sprintf(
                        'Reading of %d bytes failed: timeout of %d seconds exceeded.',
                        $length,
                        $this->timeout
                    )
                );
            }

            throw new ConnectionFailure(sprintf('Reading of %d bytes failed.', $length));
        }

        return $data;
    }

    /**
     * @throws  ConnectionFailure
     * @throws  Timeout
     */
    public function write(string $data): int
    {
        $length = fputs($this->resource, $data, strlen($data));
        if (false === $length) {
            if (stream_get_meta_data($this->resource)['timed_out']) {
                throw new Timeout(
                    sprintf(
                        'Writing of %d bytes failed: timeout of %d seconds exceeded.',
                        strlen($data),
                        $this->timeout
                    )
                );
            }

            throw new ConnectionFailure(sprintf('Writing of %d bytes failed.', strlen($data)));
        }

        return $length;
    }

    public function eof(): bool
    {
        return feof($this->resource);
    }
}
