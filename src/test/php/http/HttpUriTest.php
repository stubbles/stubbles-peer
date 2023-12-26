<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer\http;
use bovigo\callmap\NewCallable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stubbles\peer\MalformedUri;

use function bovigo\assert\{
    assertThat,
    assertFalse,
    assertTrue,
    expect,
    fail,
    predicate\equals,
    predicate\isInstanceOf,
    predicate\isNotSameAs,
    predicate\isSameAs
};
/**
 * Test for stubbles\peer\http\HttpUri.
 */
#[Group('peer')]
#[Group('peer_http')]
class HttpUriTest extends TestCase
{
    /**
     * @since  2.0.0
     */
    #[Test]
    public function canCreateInstanceForSchemeHttp(): void
    {
        assertThat(
            HttpUri::fromString('http://example.net/'),
            isInstanceOf(HttpUri::class)
        );
    }

     /**
     * @since  2.0.0
     */
    #[Test]
    public function canCreateInstanceForSchemeHttps(): void
    {
        assertThat(
            HttpUri::fromString('https://example.net/'),
            isInstanceOf(HttpUri::class)
        );
    }

    #[Test]
    public function canNotCreateHttpUriFromInvalidHost(): void
    {
        expect(function() { HttpUri::fromString('http://:'); })
            ->throws(MalformedUri::class);
    }

    /**
     * @since   8.0.0
     * @return  array<string[]>
     */
    public static function urisWithEmptyHost(): array
    {
        return [['http:///foobar.html'], ['http:///'], ['http://?foo=bar']];
    }

