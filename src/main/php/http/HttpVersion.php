<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer\http;

use InvalidArgumentException;

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
    public const string HTTP_1_0 = 'HTTP/1.0';
    /**
     * HTTP version: HTTP/1.1
     */
    public const string HTTP_1_1 = 'HTTP/1.1';
    private int $major;
    private int $minor;

    /**
     * parses http version from given string
     *
     * @throws  InvalidArgumentException  in case string can not be parsed successfully
     */
    public static function fromString(string $httpVersion): self
    {
        if (empty($httpVersion)) {
            throw new InvalidArgumentException('Given HTTP version is empty');
        }

        $major = null;
        $minor = null;
        if (2 != sscanf($httpVersion, 'HTTP/%d.%d', $major, $minor)) {
            throw new InvalidArgumentException(
                sprintf('Given HTTP version "%s" can not be parsed', $httpVersion)
            );
        }

        return new self($major, $minor);
    }

    /**
     * tries to case given $httpVersion value to an instance of HttpVersion
     *
     * @throws  InvalidArgumentException  in case neither $httpVersion nor $default represent a valid HTTP version
     */
    public static function castFrom(string|HttpVersion $httpVersion): self
    {
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
     * @throws InvalidArgumentException
     */
    public function __construct(int|string $major, int|string $minor)
    {
        if (is_string($major) && !ctype_digit($major)) {
            throw new InvalidArgumentException(
                sprintf('Given major version "%s" is not an integer.', $major)
            );
        }

        if (0 > $major) {
            throw new InvalidArgumentException('Major version can not be negative.');
        }

        if (is_string($minor) && !ctype_digit($minor)) {
            throw new InvalidArgumentException(
                sprintf('Given minor version "%s" is not an integer.', $minor)
            );
        }

        if (0 > $minor) {
            throw new InvalidArgumentException('Minor version can not be negative.');
        }

        $this->major = (int) $major;
        $this->minor = (int) $minor;
    }

    public function major(): int
    {
        return $this->major;
    }

    public function minor(): int
    {
        return $this->minor;
    }

    /**
     * checks if given http version is equal to this http version
     */
    public function equals(string|self $httpVersion): bool
    {
        if (empty($httpVersion)) {
            return false;
        }

        try {
            $other = self::castFrom($httpVersion);
        } catch (InvalidArgumentException $iae) {
            return false;
        }

        return $this->major() === $other->major() && $this->minor() === $other->minor();
    }

    public function __toString(): string
    {
        return sprintf('HTTP/%d.%d', $this->major, $this->minor);
    }
}
