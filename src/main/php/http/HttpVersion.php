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
namespace stubbles\peer\http;
/**
 * Represents a HTTP version.
 *
 * @since  4.0.0
 * @link   http://tools.ietf.org/html/rfc7230#section-2.6
 */
class HttpVersion
{
    /**
     * HTTP version: HTTP/1.0
     */
    const HTTP_1_0               = 'HTTP/1.0';
    /**
     * HTTP version: HTTP/1.1
     */
    const HTTP_1_1               = 'HTTP/1.1';
    /**
     * major http version
     *
     * @type  int
     */
    private $major;
    /**
     * minor http version
     *
     * @type  int
     */
    private $minor;

    /**
     * parses http version from given string
     *
     * @param   string  $httpVersion  a http version string like "HTTP/1.1"
     * @return  HttpVersion
     * @throws  \InvalidArgumentException  in case string can not be parsed successfully
     */
    public static function fromString($httpVersion): self
    {
        if (empty($httpVersion)) {
            throw new \InvalidArgumentException('Given HTTP version is empty');
        }

        $major = null;
        $minor = null;
        if (2 != sscanf($httpVersion, 'HTTP/%d.%d', $major, $minor)) {
            throw new \InvalidArgumentException(
                    'Given HTTP version "' . $httpVersion . '" can not be parsed'
            );
        }

        return new self($major, $minor);
    }

    /**
     * tries to case given $httpVersion value to an instance of HttpVersion
     *
     * @param   string|\stubbles\peer\http\HttpVersion  $httpVersion  value to cast from
     * @return  \stubbles\peer\http\HttpVersion
     * @throws  \InvalidArgumentException  in case neither $httpVersion nor $default represent a valid HTTP version
     */
    public static function castFrom($httpVersion): self
    {
        if (empty($httpVersion)) {
            throw new \InvalidArgumentException('Given HTTP version is empty');
        }

        if ($httpVersion instanceof self) {
            return $httpVersion;
        }

        return self::fromString($httpVersion);
    }

    /**
     * constructor
     *
     * In case the given major or minor version can not be casted to a valid
     * integer an InvalidArgumentException is thrown.
     *
     * @param  int|string  $major
     * @param  int|string  $minor
     * @throws  \InvalidArgumentException
     */
    public function __construct($major, $minor)
    {
        if (is_string($major) && !ctype_digit($major)) {
            throw new \InvalidArgumentException(
                    'Given major version "' . $major . '" is not an integer.'
            );
        }

        if (0 > $major) {
            throw new \InvalidArgumentException(
                    'Major version can not be negative.'
            );
        }

        if (is_string($minor) && !ctype_digit($minor)) {
            throw new \InvalidArgumentException(
                    'Given minor version "' . $minor . '" is not an integer.'
            );
        }

        if (0 > $minor) {
            throw new \InvalidArgumentException(
                    'Minor version can not be negative.'
            );
        }

        $this->major = (int) $major;
        $this->minor = (int) $minor;
    }

    /**
     * returns major version number
     *
     * @return  int
     */
    public function major(): int
    {
        return $this->major;
    }

    /**
     * returns minor version number
     *
     * @return  int
     */
    public function minor(): int
    {
        return $this->minor;
    }

    /**
     * checks if given http version is equal to this http version
     *
     * @param   string|\stubbles\peer\http\HttpVersion  $httpVersion
     * @return  bool
     */
    public function equals($httpVersion): bool
    {
        if (empty($httpVersion)) {
            return false;
        }

        try {
            $other = self::castFrom($httpVersion);
        } catch (\InvalidArgumentException $iae) {
            return false;
        }

        return $this->major() === $other->major() && $this->minor() === $other->minor();
    }

    /**
     * returns string representation
     *
     * @return  string
     */
    public function __toString(): string
    {
        return 'HTTP/' . $this->major . '.' . $this->minor;
    }
}
