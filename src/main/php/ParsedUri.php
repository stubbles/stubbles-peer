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
 * Represents a parses uri.
 *
 * @internal
 */
class ParsedUri
{
    /**
     * list of hostnames that are localhost
     *
     * @var  array<string,string>
     */
    const LOCALHOSTNAMES = [
            'localhost' => 'localhost',
            '127.0.0.1' => '127.0.0.1',
            '[::1]'     => '[::1]'
    ];
    /**
     * @var  string
     */
    private $scheme;
    /**
     * @var  string
     */
    private $host;
    /**
     * @var  int
     */
    private $port;
    /**
     * @var  string
     */
    private $path;
    /**
     * @var  string
     */
    private $user;
    /**
     * @var  string
     */
    private $pass;
    /**
     * query string of uri
     *
     * @var  \stubbles\peer\QueryString
     */
    private $queryString;
    /**
     * @var  string
     */
    private $fragment;

    /**
     * constructor
     *
     * Passing a query string will omit any query string already present in $uri.
     *
     * @param   string|array<string,int|string>  $uri          uri to parse
     * @param   \stubbles\peer\QueryString   $queryString  optional  parameters when not in uri
     * @throws  \stubbles\peer\MalformedUri
     */
    public function __construct($uri, QueryString $queryString = null)
    {
        $parsedUri = !\is_array($uri) ? \parse_url($uri): $uri;
        if (!\is_array($parsedUri)) {
            throw new MalformedUri('Given URI ' . (\is_string($uri) ? $uri : '') . ' is not a valid URI');
        }

        if (!isset($parsedUri['scheme'])) {
            throw new MalformedUri('Given URI ' . (\is_string($uri) ? $uri : '') . ' is missing a scheme.');
        }

        $this->scheme = (string) $parsedUri['scheme'];
        if (isset($parsedUri['host'])) {
            $this->host = strtolower((string) $parsedUri['host']);
        }

        if (isset($parsedUri['port'])) {
            $this->port = (int) $parsedUri['port'];
        }

        $this->path = (string) ($parsedUri['path'] ?? '');
        if (null !== $queryString) {
            $this->queryString = $queryString;
        } else {
            try {
                $this->queryString = new QueryString($parsedUri['query'] ?? null);
            } catch (\InvalidArgumentException $iae) {
                throw new MalformedUri($iae->getMessage(), $iae);
            }
        }

        if (isset($parsedUri['fragment'])) {
            $this->fragment = (string) $parsedUri['fragment'];
        }

        // bugfix for a PHP issue: ftp://user:@auxiliary.kl-s.com/
        // will lead to an unset $parsedUri['pass'] which is wrong
        // due to RFC1738 3.1, it has to be an empty string
        if (isset($parsedUri['user'])) {
            $this->user = (string) $parsedUri['user'];
            if (!isset($parsedUri['pass']) && $this->asString() !== $uri) {
                $this->pass = '';
            } elseif (isset($parsedUri['pass'])) {
                $this->pass = (string) $parsedUri['pass'];
            }
        }
    }

    /**
     * transposes the uri to another one
     *
     * This will create a new instance, leaving the existing instance unchanged.
     * The given array should contain the parts to change, where the key denotes
     * the part to change and the value the value to change to.
     *
     * The return value is a new instance with the named parts changed to the
     * new values.
     *
     * @param   array<string,int|string|null>  $changedUri
     * @return  \stubbles\peer\ParsedUri
     */
    public function transpose(array $changedUri): self
    {
        return new self(array_filter(array_merge([
                'scheme'   => $this->scheme,
                'host'     => $this->host,
                'port'     => $this->port,
                'path'     => $this->path,
                'user'     => $this->user,
                'pass'     => $this->pass,
                'fragment' => $this->fragment
            ], $changedUri), function($val) { return null !== $val; }),
            $this->queryString
        );
    }

    /**
     * returns original uri
     *
     * @return  string
     */
    public function asString(): string
    {
        return $this->createString(function(ParsedUri $uri) { return $uri->port();});
    }

    /**
     * returns original uri
     *
     * @return  string
     */
    public function asStringWithoutPort(): string
    {
        return $this->createString(function(ParsedUri $uri) { return null;});
    }

