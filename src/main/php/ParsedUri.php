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
 * Represents a parses uri.
 *
 * @internal
 */
class ParsedUri
{
    /**
     * internal representation after parse_url()
     *
     * @type  array
     */
    private $uri         = [];
    /**
     * query string of uri
     *
     * @type  \stubbles\peer\QueryString
     */
    private $queryString;

    /**
     * constructor
     *
     * Passing a query string will omit any query string already present in $uri.
     *
     * @param   string|array                $uri          uri to parse
     * @param   \stubbles\peer\QueryString  $queryString  optional  parameters when not in uri
     * @throws  \stubbles\peer\MalformedUri
     */
    public function __construct($uri, QueryString $queryString = null)
    {
        $this->uri = !is_array($uri) ? parse_url($uri): $uri;
        if (!is_array($this->uri)) {
            throw new MalformedUri('The URI ' . $uri . ' is not a valid URI');
        }

        if (!isset($this->uri['scheme'])) {
            throw new MalformedUri('The URI ' . $uri . ' is missing a scheme.');
        }

        if (isset($this->uri['host'])) {
            $this->uri['host'] = strtolower($this->uri['host']);
        }

        if (!isset($this->uri['path'])) {
            $this->uri['path'] = '';
        }

        if (null !== $queryString) {
            $this->queryString = $queryString;
        } else {
            try {
                $this->queryString = new QueryString($this->uri['query'] ?? null);
            } catch (\InvalidArgumentException $iae) {
                throw new MalformedUri($iae->getMessage(), $iae);
            }
        }

        // bugfix for a PHP issue: ftp://user:@auxiliary.kl-s.com/
        // will lead to an unset $this->uri['pass'] which is wrong
        // due to RFC1738 3.1, it has to be an empty string
        if (isset($this->uri['user']) && !isset($this->uri['pass']) && $this->asString() !== $uri) {
            $this->uri['pass'] = '';
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
     * @param   array  $changedUri
     * @return  \stubbles\peer\ParsedUri
     */
    public function transpose(array $changedUri): self
    {
        return new self(array_merge($this->uri, $changedUri), $this->queryString);
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
     * @deprecated  since 8.0.0, a valid URI always has a scheme, will be removed with 9.0.0
     */
    public function hasScheme(): bool
    {
        return isset($this->uri['scheme']);
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
        return $this->uri['scheme'];
    }

    /**
     * checks whether user is set
     *
     * @return  bool
     */
    public function hasUser(): bool
    {
        return isset($this->uri['user']);
    }

    /**
     * returns the user of the uri
     *
     * @param   string  $defaultUser  user to return if no user is set
     * @return  string|null
     */
    public function user(string $defaultUser = null)
    {
        return $this->uri['user'] ?? $defaultUser;
    }

    /**
     * checks whether password is set
     *
     * @return  bool
     * @deprecated  since 8.0.0, passing a password via URI is inherintly insecure, will be removed with 9.0.0
     */
    public function hasPassword(): bool
    {
        return isset($this->uri['pass']);
    }

    /**
     * returns the password of the uri
     *
     * @return  string|null
     * @deprecated  since 8.0.0, passing a password via URI is inherintly insecure, will be removed with 9.0.0
     */
    public function password()
    {
        return $this->uri['pass'] ?? null;
    }

    /**
     * checks whether host is set
     *
     * @return  bool
     */
    public function hasHostname(): bool
    {
        return isset($this->uri['host']);
    }

    /**
     * checks if host is local
     *
     * @return  bool
     */
    public function isLocalHost(): bool
    {
        return in_array($this->uri['host'], ['localhost', '127.0.0.1', '[::1]']);
    }

    /**
     * returns hostname of the uri
     *
     * @return  string|null
     */
    public function hostname()
    {
        return $this->uri['host'] ?? null;
    }

    /**
     * checks whether port is set
     *
     * @return  bool
     */
    public function hasPort(): bool
    {
        return isset($this->uri['port']);
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
    public function port()
    {
        if (isset($this->uri['port'])) {
            return (int) $this->uri['port'];
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
        return isset($this->uri['path']);
    }

    /**
     * returns path of the uri
     *
     * @return  string
     */
    public function path(): string
    {
        return $this->uri['path'];
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
        return isset($this->uri['fragment']);
    }

    /**
     * returns port of the uri
     *
     * @return  string|null
     */
    public function fragment()
    {
        return $this->uri['fragment'] ?? null;
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
