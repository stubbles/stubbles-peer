<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer;
/**
 * Represents a socket to which a connection can be established.
 *
 * @api
 */
class Socket
{
    /**
     * host to open socket to
     *
     * @var  string
     */
    private $host;
    /**
     * port to use for opening the socket
     *
     * @var  int
     */
    private $port;
    /**
     * prefix for host, e.g. ssl://
     *
     * @var  string|null
     */
    private $prefix;
    /**
     * @var  callable
     */
    private $fsockopen = '\fsockopen';

    /**
     * constructor
     *
     * @param   string  $host    host to open socket to
     * @param   int     $port    port to use for opening the socket
     * @param   string  $prefix  prefix for host, e.g. ssl://
     * @throws  \InvalidArgumentException
     */
    public function __construct(string $host, int $port = 80, string $prefix = null)
    {
        if (empty($host)) {
            throw new \InvalidArgumentException('Host can not be empty');
        }

        if (0 > $port) {
            throw new \InvalidArgumentException('Port can not be negative');
        }

        $this->host   = $host;
        $this->port   = $port;
        $this->prefix = $prefix;
    }

    /**
     * sets function to open socket with
     *
     * @param   callable  $fsockopen
     * @return  Socket
     * @since   8.1.0
     */
    public function openWith(callable $fsockopen): self
    {
        $this->fsockopen = $fsockopen;
        return $this;
    }

    /**
     * opens a connection to host
     *
     * @param   float  $connectTimeout  optional timeout for establishing the connection, defaults to 1 second
     * @return  \stubbles\peer\Stream
     * @throws  \stubbles\peer\ConnectionFailure
     */
    public function connect(float $connectTimeout = 1.0): Stream
    {
        $errno  = 0;
        $errstr = '';
        $fsockopen = $this->fsockopen;
        $resource = $fsockopen(
                $this->prefix . $this->host,
                $this->port,
                $errno,
                $errstr,
                $connectTimeout
        );
        if (false === $resource) {
            throw new ConnectionFailure(
                    'Connect to ' . $this->prefix . $this->host . ':'. $this->port
                    . ' within ' . $connectTimeout . ' second'
                    . (1 == $connectTimeout ? '' : 's') . ' failed: '
                    . $errstr . ' (' . $errno . ').'
            );
        }

        return new Stream($resource, $this->usesSsl());
    }

    /**
     * checks if socket uses a secure connection
     *
     * @return  bool
     * @since   4.0.0
     */
    public function usesSsl(): bool
    {
        return 'ssl://' === $this->prefix || 'tls://' === $this->prefix;
    }
}