    /**
     * creates string representation of uri
     *
     * @param   \Closure  $portCreator
     * @return  string
     */
    protected function createString(\Closure $portCreator): string
    {
        $uri = $this->scheme() . '://';
        if ($this->hasUser()) {
            $user = $this->user();
            if ($this->hasPassword()) {
                $user .= ':' . $this->password();
            }

            $uri .= $user;
            if ($this->hasHostname()) {
                $uri .= '@';
            }
        }

        if ($this->hasHostname()) {
            $uri .= $this->hostname();
            $port = (string) $portCreator($this);
            if (strlen($port) > 0) {
                $uri .= ':' . $port;
            }
        }

        $uri .= $this->path();
        if ($this->queryString->hasParams()) {
            $uri .= '?' . $this->queryString->build();
        }

        if ($this->hasFragment()) {
            $uri .= '#' . $this->fragment();
        }

        return $uri;
    }

    /**
     * checks whether scheme is set
     *
     * @return  bool
     */
    public function hasScheme(): bool
    {
        return null !== $this->scheme;
    }

    /**
     * checks if uri scheme equals given scheme
     *
     * @param   string  $scheme
     * @return  bool
     * @since   4.0.0
     */
    public function schemeEquals(string $scheme = null): bool
    {
        return $scheme === $this->scheme();
    }

    /**
     * returns the scheme of the uri
     *
     * @return  string
     */
    public function scheme(): string
    {
        return $this->scheme;
    }

    /**
     * checks whether user is set
     *
     * @return  bool
     */
    public function hasUser(): bool
    {
        return null !== $this->user;
    }

    /**
     * returns the user of the uri
     *
     * @param   string  $defaultUser  user to return if no user is set
     * @return  string|null
     */
    public function user(string $defaultUser = null): ?string
    {
        return null !== $this->user ? $this->user : $defaultUser;
    }

    /**
     * checks whether password is set
     *
     * @return  bool
     * @deprecated  since 8.0.0, passing a password via URI is inherintly insecure
     */
    public function hasPassword(): bool
    {
        return null !== $this->pass;
    }

    /**
     * returns the password of the uri
     *
     * @return  string|null
     * @deprecated  since 8.0.0, passing a password via URI is inherintly insecure
     */
    public function password(): ?string
    {
        return null !== $this->pass ? $this->pass : null;
    }

    /**
     * checks whether host is set
     *
     * @return  bool
     */
    public function hasHostname(): bool
    {
        return null !== $this->host;
    }

    /**
     * checks if host is local
     *
     * @return  bool
     */
    public function isLocalHost(): bool
    {
        return isset(self::LOCALHOSTNAMES[$this->host]);
    }

    /**
     * returns hostname of the uri
     *
     * @return  string|null
     */
    public function hostname(): ?string
    {
        return $this->host;
    }

    /**
     * checks whether port is set
     *
     * @return  bool
     */
    public function hasPort(): bool
    {
        return null !== $this->port;
    }

    /**
     * checks if given port equals the uri's port
     *
     * @param   int  $port
     * @return  bool
     * @since   4.0.0
     */
    public function portEquals(int $port = null): bool
    {
        return $port === $this->port();
    }

    /**
     * returns port of the uri
     *
     * @return  int|null
     */
    public function port(): ?int
    {
        if (null !== $this->port) {
            return $this->port;
        }

        return null;
    }

    /**
     * checks if path is set
     *
     * @return  bool
     * @since   4.0.0
     * @deprecated  since 8.0.0, a valid URI always has a path, will be removed with 9.0.0
     */
    public function hasPath(): bool
    {
        return null !== $this->path;
    }

    /**
     * returns path of the uri
     *
     * @return  string
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * returns the query string
     *
     * @return  \stubbles\peer\QueryString
     */
    public function queryString(): QueryString
    {
        return $this->queryString;
    }

    /**
     * checks whether fragment is set
     *
     * @return  bool
     */
    public function hasFragment(): bool
    {
        return null !== $this->fragment;
    }

    /**
     * returns port of the uri
     *
     * @return  string|null
     */
    public function fragment(): ?string
    {
        return $this->fragment;
    }

    /**
     * returns a string representation of the class
     *
     * @XmlIgnore
     * @return  string
     */
    public function __toString(): string
    {
        return $this->asString();
    }
}
