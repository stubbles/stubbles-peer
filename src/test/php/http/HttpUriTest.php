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
use stubbles\peer\MalformedUri;

use function bovigo\assert\assert;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertNull;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\assert\predicate\isNotSameAs;
use function bovigo\assert\predicate\isSameAs;
/**
 * Test for stubbles\peer\http\HttpUri.
 *
 * @group  peer
 * @group  peer_http
 */
class HttpUriTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        CheckdnsrrResult::$value = null;
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function canCreateInstanceForSchemeHttp()
    {
        assert(
                HttpUri::fromString('http://example.net/'),
                isInstanceOf(HttpUri::class)
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function canCreateInstanceForSchemeHttps()
    {
        assert(
                HttpUri::fromString('https://example.net/'),
                isInstanceOf(HttpUri::class)
        );
    }

    /**
     * @test
     */
    public function canNotCreateHttpUriFromInvalidHost()
    {
        expect(function() {
                HttpUri::fromString('http://:');
        })->throws(MalformedUri::class);
    }

    /**
     * @since  8.0.0
     */
    public function urisWithEmptyHost(): array
    {
        return [['http:///foobar.html'], ['http:///'], ['http://?foo=bar']];
    }

    /**
     * @test
     * @dataProvider  urisWithEmptyHost
     * @since  8.0.0
     */
    public function canNotCreateHttpUriFromEmptyHost($emptyHost)
    {
        expect(function() use ($emptyHost) {
                HttpUri::fromString($emptyHost);
        })->throws(MalformedUri::class);
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function createInstanceForOtherSchemeThrowsMalformedUri()
    {
        expect(function() {
                HttpUri::fromString('invalid://example.net/');
        })->throws(MalformedUri::class);
    }

    /**
     * @test
     */
    public function createInstanceFromInvalidUriThrowsMalformedUri()
    {
        expect(function() {
                HttpUri::fromString('invalid');
        })->throws(MalformedUri::class);
    }

    /**
     * @test
     */
    public function createInstanceFromEmptyStringThrowsMalformedUri()
    {
        expect(function() { HttpUri::fromString(''); })
                ->throws(MalformedUri::class);
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function automaticallyAppensSlashAsPathIfNoPathSet()
    {
        assert(HttpUri::fromString('http://example.net')->path(), equals('/'));
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function hasDefaultPortIfNoPortGivenInSchemeHttp()
    {
        assertTrue(HttpUri::fromString('http://example.net/')->hasDefaultPort());
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function hasDefaultPortIfDefaultPortGivenInSchemeHttp()
    {
        assertTrue(
                HttpUri::fromString('http://example.net:80/')->hasDefaultPort()
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function doesNotHaveDefaultPortIfOtherPortGivenInSchemeHttp()
    {
        assertFalse(
                HttpUri::fromString('http://example.net:8080/')->hasDefaultPort()
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function hasDefaultPortIfNoPortGivenInSchemeHttps()
    {
        assertTrue(
                HttpUri::fromString('https://example.net/')->hasDefaultPort()
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function hasDefaultPortIfDefaultPortGivenInSchemeHttps()
    {
        assertTrue(
                HttpUri::fromString('https://example.net:443/')->hasDefaultPort()
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function doesNotHaveDefaultPortIfOtherPortGivenInSchemeHttps()
    {
        assertFalse(
                HttpUri::fromString('https://example.net:8080/')->hasDefaultPort()
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function getPortReturnsGivenPort()
    {
        assert(
                HttpUri::fromString('http://example.net:8080/')->port(),
                equals(8080)
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function getPortReturns80IfSchemeIsHttp()
    {
        assert(
                HttpUri::fromString('http://example.net/')->port(),
                equals(80)
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function getPortReturns443IfSchemeIsHttp()
    {
        assert(
                HttpUri::fromString('https://example.net/')->port(),
                equals(443)
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function isHttpIfSchemeIsHttp()
    {
        assertTrue(HttpUri::fromString('http://example.net/')->isHttp());
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function isNotHttpIfSchemeIsHttps()
    {
        assertFalse(HttpUri::fromString('https://example.net/')->isHttp());
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function isHttpsIfSchemeIsHttps()
    {
        assertTrue(HttpUri::fromString('https://example.net/')->isHttps());
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function isNotHttpsIfSchemeIsHttp()
    {
        assertFalse(HttpUri::fromString('http://example.net/')->isHttps());
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function returnsSameInstanceWhenTransposingHttpToHttp()
    {
        $httpUri = HttpUri::fromString('http://example.net/');
        assert($httpUri->toHttp(), isSameAs($httpUri));
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function returnsDifferentInstanceWhenTransposingHttpToHttps()
    {
        $httpUri = HttpUri::fromString('http://example.net/');
        assert($httpUri->toHttps(), isNotSameAs($httpUri));
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function transposingToHttpsLeavesEverythingExceptSchemeAndPort()
    {
        assert(
                HttpUri::fromString('http://example.net:8080/foo.php?bar=baz#top')
                       ->toHttps()
                       ->asString(),
                equals('https://example.net/foo.php?bar=baz#top')
        );
    }

    /**
     * @since  4.0.2
     * @test
     */
    public function transposingToHttpChangesPort()
    {
        assert(
                HttpUri::fromString('http://example.net:8080/foo.php?bar=baz#top')
                       ->toHttp()
                       ->asString(),
                equals('http://example.net:8080/foo.php?bar=baz#top')
        );
    }

    /**
     * @since  4.1.1
     * @test
     */
    public function transposingToHttpUsesDefaultPortToDefaultIfDefault()
    {
        assert(
                HttpUri::fromString('https://example.net/foo.php?bar=baz#top')
                       ->toHttp()
                       ->asString(),
                equals('http://example.net/foo.php?bar=baz#top')
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function returnsSameInstanceWhenTransposingHttpsToHttps()
    {
        $httpUri = HttpUri::fromString('https://example.net/');
        assert($httpUri->toHttps(), isSameAs($httpUri));
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function returnsDifferentInstanceWhenTransposingHttpsToHttp()
    {
        $httpUri = HttpUri::fromString('https://example.net/');
        assert($httpUri->toHttp(), isNotSameAs($httpUri));
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function transposingToHttpLeavesEverythingExceptSchemeAndPort()
    {
        assert(
                HttpUri::fromString('https://example.net:8080/foo.php?bar=baz#top')
                       ->toHttp()
                       ->asString(),
                equals('http://example.net/foo.php?bar=baz#top')
        );
    }

    /**
     * @since  4.0.2
     * @test
     */
    public function transposingToHttpsWithDifferentPort()
    {
        assert(
                HttpUri::fromString('https://example.net:8080/foo.php?bar=baz#top')
                       ->toHttps()
                       ->asString(),
                equals('https://example.net:8080/foo.php?bar=baz#top')
        );
    }

    /**
     * @since  4.1.1
     * @test
     */
    public function transposingToHttpsUsesDefaultPortIfIsDefaultPort()
    {
        assert(
                HttpUri::fromString('http://example.net/foo.php?bar=baz#top')
                       ->toHttps()
                       ->asString(),
                equals('https://example.net/foo.php?bar=baz#top')
        );
    }

    /**
     * @test
     */
    public function connectCreatesHttpConnection()
    {
        assert(
                HttpUri::fromString('http://example.net/')->connect(),
                isInstanceOf(HttpConnection::class)
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function createSocketForHttpDoesNotYieldSocketWithSecureConnection()
    {
        assertFalse(
                HttpUri::fromString('http://example.net/')
                       ->createSocket()
                       ->usesSsl()
        );
    }
    /**
     * @since  2.0.0
     * @test
     */
    public function createSocketForHttpsDoesYieldSocketWithSecureConnection()
    {
        assertTrue(
                HttpUri::fromString('https://example.net/')
                       ->createSocket()
                       ->usesSsl()
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function openSocketUsesDefaultTimeout()
    {
        assert(
               HttpUri::fromString('http://example.net/')
                      ->openSocket()
                      ->timeout(),
                equals(5)
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function openSocketUsesGivenTimeout()
    {
        assert(
                HttpUri::fromString('http://example.net/')
                       ->openSocket(2)
                       ->timeout(),
                equals(2)
        );
    }

    /**
     * @since  4.0.0
     * @test
     */
    public function createInstanceWithUserInfoThrowsMalformedUriForDefaultRfc()
    {
        expect(function() {
                HttpUri::fromString('http://user:password@example.net/');
        })->throws(MalformedUri::class);
    }

    /**
     * @since  4.0.0
     * @test
     */
    public function createInstanceWithUserInfoThrowsMalformedUriForRfc7230()
    {
        expect(function() {
                HttpUri::fromString('http://user:password@example.net/', Http::RFC_7230);
        })->throws(MalformedUri::class);
    }

    /**
     * @since  4.0.0
     * @test
     */
    public function createInstanceWithUserInfoThrowsNoMalformedUriForRfc2616()
    {
        $uri = 'http://user:password@example.net/';
        assert(
                HttpUri::fromString($uri, Http::RFC_2616)->asString(),
                equals($uri)
        );
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function castFromInstanceReturnsInstance()
    {
        $uri = HttpUri::fromString('http://example.net/');
        assert(HttpUri::castFrom($uri), isSameAs($uri));
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function castFromStringeReturnsInstance()
    {
        $uri = HttpUri::fromString('http://example.net/');
        assert(HttpUri::castFrom('http://example.net/'), equals($uri));
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function castFromOtherThrowsIllegalArgumentException()
    {
        expect(function() {
                HttpUri::castFrom(new \stdClass());
        })
        ->throws(\InvalidArgumentException::class)
        ->withMessage(
                'Uri must be a string containing a HTTP URI or an instance of '
                . HttpUri::class . ', but was stdClass'
        );
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function createFromPartsWithInvalidSchemeThrowsMalformedUri()
    {
        expect(function() {
                HttpUri::fromParts('foo', 'localhost');
        })->throws(MalformedUri::class);
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function createFromPartsWithDefaultPortAndPathAndNoQueryString()
    {
        assert(HttpUri::fromParts('http', 'localhost'), equals('http://localhost/'));
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function createFromAllParts()
    {
        assert(
                HttpUri::fromParts('https', 'localhost', 8080, '/index.php', 'foo=bar'),
                equals('https://localhost:8080/index.php?foo=bar')
        );
    }
    /**
     * @test
     * @since  4.0.0
     */
    public function fromPartsReturnsInstanceOfHttpUri()
    {
        assert(
                HttpUri::fromParts('https', 'localhost', 8080, '/index.php', 'foo=bar'),
                isInstanceOf(HttpUri::class)
        );
    }

    /**
     * @test
     * @since  5.5.0
     */
    public function withPathExchangesPathCompletely()
    {
        assert(
                HttpUri::fromString('http://example.org/foo')->withPath('/bar'),
                equals('http://example.org/bar')
        );
    }

    /**
     * @test
     * @since  5.5.0
     */
    public function withPathReturnsNewInstance()
    {
        $uri = HttpUri::fromString('http://example.org/foo');
        assert($uri->withPath('/bar'), isNotSameAs($uri));
    }

    public function invalidValues(): array
    {
        return [[null],
                [303],
                [true],
                [false],
                [''],
                ['invalid'],
                ['ftp://example.net']
        ];
    }

    /**
     * @test
     * @dataProvider  invalidValues
     */
    public function invalidValueEvaluatesToFalse($invalid)
    {
        assertFalse(HttpUri::isValid($invalid));
    }

    public function validValues(): array
    {
        return [
            ['http://localhost/'],
            [HttpUri::fromString('http://localhost/')]
        ];
    }

    /**
     * @test
     * @dataProvider  validValues
     */
    public function validHttpUrlWithDnsEntryEvaluatesToTrue($value)
    {
        assertTrue(HttpUri::isValid($value));
    }

    public function validValuesWithoutDnsEntry(): array
    {
        return [
            ['http://stubbles.doesNotExist/'],
            [HttpUri::fromString('http://stubbles.doesNotExist/')]
        ];
    }

    /**
     * @test
     * @dataProvider  validValuesWithoutDnsEntry
     */
    public function validHttpUrlWithoutDnsEntryEvaluatesToTrue($value)
    {
        assertTrue(HttpUri::isValid($value));
    }

    /**
     * @test
     * @dataProvider  invalidValues
     */
    public function invalidValueEvaluatesToFalseWhenTestedForExistance($invalid)
    {
        assertFalse(HttpUri::exists($invalid));
    }

    /**
     * @test
     * @dataProvider  validValues
     */
    public function validHttpUrlWithDnsEntryEvaluatesToTrueWhenTestedForExistance($value)
    {
        assertTrue(HttpUri::exists($value));
    }

    /**
     * @test
     */
    public function validHttpUrlWithoutDnsEntryEvaluatesToFalseWhenTestedForExistance()
    {
        CheckdnsrrResult::$value = false;
        assertFalse(HttpUri::exists('http://stubbles.doesNotExist/'));
    }
}
