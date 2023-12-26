<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer;
use bovigo\callmap\NewCallable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\{
    assertThat,
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
 * @since  4.0.0
 */
#[Group('peer')]
class IpAddressTest extends TestCase
{
    /**
     * @since  7.1.0
     */
    #[Test]
    public function stringIsNoIpAndEvaluatesToFalse(): void
    {
        assertFalse(IpAddress::isValid('foo'));
    }

    /**
     * @since  7.1.0
     */
    #[Test]
    public function emptyStringIsNoIpAndEvaluatesToFalse(): void
    {
        assertFalse(IpAddress::isValid(''));
    }

    /**
     * @since  7.1.0
     */
    #[Test]
    public function isValidForIpV4(): void
    {
        assertTrue(IpAddress::isValid('127.0.0.1'));
    }

    /**
     * @since  7.1.0
     */
    #[Test]
    public function isValidForIpV6(): void
    {
        assertTrue(IpAddress::isValid('febc:a574:382b:23c1:aa49:4592:4efe:9982'));
    }

    #[Test]
    public function stringIsNoIpV4AndEvaluatesToFalse(): void
    {
        assertFalse(IpAddress::isValidV4('foo'));
    }

    #[Test]
    public function emptyStringIsNoIpV4AndEvaluatesToFalse(): void
    {
        assertFalse(IpAddress::isValidV4(''));
    }

    #[Test]
    public function invalidIpV4WithMissingPartEvaluatesToFalse(): void
    {
        assertFalse(IpAddress::isValidV4('255.55.55'));
    }

    #[Test]
    public function invalidIpV4WithSuperflousPartEvaluatesToFalse(): void
    {
        assertFalse(IpAddress::isValidV4('111.222.333.444.555'));
    }

    #[Test]
    public function invalidIpV4WithMissingNumberEvaluatesToFalse(): void
    {
        assertFalse(IpAddress::isValidV4('1..3.4'));
    }

    #[Test]
    public function invalidIpV4WithNumberOutOfRangeEvaluatesToFalse(): void
    {
        assertFalse(IpAddress::isValidV4('1.256.3.4'));
    }

    #[Test]
    public function greatestIpV4EvaluatesToTrue(): void
    {
        assertTrue(IpAddress::isValidV4('255.255.255.255'));
    }

    #[Test]
    public function lowestIpV4EvaluatesToTrue(): void
    {
        assertTrue(IpAddress::isValidV4('0.0.0.0'));
    }

    #[Test]
    public function correctIpV4EvaluatesToTrue(): void
    {
        assertTrue(IpAddress::isValidV4('1.2.3.4'));
    }

    #[Test]
    public function stringIsNoIpV6AndEvaluatesToFalse(): void
    {
        assertFalse(IpAddress::isValidV6('foo'));
    }

    #[Test]
    public function emptyStringIsNoIpV6AndEvaluatesToFalse(): void
    {
        assertFalse(IpAddress::isValidV6(''));
    }

    #[Test]
    public function ipv4EvaluatesToFalse(): void
    {
        assertFalse(IpAddress::isValidV6('1.2.3.4'));
    }

    #[Test]
    public function invalidIpV6WithMissingPartEvaluatesToFalse(): void
    {
        assertFalse(IpAddress::isValidV6(':1'));
    }

    #[Test]
    public function invalidIpV6EvaluatesToFalse(): void
    {
        assertFalse(IpAddress::isValidV6('::ffffff:::::a'));
    }

    #[Test]
    public function invalidIpV6WithHexquadAtStartEvaluatesToFalse(): void
    {
        assertFalse(IpAddress::isValidV6('XXXX::a574:382b:23c1:aa49:4592:4efe:9982'));
    }

    #[Test]
    public function invalidIpV6WithHexquadAtEndEvaluatesToFalse(): void
    {
        assertFalse(IpAddress::isValidV6('9982::a574:382b:23c1:aa49:4592:4efe:XXXX'));
    }

    #[Test]
    public function invalidIpV6WithHexquadEvaluatesToFalse(): void
    {
        assertFalse(IpAddress::isValidV6('a574::XXXX:382b:23c1:aa49:4592:4efe:9982'));
    }

    #[Test]
    public function invalidIpV6WithHexDigitEvaluatesToFalse(): void
    {
        assertFalse(IpAddress::isValidV6('a574::382X:382b:23c1:aa49:4592:4efe:9982'));
    }

    #[Test]
    public function correctIpV6EvaluatesToTrue(): void
    {
        assertTrue(IpAddress::isValidV6('febc:a574:382b:23c1:aa49:4592:4efe:9982'));
    }

    #[Test]
    public function localhostIpV6EvaluatesToTrue(): void
    {
        assertTrue(IpAddress::isValidV6('::1'));
    }

    #[Test]
    public function shortenedIpV6EvaluatesToTrue(): void
    {
        assertTrue(IpAddress::isValidV6('febc:a574:382b::4592:4efe:9982'));
    }

    #[Test]
    public function evenMoreShortenedIpV6EvaluatesToTrue(): void
    {
        assertTrue(IpAddress::isValidV6('febc::23c1:aa49:0:0:9982'));
    }

    #[Test]
    public function singleShortenedIpV6EvaluatesToTrue(): void
    {
        assertTrue(IpAddress::isValidV6('febc:a574:2b:23c1:aa49:4592:4efe:9982'));
    }

    #[Test]
    public function shortenedPrefixIpV6EvaluatesToTrue(): void
    {
        assertTrue(IpAddress::isValidV6('::382b:23c1:aa49:4592:4efe:9982'));
    }

    #[Test]
    public function shortenedPostfixIpV6EvaluatesToTrue(): void
    {
        assertTrue(IpAddress::isValidV6('febc:a574:382b:23c1:aa49::'));
    }

    #[Test]
    public function createWithLong(): void
    {
        assertThat(new IpAddress(2130706433), equals('127.0.0.1'));
    }

    /**
     * @return  array<mixed[]>
     */
    public static function validValues(): array
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
     * @since  7.0.0
     */
    #[Test]
    #[DataProvider('validValues')]
    public function typeReturnsInfoBasedOnValue(int|string $value, string $expectedType): void
    {
        assertThat(IpAddress::castFrom($value)->type(), equals($expectedType));
    }

    /**
     * @since  7.0.0
     */
    #[Test]
    #[DataProvider('validValues')]
    public function isVxReturnsTrueBasedOnType(int|string $value, string $expectedType): void
    {
        if (IpAddress::V4 === $expectedType) {
            assertTrue(IpAddress::castFrom($value)->isV4());
        } else {
            assertTrue(IpAddress::castFrom($value)->isV6());
        }
    }

    /**
     * @since  7.0.0
     */
    #[Test]
    #[DataProvider('validValues')]
    public function isVxReturnsFalseBasedOnType(int|string $value, string $expectedType): void
    {
        if (IpAddress::V4 === $expectedType) {
            assertFalse(IpAddress::castFrom($value)->isV6());
        } else {
            assertFalse(IpAddress::castFrom($value)->isV4());
        }
    }

    #[Test]
    public function longNotationIsTransformedIntoStringNotation(): void
    {
        assertThat(IpAddress::castFrom(2130706433), equals('127.0.0.1'));
    }

    #[Test]
    public function castFromCreatesIpAddress(): void
    {
        assertThat(IpAddress::castFrom('127.0.0.1'), equals('127.0.0.1'));
    }

    #[Test]
    public function castFromInstanceReturnsInstance(): void
    {
        $ipAddress = new IpAddress('127.0.0.1');
        assertThat(IpAddress::castFrom($ipAddress), isSameAs($ipAddress));
    }

    #[Test]
    public function asLongReturnsLongValueForIpAddress(): void
    {
        assertThat(IpAddress::castFrom('127.0.0.1')->asLong(), equals(2130706433));
    }

    #[Test]
    public function createSocketReturnsSocketInstance(): void
    {
        assertThat(
                IpAddress::castFrom('127.0.0.1')->createSocket(80),
                isInstanceOf(Socket::class)
        );
    }

    #[Test]
    public function createSecureSocketReturnsSocketInstance(): void
    {
        assertThat(
                IpAddress::castFrom('127.0.0.1')->createSecureSocket(443),
                isInstanceOf(Socket::class)
        );
    }

    #[Test]
    public function createSecureSocketUsesSsl(): void
    {
        assertTrue(
                IpAddress::castFrom('127.0.0.1')
                        ->createSecureSocket(443)
                        ->usesSsl()
        );
    }

    /**
     * @since  8.0.0
     */
    #[Test]
    public function openSocketReturnsStreamInstance(): void
    {
        $fsockopen = NewCallable::of('fsockopen')->returns(fopen(__FILE__, 'rb'));
        assertThat(
            IpAddress::castFrom('127.0.0.1')->openSocket(80, 1, $fsockopen),
            isInstanceOf(Stream::class)
        );
    }

    /**
     * @since  8.0.0
     */
    #[Test]
    public function openSocketDoesNotUseTls(): void
    {
        $fsockopen = NewCallable::of('fsockopen')->returns(fopen(__FILE__, 'rb'));
        assertFalse(
            IpAddress::castFrom('127.0.0.1')
                ->openSocket(80, 1, $fsockopen)
                ->usesTls()
        );
    }

    /**
     * @since  8.0.0
     */
    #[Test]
    public function openSecureSocketReturnsStreamInstance(): void
    {
        $fsockopen = NewCallable::of('fsockopen')->returns(fopen(__FILE__, 'rb'));
        assertThat(
            IpAddress::castFrom('127.0.0.1')->openSecureSocket(443, 1, $fsockopen),
            isInstanceOf(Stream::class)
        );
    }

    /**
     * @since  8.0.0
     */
    #[Test]
    public function openSecureSocketUsesTls(): void
    {
        $fsockopen = NewCallable::of('fsockopen')->returns(fopen(__FILE__, 'rb'));
        assertTrue(
            IpAddress::castFrom('127.0.0.1')
                ->openSecureSocket(443, 1, $fsockopen)
                ->usesTls()
        );
    }

    /**
     * @return  array<mixed[]>
     */
    public static function containedInCidr(): array
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

    #[Test]
    #[DataProvider('containedInCidr')]
    public function isInCidrRangeReturnsTrueIfIpIsInRange(string $ip, string $cidrIpShort, int|string $cidrMask): void
    {
        assertTrue(
            IpAddress::castFrom($ip)->isInCidrRange($cidrIpShort, $cidrMask)
        );
    }

    /**
     * @return  array<mixed[]>
     */
    public static function notContainedInCidr(): array
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

    #[Test]
    #[DataProvider('notContainedInCidr')]
    public function isInCidrRangeReturnsFalseIfIpIsNotInRange(
        string $ip,
        string $cidrIpShort,
        int|string $cidrMask
    ): void {
        assertFalse(
            IpAddress::castFrom($ip)->isInCidrRange($cidrIpShort, $cidrMask)
        );
    }

    /**
     * @since  8.0.0
     */
    #[Test]
    public function isInCidrRangeThrowsWhenCidrMaskNoValidInteger(): void
    {
        $ip = new IpAddress('172.19.14.1');
        expect(fn(): never => $ip->isInCidrRange('172.19.13', 'invalid'))
            ->throws(InvalidArgumentException::class);
    }
}
