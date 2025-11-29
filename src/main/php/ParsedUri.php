<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer;

use Closure;
use InvalidArgumentException;

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
    private const array LOCALHOSTNAMES = [
        'localhost' => 'localhost',
        '127.0.0.1' => '127.0.0.1',
        '[::1]'     => '[::1]'
    ];
    private string $scheme;
    private ?string $host = null;
    private ?int $port = null;
    private string $path;
    private ?string $user = null;
    private ?string $pass = null;
    private QueryString $queryString;
    private ?string $fragment = null;

    /**
     * constructor
     *
     * Passing a query string will omit any query string already present in $uri.
     *
     * @param   string|array<string,int|string>  $uri  uri to parse
     * @throws  MalformedUri
     */
    public function __construct(string|array $uri, ?QueryString $queryString = null)
    {
        $parsedUri = !is_array($uri) ? parse_url($uri): $uri;
        if (!is_array($parsedUri)) {
            throw new MalformedUri(
                sprintf(
                    'Given URI %s is not a valid URI',
                    (is_string($uri) ? $uri : '')
                )
            );
        }

        if (!isset($parsedUri['scheme'])) {
            throw new MalformedUri(
                sprintf(
                    'Given URI %s is missing a scheme.',
                    (is_string($uri) ? $uri : '')
                )
            );
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
            } catch (InvalidArgumentException $iae) {
                throw new MalformedUri($iae->getMessage(), $iae);
            }
        }

        if (isset($parsedUri['fragment'])) {
            $this->fragment = (string) $parsedUri['fragment'];
        }

        $this->fixPass($parsedUri, $uri);
    }

    /**
     * Bugfix for a PHP issue: ftp://user:@auxiliary.kl-s.com/
     * will lead to an unset $parsedUri['pass'] which is wrong
     * due to RFC1738 3.1, it has to be an empty string
     */
    private function fixPass(array $parsedUri, string|array $originalUri)
    {
        if (isset($parsedUri['user'])) {
            $this->user = (string) $parsedUri['user'];
            if (!isset($parsedUri['pass']) && $this->asString() !== $originalUri) {
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
     * @param  array<string,int|string|null>  $changedUri
     */
    public function transpose(array $changedUri): self
    {
        return new self(
            array_filter(
                array_merge(
                    [
                        'scheme'   => $this->scheme,
                        'host'     => $this->host,
                        'port'     => $this->port,
                        'path'     => $this->path,
                        'user'     => $this->user,
                        'pass'     => $this->pass,
                        'fragment' => $this->fragment
                    ],
                    $changedUri
                ),
                fn(mixed $val): bool => null !== $val
            ),
            $this->queryString
        );
    }

    /**
     * returns original uri
     */
    public function asString(): string
    {
        return $this->createString(fn(ParsedUri $uri): string => (string) $uri->port());
    }

    /**
     * returns original uri
     */
    public function asStringWithoutPort(): string
    {
        return $this->createString(fn(ParsedUri $uri): string => '');
    }

    /**
     * creates string representation of uri
     */
    protected function createString(Closure $portCreator): string
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
     */
    public function hasScheme(): bool
    {
        return null !== $this->scheme;
    }

    /**
     * checks if uri scheme equals given scheme
     *
     * @since   4.0.0
     */
    public function schemeEquals(?string $scheme = null): bool
    {
        return $scheme === $this->scheme();
    }

    /**
     * returns the scheme of the uri
     */
    public function scheme(): string
    {
        return $this->scheme;
    }

    /**
     * checks whether user is set
     */
    public function hasUser(): bool
    {
        return null !== $this->user;
    }

    /**
     * returns the user of the uri
     */
    public function user(?string $defaultUser = null): ?string
    {
        return $this->user ?? $defaultUser;
    }

    /**
     * checks whether password is set
     *
     * @deprecated  since 8.0.0, passing a password via URI is inherintly insecure
     */
    public function hasPassword(): bool
    {
        return null !== $this->pass;
    }

    /**
     * returns the password of the uri
     *
     * @deprecated  since 8.0.0, passing a password via URI is inherintly insecure
     */
    public function password(): ?string
    {
        return $this->pass;
    }

    /**
     * checks whether host is set
     */
    public function hasHostname(): bool
    {
        return null !== $this->host;
    }

    /**
     * checks if host is local
     */
    public function isLocalHost(): bool
    {
        return isset(self::LOCALHOSTNAMES[$this->host]);
    }

    /**
     * returns hostname of the uri
     */
    public function hostname(): ?string
    {
        return $this->host;
    }

    /**
     * checks whether port is set
     */
    public function hasPort(): bool
    {
        return null !== $this->port;
    }

    /**
     * checks if given port equals the uri's port
     *
     * @since   4.0.0
     */
    public function portEquals(?int $port = null): bool
    {
        return $port === $this->port();
    }

    /**
     * returns port of the uri
     */
    public function port(): ?int
    {
        return $this->port;
    }

    /**
     * returns path of the uri
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * returns the query string
     */
    public function queryString(): QueryString
    {
        return $this->queryString;
    }

    /**
     * checks whether fragment is set
     */
    public function hasFragment(): bool
    {
        return null !== $this->fragment;
    }

    /**
     * returns port of the uri
     */
    public function fragment(): ?string
    {
        return $this->fragment;
    }

    /**
     * returns a string representation of the class
     *
     * @XmlIgnore
     */
    public function __toString(): string
    {
        return $this->asString();
    }
}