    /**
     * @since  8.0.0
     */
    #[Test]
    #[DataProvider('urisWithEmptyHost')]
    public function canNotCreateHttpUriFromEmptyHost(string $emptyHost): void
    {
        expect(function() use ($emptyHost) {
            HttpUri::fromString($emptyHost);
        })->throws(MalformedUri::class);
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function createInstanceForOtherSchemeThrowsMalformedUri(): void
    {
        expect(function() {
            HttpUri::fromString('invalid://example.net/');
        })->throws(MalformedUri::class);
    }

    #[Test]
    public function createInstanceFromInvalidUriThrowsMalformedUri(): void
    {
        expect(function() {
            HttpUri::fromString('invalid');
        })->throws(MalformedUri::class);
    }

    #[Test]
    public function createInstanceFromEmptyStringThrowsMalformedUri(): void
    {
        expect(function() { HttpUri::fromString(''); })
            ->throws(MalformedUri::class);
    }

    /**
     * @since  8.0.0
     */
    #[Test]
    public function createWithSyntacticallyInvalidUriThrowsMalformedUri(): void
    {
        expect(function() { HttpUri::fromString('http://fööbär?:'); })
            ->throws(MalformedUri::class);
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function automaticallyAppensSlashAsPathIfNoPathSet(): void
    {
        assertThat(HttpUri::fromString('http://example.net')->path(), equals('/'));
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function hasDefaultPortIfNoPortGivenInSchemeHttp(): void
    {
        assertTrue(HttpUri::fromString('http://example.net/')->hasDefaultPort());
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function hasDefaultPortIfDefaultPortGivenInSchemeHttp(): void
    {
        assertTrue(
            HttpUri::fromString('http://example.net:80/')->hasDefaultPort()
        );
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function doesNotHaveDefaultPortIfOtherPortGivenInSchemeHttp(): void
    {
        assertFalse(
            HttpUri::fromString('http://example.net:8080/')->hasDefaultPort()
        );
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function hasDefaultPortIfNoPortGivenInSchemeHttps(): void
    {
        assertTrue(
            HttpUri::fromString('https://example.net/')->hasDefaultPort()
        );
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function hasDefaultPortIfDefaultPortGivenInSchemeHttps(): void
    {
        assertTrue(
            HttpUri::fromString('https://example.net:443/')->hasDefaultPort()
        );
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function doesNotHaveDefaultPortIfOtherPortGivenInSchemeHttps(): void
    {
        assertFalse(
            HttpUri::fromString('https://example.net:8080/')->hasDefaultPort()
        );
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function getPortReturnsGivenPort(): void
    {
        assertThat(
            HttpUri::fromString('http://example.net:8080/')->port(),
            equals(8080)
        );
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function getPortReturns80IfSchemeIsHttp(): void
    {
        assertThat(
            HttpUri::fromString('http://example.net/')->port(),
            equals(80)
        );
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function getPortReturns443IfSchemeIsHttp(): void
    {
        assertThat(
            HttpUri::fromString('https://example.net/')->port(),
            equals(443)
        );
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function isHttpIfSchemeIsHttp(): void
    {
        assertTrue(HttpUri::fromString('http://example.net/')->isHttp());
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function isNotHttpIfSchemeIsHttps(): void
    {
        assertFalse(HttpUri::fromString('https://example.net/')->isHttp());
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function isHttpsIfSchemeIsHttps(): void
    {
        assertTrue(HttpUri::fromString('https://example.net/')->isHttps());
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function isNotHttpsIfSchemeIsHttp(): void
    {
        assertFalse(HttpUri::fromString('http://example.net/')->isHttps());
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function returnsSameInstanceWhenTransposingHttpToHttp(): void
    {
        $httpUri = HttpUri::fromString('http://example.net/');
        assertThat($httpUri->toHttp(), isSameAs($httpUri));
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function returnsDifferentInstanceWhenTransposingHttpToHttps(): void
    {
        $httpUri = HttpUri::fromString('http://example.net/');
        assertThat($httpUri->toHttps(), isNotSameAs($httpUri));
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function transposingToHttpsLeavesEverythingExceptSchemeAndPort(): void
    {
        assertThat(
            HttpUri::fromString('http://example.net:8080/foo.php?bar=baz#top')
                ->toHttps()
                ->asString(),
            equals('https://example.net/foo.php?bar=baz#top')
        );
    }

    /**
     * @since  4.0.2
     */
    #[Test]
    public function transposingToHttpDoesNotChangeOriginalPort(): void
    {
        assertThat(
            HttpUri::fromString('http://example.net:8080/foo.php?bar=baz#top')
                ->toHttp()
                ->asString(),
            equals('http://example.net:8080/foo.php?bar=baz#top')
        );
    }

    /**
     * @since  4.1.1
     */
    #[Test]
    public function transposingToHttpUsesDefaultPortToDefaultIfDefault(): void
    {
        assertThat(
            HttpUri::fromString('https://example.net/foo.php?bar=baz#top')
                ->toHttp()
                ->asString(),
            equals('http://example.net/foo.php?bar=baz#top')
        );
    }

    /**
     * @since  8.0.0
     */
    #[Test]
    public function transposingHttpWithPortToHttpAppliesGivenPort(): void
    {
        assertThat(
            HttpUri::fromString('http://example.net:80/foo.php?bar=baz#top')
                ->toHttp(8080)
                ->asString(),
            equals('http://example.net:8080/foo.php?bar=baz#top')
        );
    }

    /**
     * @since  8.0.0
     */
    #[Test]
    public function transposingHttpWithoutPortToHttpAppliesGivenPort(): void
    {
        assertThat(
            HttpUri::fromString('http://example.net/foo.php?bar=baz#top')
                ->toHttp(8080)
                ->asString(),
            equals('http://example.net:8080/foo.php?bar=baz#top')
        );
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function returnsSameInstanceWhenTransposingHttpsToHttps(): void
    {
        $httpUri = HttpUri::fromString('https://example.net/');
        assertThat($httpUri->toHttps(), isSameAs($httpUri));
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function returnsDifferentInstanceWhenTransposingHttpsToHttp(): void
    {
        $httpUri = HttpUri::fromString('https://example.net/');
        assertThat($httpUri->toHttp(), isNotSameAs($httpUri));
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function transposingToHttpLeavesEverythingExceptSchemeAndPort(): void
    {
        assertThat(
            HttpUri::fromString('https://example.net:8080/foo.php?bar=baz#top')
                ->toHttp()
                ->asString(),
            equals('http://example.net/foo.php?bar=baz#top')
        );
    }

    /**
     * @since  4.0.2
     */
    #[Test]
    public function transposingToHttpsWithDifferentPort(): void
    {
        assertThat(
            HttpUri::fromString('https://example.net:8080/foo.php?bar=baz#top')
                ->toHttps()
                ->asString(),
            equals('https://example.net:8080/foo.php?bar=baz#top')
        );
    }

    /**
     * @since  4.1.1
     */
    #[Test]
    public function transposingToHttpsUsesDefaultPortIfIsDefaultPort(): void
    {
        assertThat(
            HttpUri::fromString('http://example.net/foo.php?bar=baz#top')
                ->toHttps()
                ->asString(),
            equals('https://example.net/foo.php?bar=baz#top')
        );
    }

    /**
     * @since  8.0.0
     */
    #[Test]
    public function transposingHttpsWithPortToHttpsAppliesGivenPort(): void
    {
        assertThat(
            HttpUri::fromString('https://example.net:443/foo.php?bar=baz#top')
                ->toHttps(8080)
                ->asString(),
            equals('https://example.net:8080/foo.php?bar=baz#top')
        );
    }

    /**
     * @since  8.0.0
     */
    #[Test]
    public function transposingHttpsWithoutPortToHttpsAppliesGivenPort(): void
    {
        assertThat(
            HttpUri::fromString('https://example.net/foo.php?bar=baz#top')
                ->toHttps(8080)
                ->asString(),
            equals('https://example.net:8080/foo.php?bar=baz#top')
        );
    }

    #[Test]
    public function connectCreatesHttpConnection(): void
    {
        assertThat(
            HttpUri::fromString('http://example.net/')->connect(),
            isInstanceOf(HttpConnection::class)
        );
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function createSocketForHttpDoesNotYieldSocketWithSecureConnection(): void
    {
        assertFalse(
            HttpUri::fromString('http://example.net/')
                ->createSocket()
                ->usesSsl()
        );
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function createSocketForHttpsDoesYieldSocketWithSecureConnection(): void
    {
        assertTrue(
            HttpUri::fromString('https://example.net/')
                ->createSocket()
                ->usesSsl()
        );
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function openSocketUsesDefaultTimeout(): void
    {
        $handle = fopen(__FILE__, 'rb');
        if (false === $handle) {
            fail('Could not open file to retrieve handle for test');
        }

        $fsockopen = NewCallable::of('fsockopen')->returns($handle);
        assertThat(
            HttpUri::fromString('http://example.net/')
                ->openSocket(5, $fsockopen)
                ->timeout(),
            equals(5)
        );
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function openSocketUsesGivenTimeout(): void
    {
        $fsockopen = NewCallable::of('fsockopen')->returns(fopen(__FILE__, 'rb'));
        assertThat(
            HttpUri::fromString('http://example.net/')
                ->openSocket(2, $fsockopen)
                ->timeout(),
            equals(2)
        );
    }

    /**
     * @since  4.0.0
     */
    #[Test]
    public function createInstanceWithUserInfoThrowsMalformedUriForDefaultRfc(): void
    {
        expect(function() {
            HttpUri::fromString('http://user:password@example.net/');
        })->throws(MalformedUri::class);
    }

    /**
     * @since  4.0.0
     */
    #[Test]
    public function createInstanceWithUserInfoThrowsMalformedUriForRfc7230(): void
    {
        expect(function() {
            HttpUri::fromString('http://user:password@example.net/', Http::RFC_7230);
        })->throws(MalformedUri::class);
    }

    /**
     * @since  4.0.0
     */
    #[Test]
    public function createInstanceWithUserInfoThrowsNoMalformedUriForRfc2616(): void
    {
        $uri = 'http://user:password@example.net/';
        assertThat(
            HttpUri::fromString($uri, Http::RFC_2616)->asString(),
            equals($uri)
        );
    }

    /**
     * @since  4.0.0
     */
    #[Test]
    public function castFromInstanceReturnsInstance(): void
    {
        $uri = HttpUri::fromString('http://example.net/');
        assertThat(HttpUri::castFrom($uri), isSameAs($uri));
    }

    /**
     * @since  4.0.0
     */
    #[Test]
    public function castFromStringeReturnsInstance(): void
    {
        $uri = HttpUri::fromString('http://example.net/');
        assertThat(HttpUri::castFrom('http://example.net/'), equals($uri));
    }

    /**
     * @since  4.0.0
     */
    #[Test]
    public function createFromPartsWithInvalidSchemeThrowsMalformedUri(): void
    {
        expect(function() {
            HttpUri::fromParts('foo', 'localhost');
        })->throws(MalformedUri::class);
    }

    /**
     * @since  4.0.0
     */
    #[Test]
    public function createFromPartsWithDefaultPortAndPathAndNoQueryString(): void
    {
        assertThat(HttpUri::fromParts('http', 'localhost'), equals('http://localhost/'));
    }

    /**
     * @since  4.0.0
     */
    #[Test]
    public function createFromAllParts(): void
    {
        assertThat(
            HttpUri::fromParts('https', 'localhost', 8080, '/index.php', 'foo=bar'),
            equals('https://localhost:8080/index.php?foo=bar')
        );
    }
    
    /**
     * @since  4.0.0
     */
    #[Test]
    public function fromPartsReturnsInstanceOfHttpUri(): void
    {
        assertThat(
            HttpUri::fromParts('https', 'localhost', 8080, '/index.php', 'foo=bar'),
            isInstanceOf(HttpUri::class)
        );
    }

    /**
     * @since  5.5.0
     */
    #[Test]
    public function withPathExchangesPathCompletely(): void
    {
        assertThat(
            HttpUri::fromString('http://example.org/foo')->withPath('/bar'),
            equals('http://example.org/bar')
        );
    }

    /**
     * @since  5.5.0
     */
    #[Test]
    public function withPathReturnsNewInstance(): void
    {
        $uri = HttpUri::fromString('http://example.org/foo');
        assertThat($uri->withPath('/bar'), isNotSameAs($uri));
    }

    /**
     * @return  array<mixed[]>
     */
    public static function invalidValues(): array
    {
        return [
            ['invalid'],
            ['ftp://example.net']
        ];
    }

    #[Test]
    #[DataProvider('invalidValues')]
    public function invalidValueEvaluatesToFalse(mixed $invalid): void
    {
        assertFalse(HttpUri::isValid($invalid));
    }

    /**
     * @return  array<mixed[]>
     */
    public static function validValues(): array
    {
        return [
            ['http://localhost/'],
            [HttpUri::fromString('http://localhost/')]
        ];
    }

    #[Test]
    #[DataProvider('validValues')]
    public function validHttpUrlWithDnsEntryEvaluatesToTrue(string|HttpUri $value): void
    {
        assertTrue(HttpUri::isValid($value));
    }

    /**
     * @return  array<mixed[]>
     */
    public static function validValuesWithoutDnsEntry(): array
    {
        return [
            ['http://stubbles.doesNotExist/'],
            [HttpUri::fromString('http://stubbles.doesNotExist/')]
        ];
    }

    #[Test]
    #[DataProvider('validValuesWithoutDnsEntry')]
    public function validHttpUrlWithoutDnsEntryEvaluatesToTrue(string|HttpUri $value): void
    {
        assertTrue(HttpUri::isValid($value));
    }

    #[Test]
    #[DataProvider('invalidValues')]
    public function invalidValueEvaluatesToFalseWhenTestedForExistance(mixed $invalid): void
    {
        assertFalse(HttpUri::exists($invalid));
    }

    #[Test]
    #[DataProvider('validValues')]
    public function validHttpUrlWithDnsEntryEvaluatesToTrueWhenTestedForExistance(string|HttpUri $value): void
    {
        assertTrue(HttpUri::exists($value));
    }

    #[Test]
    public function validHttpUrlWithoutDnsEntryEvaluatesToFalseWhenTestedForExistance(): void
    {
        assertFalse(HttpUri::exists(
            'http://stubbles.doesNotExist/',
            NewCallable::of('checkdnsrr')->returns(false)
        ));
    }
}
