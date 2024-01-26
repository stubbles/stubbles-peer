<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer;

use ArrayIterator;
use InvalidArgumentException;
use Iterator;
use Traversable;

/**
 * Container for list of headers.
 *
 * @api
 * @implements \IteratorAggregate<string,scalar>
 */
class HeaderList implements \IteratorAggregate, \Countable
{
    /**
     * @param  array<string,scalar>  $headers
     * @since  2.0.0
     */
    public function __construct(private array $headers = [])  { }

    /**
     * creates headerlist from given string
     */
    public static function fromString(string $headers): self
    {
        return new self(self::parse($headers));
    }

    /**
     * parses given header string and returns a list of headers
     *
     * @return  array<string,scalar>
     */
    private static function parse(string $headers): array
    {
        $header  = [];
        $matches = [];
        preg_match_all(
            '=^(.[^: ]+): ([^\r\n]*)=m',
            $headers,
            $matches,
            PREG_SET_ORDER
        );
        foreach ($matches as $line) {
            $header[(string) $line[1]] = $line[2];
        }

        return $header;
    }

    /**
     * appends given headers
     *
     * If the header to append contain an already set header the existing header
     * value will be overwritten by the new one.
     *
     * @since  2.0.0
     */
    public function append(string|array|self $headers): self
    {
        if (is_string($headers)) {
            $append = self::parse($headers);
        } elseif (is_array($headers)) {
            $append = $headers;
        } else {
            $append = $headers->headers;
        }

        $this->headers = array_merge($this->headers, $append);
        return $this;
    }

    /**
     * creates header with value for key
     *
     * @param   scalar  $value  value of header
     * @throws  InvalidArgumentException
     */
    public function put(string $key, mixed $value): self
    {
        if (!is_scalar($value)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Argument 2 passed to %s must be an instance of a scalar value.',
                    __METHOD__
                )
            );
        }

        $this->headers[$key] = (string) $value;
        return $this;
    }

    /**
     * removes header with given key
     */
    public function remove(string $key): self
    {
        if (isset($this->headers[$key])) {
            unset($this->headers[$key]);
        }

        return $this;
    }

    /**
     * creates header for user agent
     */
    public function putUserAgent(string $userAgent): self
    {
        $this->put('User-Agent', $userAgent);
        return $this;
    }

    /**
     * creates header for referer
     */
    public function putReferer(string $referer): self
    {
        $this->put('Referer', $referer);
        return $this;
    }

    /**
     * creates header for cookie
     *
     * @param  array<string,string>  $cookieValues  cookie values
     */
    public function putCookie(array $cookieValues): self
    {
        $cookieValue = '';
        foreach ($cookieValues as $key => $value) {
            $cookieValue .= $key . '=' . urlencode($value) . ';';
        }

        $this->put('Cookie', $cookieValue);
        return $this;
    }

    /**
     * creates header for authorization
     */
    public function putAuthorization(string $user, string $password): self
    {
        $this->put('Authorization', 'BASIC ' . base64_encode($user . ':' . $password));
        return $this;
    }

    /**
     * adds a date header
     *
     * @param  int  $timestamp  timestamp to use as date, defaults to current timestamp
     */
    public function putDate(?int $timestamp = null): self
    {
        if (null === $timestamp) {
            $date = gmdate('D, d M Y H:i:s');
        } else {
            $date = gmdate('D, d M Y H:i:s', $timestamp);
        }

        $this->put('Date', $date . ' GMT');
        return $this;
    }

    /**
     * creates X-Binford header
     */
    public function enablePower(): self
    {
        $this->put('X-Binford', 'More power!');
        return $this;
    }

    /**
     * removes all headers
     */
    public function clear(): self
    {
        $this->headers = [];
        return $this;
    }

    /**
     * returns value of header with given key
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->headers[$key] ?? $default;
    }

    /**
     * returns true if an header with given key exists
     */
    public function containsKey(string $key): bool
    {
        return isset($this->headers[$key]);
    }

    /**
     * returns an iterator object
     *
     * @return  Iterator<string,scalar>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->headers);
    }

    /**
     * returns amount of headers
     *
     * @since  7.0.0
     */
    public function count(): int
    {
        return count($this->headers);
    }

    /**
     * returns a string representation of the class
     *
     * @XmlIgnore
     */
    public function __toString(): string
    {
        $result = [];
        foreach ($this->headers as $name => $value) {
          $result[] = $name . ': ' . $value;
        }

        return join("\r\n", $result);
    }
}
