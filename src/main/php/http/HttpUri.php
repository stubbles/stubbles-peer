<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer\http;
use stubbles\peer\HeaderList;
use stubbles\peer\MalformedUri;
use stubbles\peer\ParsedUri;
use stubbles\peer\Socket;
use stubbles\peer\Stream;
use stubbles\peer\Uri;
/**
 * Class for URIs of scheme hypertext transfer protocol.
 *
 * @api
 */
abstract class HttpUri extends Uri
{
    /**
     * @since   4.0.0
     */
    public static function fromParts(
            string $scheme,
            string $host,
            int $port = null,
            string $path = '/',
            string $queryString = null
    ): self {
        if (null !== $queryString && substr($queryString, 0, 1) !== '?') {
            $queryString = '?' . $queryString;
        }

        return self::fromString(
                $scheme
                . '://'
                . $host
                . (null === $port ? '' : ':' . ((string) $port))
                . $path
                . (string) $queryString
        );
    }

    /**
     * parses an uri out of a string
     *
     * @param   string  $rfc  optional  RFC to base validation on, defaults to Http::RFC_7230
     * @throws  \stubbles\peer\MalformedUri
     */
    public static function fromString(string $uriString, string $rfc = Http::RFC_7230): self
    {
        if (strlen($uriString) === 0) {
            throw new MalformedUri('Empty string is not a valid HTTP URI');
        }

        $uri = new ConstructedHttpUri(new ParsedUri($uriString));
        if ($uri->isValidForRfc($rfc)) {
            if (empty($uri->parsedUri->path())) {
                $uri->parsedUri = $uri->parsedUri->transpose(['path' => '/']);
            }

            return $uri;
        }

        throw new MalformedUri('The URI ' . $uriString . ' is not a valid HTTP URI');
    }

