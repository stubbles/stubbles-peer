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
 * Represents an ip address and possible operations on an ip address.
 *
 * @since  4.0.0
 */
class IpAddress
{
    /**
     * type IPv4
     */
    const V4 = 'IPv4';
    /**
     * type IPv6
     */
    const V6 = 'IPv6';
    /**
     * actual ip address
     *
     * @type  string
     */
    private $ip;
    /**
     * stores whether it is a IPv4 or IPv6 address
     *
     * @type  string
     */
    private $type;

    /**
     * checks if given value is either a IPv4 or IPv6 address
     *
     * @param   string  $value
     * @return  bool
     * @since   7.1.0
     */
    public static function isValid($value): bool
    {
        return self::isValidV4($value) || self::isValidV6($value);
    }

    /**
     * checks if given value is a syntactical correct IPv4 address
     *
     * @param   string  $value
     * @return  bool
     * @since   7.0.0
     */
    public static function isValidV4($value): bool
    {
        return false !== filter_var(
                $value,
                FILTER_VALIDATE_IP,
                ['flags' => FILTER_FLAG_IPV4]
        );
    }

    /**
     * checks if given value is a syntactical correct IPv6 address
     *
     * @param   string  $value
     * @return  bool
     * @since   7.0.0
     */
    public static function isValidV6($value): bool
    {
        return false !== filter_var(
                $value,
                FILTER_VALIDATE_IP,
                ['flags' => FILTER_FLAG_IPV6]
        );
    }

    /**
     * constructor
     *
     * Integer values are considered to be representations of an IP address as
     * long.
     *
     * The given value will be checked with \stubbles\predicate\IsIpAddress. If
     * the predicate returns false an IllegalArgumentException will be thrown.
     *
     * @param   int|string  $ip
     * @throws  \InvalidArgumentException
     */
    public function __construct($ip)
    {
        if (ctype_digit($ip)) {
            // long2ip() in PHP 7.0 expects string, and int since 7.1
            if (PHP_MAJOR_VERSION === 7 && PHP_MINOR_VERSION === 0) {
                settype($ip, 'string');
            } else {
                settype($ip, 'int');
            }

            $this->ip = long2ip($ip);
        } else {
            $this->ip = $ip;
        }

        if (self::isValidV4($this->ip)) {
            $this->type = self::V4;
        } elseif (self::isValidV6($this->ip)) {
            $this->type = self::V6;
        } else {
            throw new \InvalidArgumentException(
                    'Given ip address ' . $this->ip
                    . ' does not denote a valid IP address'
            );
        }
    }

    /**
     * casts given value to ip address
     *
     * @param   int|string|\stubbles\peer\IpAddress $ip
     * @return  \stubbles\peer\IpAddress
     */
    public static function castFrom($ip): self
    {
        if ($ip instanceof self) {
            return $ip;
        }

        return new self($ip);
    }

    /**
     * returns type of IP address: either IPv4 or IPv6
     *
     * @return  string
     * @since   7.0.0
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * checks whether this is an IPv4 address
     *
     * @return  bool
     * @since   7.0.0
     */
    public function isV4(): bool
    {
        return self::V4 === $this->type;
    }

    /**
     * checks whether this is an IPv6 address
     *
     * @return  bool
     * @since   7.0.0
     */
    public function isV6(): bool
    {
        return self::V6 === $this->type;
    }

    /**
     * checks if IP address is in given CIDR range
     *
     * A cidr range is commonly notated as 10.16/13. From this, $cidrIpShort
     * would be 10.16 and $cidrMask would be 13 or 47.
     *
     * Please note that this method currently supports IPv4 only.
     *
     * @param   string      $cidrIpShort
     * @param   int|string  $cidrMask
     * @return  bool
     * @throws  \InvalidArgumentException  when $cidrMask is not a valid integer
     * @see     http://en.wikipedia.org/wiki/Classless_Inter-Domain_Routing#CIDR_notation
     */
    public function isInCidrRange(string $cidrIpShort, $cidrMask): bool
    {
        if (is_string($cidrMask) && !ctype_digit($cidrMask)) {
            throw new \InvalidArgumentException(
                    'cidrMask must be of type int or a string losslessly'
                    . ' convertible to int.'
            );
        }

        list($lower, $upper) = $this->calculateIpRange(
                $this->completeCidrIp($cidrIpShort),
                (int) $cidrMask
        );
        return $this->asLong() >= $lower &&  $this->asLong() <= $upper;
    }

