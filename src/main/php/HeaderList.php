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
 * Container for list of headers.
 *
 * @api
 */
class HeaderList implements \IteratorAggregate, \Countable
{
    /**
     * list of headers
     *
     * @type  array
     */
    private $headers = [];

    /**
     * constructor
     *
     * @param  array  $headers
     * @since  2.0.0
     */
    public function __construct(array $headers = [])
    {
        $this->headers = $headers;
    }

    /**
     * creates headerlist from given string
     *
     * @param   string  $headers  string to parse for headers
     * @return  \stubbles\peer\HeaderList
     */
    public static function fromString(string $headers): self
    {
        return new self(self::parse($headers));
    }

    /**
     * parses given header string and returns a list of headers
     *
     * @param   string  $headers
     * @return  array
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
            $header[$line[1]] = $line[2];
        }

        return $header;
    }

    /**
     * appends given headers
     *
     * If the header to append contain an already set header the existing header
     * value will be overwritten by the new one.
     *
     * @param   string|array|\stubbles\peer\HeaderList  $headers
     * @return  \stubbles\peer\HeaderList
     * @throws  \InvalidArgumentException
     * @since   2.0.0
     */
    public function append($headers): self
    {
        if (is_string($headers)) {
            $append = self::parse($headers);
        } elseif (is_array($headers)) {
            $append = $headers;
        } elseif ($headers instanceof self) {
            $append = $headers->headers;
        } else {
            throw new \InvalidArgumentException(
                    'Given headers must be a string, a list of headers'
                    . ' or another instance of ' . __CLASS__
            );
        }

        $this->headers = array_merge($this->headers, $append);
        return $this;
    }

    /**
     * creates header with value for key
     *
     * @param   string  $key    name of header
     * @param   scalar  $value  value of header
     * @return  \stubbles\peer\HeaderList
     * @throws  \InvalidArgumentException
     */
    public function put(string $key, $value): self
    {
        if (!is_scalar($value)) {
            throw new \InvalidArgumentException(
                    'Argument 2 passed to ' . __METHOD__
                    . ' must be an instance of a scalar value.'
            );
        }

        $this->headers[$key] = (string) $value;
        return $this;
    }

    /**
     * removes header with given key
     *
     * @param   string  $key  name of header
     * @return  \stubbles\peer\HeaderList
     */
    public function remove(string $key): self
    {
        if (isset($this->headers[$key]) == true) {
            unset($this->headers[$key]);
        }

        return $this;
    }

    /**
     * creates header for user agent
     *
     * @param   string  $userAgent  name of user agent
     * @return  \stubbles\peer\HeaderList
     */
    public function putUserAgent(string $userAgent): self
    {
        $this->put('User-Agent', $userAgent);
        return $this;
    }

    /**
     * creates header for referer
     *
     * @param   string  $referer  referer uri
     * @return  \stubbles\peer\HeaderList
     */
    public function putReferer(string $referer): self
    {
        $this->put('Referer', $referer);
        return $this;
    }

    /**
     * creates header for cookie
     *
     * @param   array  $cookieValues  cookie values
     * @return  \stubbles\peer\HeaderList
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
     *
     * @param   string  $user      login name
     * @param   string  $password  login password
     * @return  \stubbles\peer\HeaderList
     */
    public function putAuthorization(string $user, string $password): self
    {
        $this->put('Authorization', 'BASIC ' . base64_encode($user . ':' . $password));
        return $this;
    }

    /**
     * adds a date header
     *
     * @param   int  $timestamp  timestamp to use as date, defaults to current timestamp
     * @return  \stubbles\peer\HeaderList
     */
    public function putDate(int $timestamp = null): self
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
     *
     * @return  \stubbles\peer\HeaderList
     */
    public function enablePower(): self
    {
        $this->put('X-Binford', 'More power!');
        return $this;
    }

    /**
     * removes all headers
     *
     * @return  \stubbles\peer\HeaderList
     */
    public function clear(): self
    {
        $this->headers = [];
        return $this;
    }

    /**
     * returns value of header with given key
     *
     * @param   string  $key      name of header
     * @param   mixed   $default  value to return if given header not set
     * @return  mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->headers[$key] ?? $default;
    }

    /**
     * returns true if an header with given key exists
     *
     * @param   string  $key  name of header
     * @return  bool
     */
    public function containsKey(string $key): bool
    {
        return isset($this->headers[$key]);
    }

    /**
     * returns an iterator object
     *
     * @return  \Traversable
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayObject($this->headers);
    }

    /**
     * returns amount of headers
     *
     * @return  int
     * @since   7.0.0
     */
    public function count(): int
    {
        return count($this->headers);
    }

    /**
     * returns a string representation of the class
     *
     * @XmlIgnore
     * @return  string
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
