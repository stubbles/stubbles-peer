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
use bovigo\callmap\NewCallable;

use function bovigo\assert\{
    assert,
    assertFalse,
    assertTrue,
    expect,
    predicate\equals,
    predicate\isInstanceOf,
    predicate\isSameAs
};
/**
 * Test for stubbles\peer\IpAddress.
 *
 * @group  peer
 * @since  4.0.0
 */
class IpAddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @since  7.1.0
     */
    public function stringIsNoIpAndEvaluatesToFalse()
    {
        assertFalse(IpAddress::isValid('foo'));
    }

    /**
     * @test
     * @since  7.1.0
     */
    public function nullIsNoIpAndEvaluatesToFalse()
    {
        assertFalse(IpAddress::isValid(null));
    }

    /**
     * @test
     * @since  7.1.0
     */
    public function emptyStringIsNoIpAndEvaluatesToFalse()
    {
        assertFalse(IpAddress::isValid(''));
    }

    /**
     * @test
     * @since  7.1.0
     */
    public function isValidForIpV4()
    {
        assertTrue(IpAddress::isValid('127.0.0.1'));
    }

    /**
     * @test
     * @since  7.1.0
     */
    public function isValidForIpV6()
    {
        assertTrue(IpAddress::isValid('febc:a574:382b:23c1:aa49:4592:4efe:9982'));
    }

    /**
     * @test
     */
    public function stringIsNoIpV4AndEvaluatesToFalse()
    {
        assertFalse(IpAddress::isValidV4('foo'));
    }

    /**
     * @test
     */
    public function nullIsNoIpV4AndEvaluatesToFalse()
    {
        assertFalse(IpAddress::isValidV4(null));
    }

    /**
     * @test
     */
    public function emptyStringIsNoIpV4AndEvaluatesToFalse()
    {
        assertFalse(IpAddress::isValidV4(''));
    }

    /**
     * @test
     */
    public function booleansAreNoIpV4AndResultInFalse()
    {
        assertFalse(IpAddress::isValidV4(true));
        assertFalse(IpAddress::isValidV4(false));
    }

    /**
     * @test
     */
    public function singleNumbersAreNoIpV4AndResultInFalse()
    {
        assertFalse(IpAddress::isValidV4(4));
    }

    /**
     * @test
     */
    public function invalidIpV4WithMissingPartEvaluatesToFalse()
    {
        assertFalse(IpAddress::isValidV4('255.55.55'));
    }

    /**
     * @test
     */
    public function invalidIpV4WithSuperflousPartEvaluatesToFalse()
    {
        assertFalse(IpAddress::isValidV4('111.222.333.444.555'));
    }

    /**
     * @test
     */
    public function invalidIpV4WithMissingNumberEvaluatesToFalse()
    {
        assertFalse(IpAddress::isValidV4('1..3.4'));
    }

    /**
     * @test
     */
    public function invalidIpV4WithNumberOutOfRangeEvaluatesToFalse()
    {
        assertFalse(IpAddress::isValidV4('1.256.3.4'));
    }

    /**
     * @test
     */
    public function greatestIpV4EvaluatesToTrue()
    {
        assertTrue(IpAddress::isValidV4('255.255.255.255'));
    }

    /**
     * @test
     */
    public function lowestIpV4EvaluatesToTrue()
    {
        assertTrue(IpAddress::isValidV4('0.0.0.0'));
    }

    /**
     * @test
     */
    public function correctIpV4EvaluatesToTrue()
    {
        assertTrue(IpAddress::isValidV4('1.2.3.4'));
    }

    /**
     * @test
     */
    public function stringIsNoIpV6AndEvaluatesToFalse()
    {
        assertFalse(IpAddress::isValidV6('foo'));
    }

    /**
     * @test
     */
    public function nullIsNoIpV6AndEvaluatesToFalse()
    {
        assertFalse(IpAddress::isValidV6(null));
    }

    /**
     * @test
     */
    public function emptyStringIsNoIpV6AndEvaluatesToFalse()
    {
        assertFalse(IpAddress::isValidV6(''));
    }

    /**
     * @test
     */
    public function booleansAreNoIpV6AndResultInFalse()
    {
        assertFalse(IpAddress::isValidV6(true));
        assertFalse(IpAddress::isValidV6(false));
    }

    /**
     * @test
     */
    public function singleNumbersAreNoIpV6AndResultInFalse()
    {
        assertFalse(IpAddress::isValidV6(4));
    }

    /**
     * @test
     */
    public function ipv4EvaluatesToFalse()
    {
        assertFalse(IpAddress::isValidV6('1.2.3.4'));
    }

    /**
     * @test
     */
    public function invalidIpV6WithMissingPartEvaluatesToFalse()
    {
        assertFalse(IpAddress::isValidV6(':1'));
    }

    /**
     * @test
     */
    public function invalidIpV6EvaluatesToFalse()
    {
        assertFalse(IpAddress::isValidV6('::ffffff:::::a'));
    }

    /**
     * @test
     */
    public function invalidIpV6WithHexquadAtStartEvaluatesToFalse()
    {
        assertFalse(IpAddress::isValidV6('XXXX::a574:382b:23c1:aa49:4592:4efe:9982'));
    }

    /**
     * @test
     */
    public function invalidIpV6WithHexquadAtEndEvaluatesToFalse()
    {
        assertFalse(IpAddress::isValidV6('9982::a574:382b:23c1:aa49:4592:4efe:XXXX'));
    }

    /**
     * @test
     */
    public function invalidIpV6WithHexquadEvaluatesToFalse()
    {
        assertFalse(IpAddress::isValidV6('a574::XXXX:382b:23c1:aa49:4592:4efe:9982'));
    }

    /**
     * @test
     */
    public function invalidIpV6WithHexDigitEvaluatesToFalse()
    {
        assertFalse(IpAddress::isValidV6('a574::382X:382b:23c1:aa49:4592:4efe:9982'));
    }

    /**
     * @test
     */
    public function correctIpV6EvaluatesToTrue()
    {
        assertTrue(IpAddress::isValidV6('febc:a574:382b:23c1:aa49:4592:4efe:9982'));
    }

    /**
     * @test
     */
    public function localhostIpV6EvaluatesToTrue()
    {
        assertTrue(IpAddress::isValidV6('::1'));
    }

    /**
     * @test
     */
    public function shortenedIpV6EvaluatesToTrue()
    {
        assertTrue(IpAddress::isValidV6('febc:a574:382b::4592:4efe:9982'));
    }

    /**
     * @test
     */
    public function evenMoreShortenedIpV6EvaluatesToTrue()
    {
        assertTrue(IpAddress::isValidV6('febc::23c1:aa49:0:0:9982'));
    }

    /**
     * @test
     */
    public function singleShortenedIpV6EvaluatesToTrue()
    {
        assertTrue(IpAddress::isValidV6('febc:a574:2b:23c1:aa49:4592:4efe:9982'));
    }

    /**
     * @test
     */
    public function shortenedPrefixIpV6EvaluatesToTrue()
    {
        assertTrue(IpAddress::isValidV6('::382b:23c1:aa49:4592:4efe:9982'));
    }

    /**
     * @test
     */
    public function shortenedPostfixIpV6EvaluatesToTrue()
    {
        assertTrue(IpAddress::isValidV6('febc:a574:382b:23c1:aa49::'));
    }

    public function invalidValues(): array
    {
        return [['foo'], [-1.5], [true], [false]];
    }

    /**
     * @test
     * @dataProvider  invalidValues
     */
    public function constructWithInvalidValueThrowsIllegalArgumentException($invalidValue)
    {
        expect(function() use ($invalidValue) {
                new IpAddress($invalidValue);
        })->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function createWithLong()
    {
        assert(new IpAddress(2130706433), equals('127.0.0.1'));
    }

    public function validValues(): array
    {
        return [
                [2130706433, IpAddress::V4],
                ['127.0.0.1', IpAddress::V4],
                ['febc:a574:382b:23c1:aa49::', IpAddress::V6],
                ['::382b:23c1:aa49:4592:4efe:9982', IpAddress::V6],
                ['::1', IpAddress::V6]
        ];
    }

    /**
     * @test
     * @dataProvider  validValues
     * @since  7.0.0
     */
    public function typeReturnsInfoBasedOnValue(string $value, string $expectedType)
    {
        assert(IpAddress::castFrom($value)->type(), equals($expectedType));
    }

    /**
     * @test
     * @dataProvider  validValues
     * @since  7.0.0
     */
    public function isVxReturnsTrueBasedOnType(string $value, string $expectedType)
    {
        if (IpAddress::V4 === $expectedType) {
            assertTrue(IpAddress::castFrom($value)->isV4());
        } else {
            assertTrue(IpAddress::castFrom($value)->isV6());
        }
    }

    /**
     * @test
     * @dataProvider  validValues
     * @since  7.0.0
     */
    public function isVxReturnsFalseBasedOnType(string $value, string $expectedType)
    {
        if (IpAddress::V4 === $expectedType) {
            assertFalse(IpAddress::castFrom($value)->isV6());
        } else {
            assertFalse(IpAddress::castFrom($value)->isV4());
        }
    }

    /**
     * @test
     */
    public function longNotationIsTransformedIntoStringNotation()
    {
        assert(IpAddress::castFrom(2130706433), equals('127.0.0.1'));
    }

    /**
     * @test
     */
    public function castFromCreatesIpAddress()
    {
        assert(IpAddress::castFrom('127.0.0.1'), equals('127.0.0.1'));
    }

    /**
     * @test
     */
    public function castFromInstanceReturnsInstance()
    {
        $ipAddress = new IpAddress('127.0.0.1');
        assert(IpAddress::castFrom($ipAddress), isSameAs($ipAddress));
    }

    /**
     * @test
     */
    public function asLongReturnsLongValueForIpAddress()
    {
        assert(IpAddress::castFrom('127.0.0.1')->asLong(), equals(2130706433));
    }

    /**
     * @test
     */
    public function createSocketReturnsSocketInstance()
    {
        assert(
                IpAddress::castFrom('127.0.0.1')->createSocket(80),
                isInstanceOf(Socket::class)
        );
    }

    /**
     * @test
     */
    public function createSecureSocketReturnsSocketInstance()
    {
        assert(
                IpAddress::castFrom('127.0.0.1')->createSecureSocket(443),
                isInstanceOf(Socket::class)
        );
    }

    /**
     * @test
     */
    public function createSecureSocketUsesSsl()
    {
        assertTrue(
                IpAddress::castFrom('127.0.0.1')
                        ->createSecureSocket(443)
                        ->usesSsl()
        );
    }

    /**
     * @test
     * @since  8.0.0
     */
    public function openSocketReturnsStreamInstance()
    {
        $fsockopen = NewCallable::of('fsockopen')->mapCall(fopen(__FILE__, 'rb'));
        assert(
                IpAddress::castFrom('127.0.0.1')->openSocket(80, 1, $fsockopen),
                isInstanceOf(Stream::class)
        );
    }

    /**
     * @test
     * @since  8.0.0
     */
    public function openSocketDoesNotUseTls()
    {
        $fsockopen = NewCallable::of('fsockopen')->mapCall(fopen(__FILE__, 'rb'));
        assertFalse(
                IpAddress::castFrom('127.0.0.1')
                        ->openSocket(80, 1, $fsockopen)
                        ->usesTls()
        );
    }

    /**
     * @test
     * @since  8.0.0
     */
    public function openSecureSocketReturnsStreamInstance()
    {
        $fsockopen = NewCallable::of('fsockopen')->mapCall(fopen(__FILE__, 'rb'));
        assert(
                IpAddress::castFrom('127.0.0.1')->openSecureSocket(443, 1, $fsockopen),
                isInstanceOf(Stream::class)
        );
    }

    /**
     * @test
     * @since  8.0.0
     */
    public function openSecureSocketUsesTls()
    {
        $fsockopen = NewCallable::of('fsockopen')->mapCall(fopen(__FILE__, 'rb'));
        assertTrue(
                IpAddress::castFrom('127.0.0.1')
                        ->openSecureSocket(443, 1, $fsockopen)
                        ->usesTls()
        );
    }

    public function containedInCidr(): array
    {
        return [['10.16.0.1', '10.16', '13'],
                ['10.23.255.253', '10.16', 13],
                ['10.23.255.254', '10.16', '13'],
                ['172.19.13.1', '172.19.13', '24'],
                ['172.19.13.2', '172.19.13', 24],
                ['172.19.13.253', '172.19.13', '24'],
                ['172.19.13.254', '172.19.13', '24'],
                ['217.160.127.241', '217.160.127.240', '28'],
                ['217.160.127.242', '217.160.127.240', '28'],
                ['217.160.127.253', '217.160.127.240', 28],
                ['217.160.127.254', '217.160.127.240', 28]
        ];
    }

    /**
     * @test
     * @dataProvider  containedInCidr
     */
    public function isInCidrRangeReturnsTrueIfIpIsInRange(string $ip, string $cidrIpShort, $cidrMask)
    {
        assertTrue(
                IpAddress::castFrom($ip)->isInCidrRange($cidrIpShort, $cidrMask)
        );
    }

    public function notContainedInCidr(): array
    {
        return [['10.15.0.1', '10.16', '13'],
                ['10.24.0.1', '10.16', 13],
                ['172.19.12.254', '172.19.13', '24'],
                ['172.19.14.1', '172.19.13', 24],
                ['217.160.127.238', '217.160.127.240', '28'],
                ['217.160.127.239', '217.160.127.240', 28],
                ['217.160.128.1', '217.160.127.240', '28']
        ];
    }

    /**
     * @test
     * @dataProvider  notContainedInCidr
     */
    public function isInCidrRangeReturnsFalseIfIpIsNotInRange(string $ip, string $cidrIpShort, $cidrMask)
    {
        assertFalse(
                IpAddress::castFrom($ip)->isInCidrRange($cidrIpShort, $cidrMask)
        );
    }

    /**
     * @test
     * @since  8.0.0
     */
    public function isInCidrRangeThrowsWhenCidrMaskNoValidInteger()
    {
        $ip = new IpAddress('172.19.14.1');
        expect(function() use ($ip) { $ip->isInCidrRange('172.19.13', 'invalid'); })
                ->throws(\InvalidArgumentException::class);
    }
}