    /**
     * returns lower and upper ip for IP range as long
     *
     * @param   int  $cidrIpLong
     * @param   int  $cidrMask
     * @return  int[]
     */
    private function calculateIpRange(int $cidrIpLong, int $cidrMask): array
    {
        $netWork = $cidrIpLong & $this->netMask($cidrMask);
        $lower   = $netWork + 1; // ignore network ID (eg: 192.168.1.0)
        $upper   = ($netWork | $this->inverseNetMask($cidrMask)) - 1 ; //  ignore broadcast IP (eg: 192.168.1.255)
        return array($lower, $upper);
    }

    /**
     * turns short version of a CIDR IP address into its complete version
     *
     * @param   string  $cidrIpShort
     * @return  int
     */
    private function completeCidrIp(string $cidrIpShort): int
    {
        return ip2long($cidrIpShort . str_repeat('.0', 3 - substr_count($cidrIpShort, '.')));
    }

    /**
     * calculates net mask from cidr mask
     *
     * @param   int  $cidrMask
     * @return  int
     */
    private function netMask(int $cidrMask): int
    {
        return bindec(str_repeat('1', $cidrMask) . str_repeat('0', 32 - $cidrMask));
    }

    /**
     * calculates inverse net mask from cidr mask
     *
     * @param   int  $cidrMask
     * @return  int
     */
    private function inverseNetMask(int $cidrMask): int
    {
        return bindec(str_repeat('0', $cidrMask) . str_repeat('1',  32 - $cidrMask));
    }

    /**
     * returns ip address as long
     *
     * @return  int
     */
    public function asLong(): int
    {
        return ip2long($this->ip);
    }

    /**
     * returns string representation
     *
     * @return  string
     */
    public function __toString(): string
    {
        return $this->ip;
    }

    /**
     * opens socket to this ip address
     *
     * @param   int  $port     port to connect to
     * @return  \stubbles\peer\Socket
     * @since   6.0
     */
    public function createSocket(int $port): Socket
    {
        return new Socket($this->ip, $port, null);
    }

    /**
     * opens socket to this ip address
     *
     * @param   int       $port      port to connect to
     * @param   int       $timeout   optional  connection timeout
     * @param   callable  $openWith  optional  open port with this function
     * @return  \stubbles\peer\Stream
     */
    public function openSocket(int $port, int $timeout = 5, callable $openWith = null): Stream
    {
        $socket = new Socket($this->ip, $port, null);
        if (null !== $openWith) {
            $socket->openWith($openWith);
        }

        return $socket->connect()->setTimeout($timeout);
    }

    /**
     * opens secure socket using ssl to this ip address
     *
     * @param   int  $port     port to connect to
     * @return  \stubbles\peer\Socket
     * @since   6.0
     */
    public function createSecureSocket(int $port): Socket
    {
        return new Socket($this->ip, $port, 'ssl://');
    }

    /**
     * opens secure socket using ssl to this ip address
     *
     * @param   int       $port      port to connect to
     * @param   int       $timeout   optional  connection timeout
     * @param   callable  $openWith  optional  open port with this function
     * @return  \stubbles\peer\Stream
     */
    public function openSecureSocket(int $port, int $timeout = 5, callable $openWith = null): Stream
    {
        $socket = new Socket($this->ip, $port, 'ssl://');
        if (null !== $openWith) {
            $socket->openWith($openWith);
        }

        return $socket->connect()->setTimeout($timeout);
    }
}
