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
 * Represents a socket to which a connection can be established.
 *
 * @api
 */
class Socket
{
    /** @var  callable */
    private $fsockopen = '\fsockopen';

    /**
     * @param   string  $prefix  prefix for host, e.g. ssl://
     * @throws  InvalidArgumentException
     */
    public function __construct(private string $host, private int $port = 80, private ?string $prefix = null)
    {
        if (empty($host)) {
            throw new InvalidArgumentException('Host can not be empty');
        }

        if (0 > $port) {
            throw new InvalidArgumentException('Port can not be negative');
        }
    }

    /**
     * sets function to open socket with
     *
     * @since  8.1.0
     */
    public function openWith(callable $fsockopen): self
    {
        $this->fsockopen = $fsockopen;
        return $this;
    }

    /**
     * @throws  ConnectionFailure
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
                sprintf(
                    'Connect to %s%s:%d within %d second%s failed: %s (%d)',
                    $this->prefix,
                    $this->host,
                    $this->port,
                    $connectTimeout,
                    1 == $connectTimeout ? '' : 's',
                    $errstr,
                    $errno
                )
            );
        }

        return new Stream($resource, $this->usesSsl());
    }

    /**
     * @since  4.0.0
     */
    public function usesSsl(): bool
    {
        return 'ssl://' === $this->prefix || 'tls://' === $this->prefix;
    }
}
