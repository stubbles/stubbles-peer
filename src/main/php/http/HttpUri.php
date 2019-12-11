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
     * creates http uri from given uri parts
     *
     * @param   string  $scheme       scheme of http uri, must be either http or https
     * @param   string  $host         host name of http uri
     * @param   int     $port         optional  port of http uri
     * @param   string  $path         optional  path of http uri, defaults to /
     * @param   string  $queryString  optional  query string of http uri
     * @return  \stubbles\peer\http\HttpUri
     * @since   4.0.0
     */
    public static function fromParts(
            string $scheme,
            string $host,
            int $port = null,
            string $path = '/',
            string $queryString = null
    ): self {
        return self::fromString(
                $scheme
                . '://'
                . $host
                . (null === $port ? '' : ':' . ((string) $port))
                . $path
                . ((null !== $queryString) ? (substr($queryString, 0, 1) !== '?' ? '?' . $queryString : $queryString) : $queryString)
        );
    }

    /**
     * parses an uri out of a string
     *
     * @param   string  $uriString  string to create instance from
     * @param   string  $rfc        optional  RFC to base validation on, defaults to Http::RFC_7230
     * @return  \stubbles\peer\http\HttpUri
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
     * @param   string|\stubbles\peer\http\HttpUri  $value  value to cast to HttpUri
     * @param   string                              $name   optional  name of parameter to cast from
     * @return  \stubbles\peer\http\HttpUri
     * @throws  \InvalidArgumentException
     * @since   4.0.0
     */
    public static function castFrom($value, string $name = 'Uri'): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (is_string($value)) {
            return self::fromString($value);
        }

        throw new \InvalidArgumentException(
                $name . ' must be a string containing a HTTP URI or an instance of '
                . get_class() . ', but was '
                . (is_object($value) ? get_class($value) : gettype($value))
        );
    }

    /**
     * checks if uri is valid according to given rfc
     *
     * @param   string  $rfc
     * @return  bool
     * @throws  \stubbles\peer\MalformedUri
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
     * @param   string|\stubbles\peer\http\HttpUri  $httpUri
     * @param   callable                            $checkWith  optional  function to check dns record with
     * @return  bool
     * @since   7.1.0
     */
    public static function exists($httpUri, callable $checkWith = null): bool
    {
        if ($httpUri instanceof self) {
            return $httpUri->hasDnsRecord($checkWith);
        }

        if (empty($httpUri) || !is_string($httpUri)) {
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
     * @param   mixed  $httpUri
     * @return  bool
     * @since   7.1.0
     */
    public static function isValid($httpUri): bool
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
     *
     * @return  bool
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
     * @param   callable  $checkWith  optional  function to check dns record with
     * @return  bool
     */
    public function hasDnsRecord(callable $checkWith = null): bool
    {
        $checkdnsrr = null === $checkWith ? 'checkdnsrr': $checkWith;
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
     *
     * @return  string
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
     *
     * @return  bool
     */
    public function hasDefaultPort(): bool
    {
        if (!$this->parsedUri->hasPort()) {
            return true;
        }

        if ($this->isHttp() && $this->parsedUri->portEquals(Http::PORT)) {
            return true;
        }

        if ($this->isHttps() && $this->parsedUri->portEquals(Http::PORT_SSL)) {
            return true;
        }

        return false;
    }

    /**
     * returns port of the uri
     *
     * @param   int  $defaultPort  parameter is ignored for http uris
     * @return  int
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
     * @param   string  $path  new path
     * @return  \stubbles\peer\http\HttpUri
     * @since   5.5.0
     */
    public function withPath(string $path): Uri
    {
        return new ConstructedHttpUri(
                $this->parsedUri->transpose(['path' => $path])
        );
    }

    /**
     * checks whether current scheme is http
     *
     * @return  bool
     * @since   2.0.0
     */
    public function isHttp(): bool
    {
        return $this->parsedUri->schemeEquals(Http::SCHEME);
    }

    /**
     * checks whether current scheme is https
     *
     * @return  bool
     * @since   2.0.0
     */
    public function isHttps(): bool
    {
        return $this->parsedUri->schemeEquals(Http::SCHEME_SSL);
    }

    /**
     * transposes uri to http
     *
     * @param   int  $port  optional  new port to use, defaults to 80
     * @return  \stubbles\peer\http\HttpUri
     * @since   2.0.0
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
     * @param   int  $port  optional  new port to use, defaults to 443
     * @return  \stubbles\peer\http\HttpUri
     * @since   2.0.0
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
     *
     * @param   \stubbles\peer\HeaderList  $headers  list of headers to be used
     * @return  \stubbles\peer\http\HttpConnection
     */
    public function connect(HeaderList $headers = null): HttpConnection
    {
        return new HttpConnection($this, $headers);
    }

    /**
     * creates a socket to this uri
     *
     * @return  \stubbles\peer\Socket
     * @since   6.0.0
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
     * opens socket to this uri
     *
     * @param   int       $timeout  connection timeout
     * @param   callable  $openWith  optional  open port with this function
     * @return  \stubbles\peer\Stream
     * @since   2.0.0
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
