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
 * Class for URIs and methods on URIs.
 *
 * @api
 */
abstract class Uri
{
    private const ALLOWED_URI_SYNTAX = '!^([a-z][a-z0-9\+]*)://([^@]+@)?([^/?#]*)(/([^#?]*))?(.*)$!';
    /**
     * internal representation after parse_url()
     */
    protected ParsedUri $parsedUri;

    /**
     * parses an uri out of a string
     *
     * @throws  MalformedUri
     */
    public static function fromString(string $uriString): self
    {
        if (strlen($uriString) === 0) {
            throw new MalformedUri('Empty string is not a valid URI');
        }

        $uri = new ConstructedUri(new ParsedUri($uriString));
        if ($uri->isSyntacticallyValid()) {
            return $uri;
        }

        throw new MalformedUri('The URI ' . $uriString . ' is not a valid URI.');
    }

    /**
     * Checks whether URI is a syntactically correct URI.
     *
     * @return  bool
     */
    protected function isSyntacticallyValid(): bool
    {
        if (preg_match(self::ALLOWED_URI_SYNTAX, $this->parsedUri->asString()) == 0) {
            return false;
        }

        if (null != $user = $this->parsedUri->user()) {
            if (
                preg_match('~([@:/])~', $user) != 0
                || (
                    !empty($this->parsedUri->password())
                    && preg_match('~([@:/])~', $this->parsedUri->password()) != 0
                )
            ) {
                return false;
            }
        }

        $hostname = $this->parsedUri->hostname();
        if (null === $hostname || strlen($hostname) === 0) {
            return true;
        }

        if (
            null !== $hostname
            && preg_match('!^([a-zA-Z0-9\.-]+|\[[^\]]+\])(:([0-9]+))?$!', $hostname) != 0
        ) {
            return true;
        }

        return false;
    }

    /**
     * checks whether host of uri is listed in dns
     *
     * @param  callable  $checkWith  function to check dns record with
     */
    public function hasDnsRecord(?callable $checkWith = null): bool
    {
        if (!$this->parsedUri->hasHostname()) {
            return false;
        }

        $checkdnsrr = null === $checkWith ? 'checkdnsrr': $checkWith;
        if (
            $this->parsedUri->isLocalHost()
            || $checkdnsrr($this->parsedUri->hostname(), 'ANY')
            || $checkdnsrr($this->parsedUri->hostname(), 'MX')
        ) {
            return true;
        }

        return false;
    }

    /**
     * returns the uri as string as originally given
     */
    public function asString(): string
    {
        return $this->parsedUri->asString();
    }


    /**
     * returns a string representation of the uri
     *
     * @XmlIgnore
     */
    public function __toString(): string
    {
        return $this->asString();
    }

    /**
     * Returns uri as string but without port even if originally given.
     */
    public function asStringWithoutPort(): string
    {
        return $this->parsedUri->asStringWithoutPort();
    }

    /**
     * Returns uri as string containing the port if it is not the default port.
     */
    public function asStringWithNonDefaultPort(): string
    {
        if ($this->parsedUri->hasPort() && !$this->hasDefaultPort()) {
            return $this->asString();
        }

        return $this->asStringWithoutPort();
    }

    /**
     * returns the scheme of the uri
     */
    public function scheme(): string
    {
        return $this->parsedUri->scheme();
    }

    /**
     * returns the user
     */
    public function user(?string $defaultUser = null): ?string
    {
        return $this->parsedUri->user($defaultUser);
    }

    /**
     * returns the password
     *
     * @deprecated  since 8.0.0, passing a password via URI is inherintly insecure
     */
    public function password(?string $defaultPassword = null): ?string
    {
        if (!$this->parsedUri->hasUser()) {
            return null;
        }

        if ($this->parsedUri->hasPassword()) {
            return $this->parsedUri->password();
        }

        return $defaultPassword;
    }

    /**
     * returns hostname of the uri
     */
    public function hostname(): ?string
    {
        return $this->parsedUri->hostname();
    }

    /**
     * checks whether the uri uses a default port or not
     *
     * This generic implementation doesn't know default ports for protocols. It
     * simply assumes that when no port is specified in the URI that the default
     * port should be used. However, when the string from which the URI was
     * constructed explicitly contains the default port for the protocol the
     * return value of this method will be wrong, as it would return `false`
     * when in fact the default port was specified.
     */
    public function hasDefaultPort(): bool
    {
        return !$this->parsedUri->hasPort();
    }

    /**
     * returns port of the uri
     */
    public function port(?int $defaultPort = null): ?int
    {
        if ($this->parsedUri->hasPort()) {
            return $this->parsedUri->port();
        }

        return $defaultPort;
    }

    /**
     * returns a new uri instance with new path
     *
     * @since   5.5.0
     */
    public function withPath(string $path): self
    {
        return new ConstructedUri($this->parsedUri->transpose(['path' => $path]));
    }

    /**
     * returns path of the uri
     */
    public function path(): string
    {
        return $this->parsedUri->path();
    }

    /**
     * checks whether uri has a query
     */
    public function hasQueryString(): bool
    {
        return $this->parsedUri->queryString()->hasParams();
    }

    /**
     * returns query string
     *
     * @since  2.1.2
     */
    public function queryString(): string
    {
        return $this->parsedUri->queryString()->build();
    }

    /**
     * adds given map of params
     *
     * @param  array<string,scalar>  $params  map of parameters to add
     * @since  5.1.2
     */
    public function addParams(array $params): self
    {
        foreach ($params as $name => $value) {
            $this->addParam($name, $value);
        }

        return $this;
    }

    /**
     * add a parameter to the uri
     *
     * @param  string  $name   name of parameter
     * @param  mixed   $value  value of parameter
     */
    public function addParam(string $name, mixed $value): self
    {
        $this->parsedUri->queryString()->addParam($name, $value);
        return $this;
    }

    /**
     * remove a param from uri
     *
     * @since  1.1.2
     */
    public function removeParam(string $name): self
    {
        $this->parsedUri->queryString()->removeParam($name);
        return $this;
    }

    /**
     * checks whether a certain param is set
     *
     * @since  1.1.2
     */
    public function hasParam(string $name): bool
    {
        return $this->parsedUri->queryString()->containsParam($name);
    }

    /**
     * returns the value of a param
     */
    public function param(string $name, mixed $defaultValue = null): mixed
    {
        return $this->parsedUri->queryString()->param($name, $defaultValue);
    }

    /**
     * returns fragment of the uri
     */
    public function fragment(): ?string
    {
        return $this->parsedUri->fragment();
    }
}