    /**
     * casts given value to an instance of HttpUri
     *
     * @since  4.0.0
     */
    public static function castFrom(string|self $value, string $name = 'Uri'): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return self::fromString($value);
    }

    /**
     * checks if uri is valid according to given rfc
     *
     * @throws  MalformedUri
     */
    private function isValidForRfc(string $rfc): bool
    {
        if ($this->parsedUri->hasUser() && Http::RFC_7230 === $rfc) {
            throw new MalformedUri(
                    'The URI ' . $this->parsedUri->asString()
                    . ' is not a valid HTTP URI according to ' . Http::RFC_7230
                    . ': contains userinfo, but this is disallowed'
            );
        }

        return $this->isSyntacticallyValid();
    }

    /**
     * checks whether given http uri exists, i.e. has a DNS entry
     *
     * @param  callable  $checkWith  optional  function to check dns record with
     * @since  7.1.0
     */
    public static function exists(string|self $httpUri, callable $checkWith = null): bool
    {
        if ($httpUri instanceof self) {
            return $httpUri->hasDnsRecord($checkWith);
        }

        if (empty($httpUri)) {
            return false;
        }

        try {
            return self::fromString($httpUri)->hasDnsRecord($checkWith);
        } catch (MalformedUri $murle) {
            return false;
        }
    }

    /**
     * checks whether given http uri is syntactically valid
     *
     * @param  mixed  $httpUri
     * @since  7.1.0
     */
    public static function isValid(mixed $httpUri): bool
    {
        if ($httpUri instanceof self) {
            return true;
        }

        if (empty($httpUri) || !is_string($httpUri)) {
            return false;
        }

        try {
            self::fromString($httpUri);
        } catch (MalformedUri $murle) {
            return false;
        }

        return true;
    }

    /**
     * Checks whether URI is a correct URI.
     */
    protected function isSyntacticallyValid(): bool
    {
        if (!parent::isSyntacticallyValid()) {
            return false;
        }

        if (!$this->parsedUri->schemeEquals(Http::SCHEME)
          && !$this->parsedUri->schemeEquals(Http::SCHEME_SSL)) {
            return false;
        }

        return true;
    }

    /**
     * checks whether host of uri is listed in dns
     *
     * @param  callable  $checkWith  optional  function to check dns record with
     */
    public function hasDnsRecord(callable $checkWith = null): bool
    {
        $checkdnsrr = $checkWith ?? 'checkdnsrr';
        if ($this->parsedUri->isLocalHost()
          || $checkdnsrr($this->parsedUri->hostname(), 'A')
          || $checkdnsrr($this->parsedUri->hostname(), 'AAAA')
          || $checkdnsrr($this->parsedUri->hostname(), 'CNAME')) {
            return true;
        }

        return false;
    }

    /**
     * returns hostname of the uri
     */
    public function hostname(): string
    {
        $hostname = parent::hostname();
        return null === $hostname ? '' : $hostname;
    }

    /**
     * checks whether the uri uses a default port or not
     *
     * Default ports are 80 for http and 443 for https
     */
    public function hasDefaultPort(): bool
    {
        if (
            !$this->parsedUri->hasPort()
            || ($this->isHttp() && $this->parsedUri->portEquals(Http::PORT))
            || ($this->isHttps() && $this->parsedUri->portEquals(Http::PORT_SSL))
        ) {
            return true;
        }

        return false;
    }

    /**
     * returns port of the uri
     */
    public function port(int $defaultPort = null): int
    {
        $port = parent::port();
        if (null !== $port) {
            return $port;
        }

        if ($this->isHttp()) {
            return Http::PORT;
        }

        return Http::PORT_SSL;
    }

    /**
     * returns a new http uri instance with new path
     *
     * @since  5.5.0
     */
    public function withPath(string $path): Uri
    {
        return new ConstructedHttpUri(
                $this->parsedUri->transpose(['path' => $path])
        );
    }

    /**
     * @since  2.0.0
     */
    public function isHttp(): bool
    {
        return $this->parsedUri->schemeEquals(Http::SCHEME);
    }

    /**
     * @since  2.0.0
     */
    public function isHttps(): bool
    {
        return $this->parsedUri->schemeEquals(Http::SCHEME_SSL);
    }

    /**
     * transposes uri to http
     *
     * @param  int  $port  optional  new port to use, defaults to 80
     * @since  2.0.0
     */
    public function toHttp(int $port = null): self
    {
        if ($this->isHttp()) {
            if (null !== $port && !$this->parsedUri->portEquals($port)) {
                return new ConstructedHttpUri($this->parsedUri->transpose(['port' => $port]));
            }

            return $this;
        }

        $changes = ['scheme' => Http::SCHEME];
        if ($this->parsedUri->hasPort()) {
            $changes['port'] = $port;
        }

        return new ConstructedHttpUri($this->parsedUri->transpose($changes));
    }

    /**
     * transposes uri to https
     *
     * @param  int  $port  optional  new port to use, defaults to 443
     * @since  2.0.0
     */
    public function toHttps(int $port = null): self
    {
        if ($this->isHttps()) {
            if (null !== $port && !$this->parsedUri->portEquals($port)) {
                return new ConstructedHttpUri($this->parsedUri->transpose(['port' => $port]));
            }

            return $this;
        }

        $changes = ['scheme' => Http::SCHEME_SSL];
        if ($this->parsedUri->hasPort()) {
            $changes['port'] = $port;
        }

        return new ConstructedHttpUri($this->parsedUri->transpose($changes));
    }

    /**
     * creates a http connectoon for this uri
     *
     * To submit a complete HTTP request use this:
     * <code>
     * $response = $uri->connect()->asUserAgent('Not Mozilla')
     *                            ->timeout(5)
     *                            ->usingHeader('X-Money', 'Euro')
     *                            ->get();
     * </code>
     */
    public function connect(HeaderList $headers = null): HttpConnection
    {
        return new HttpConnection($this, $headers);
    }

    /**
     * creates a socket to this uri
     *
     * @since  6.0.0
     */
    public function createSocket(): Socket
    {
        return new Socket(
            $this->hostname(),
            $this->port(),
            (($this->isHttps()) ? ('ssl://') : (null))
        );
    }

    /**
     * @since  2.0.0
     */
    public function openSocket(int $timeout = 5, callable $openWith = null): Stream
    {
        $socket = $this->createSocket();
        if (null !== $openWith) {
            $socket->openWith($openWith);
        }

        return $socket->connect()->setTimeout($timeout);
    }
}
