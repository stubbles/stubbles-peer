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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function bovigo\callmap\verify;
use function bovigo\assert\{
    assertThat,
    assertEmptyString,
    assertFalse,
    assertNull,
    assertTrue,
    expect,
    predicate\equals,
    predicate\isNotSameAs
};
/**
 * Test for stubbles\peer\Uri.
 */
#[Group('peer')]
class UriTest extends TestCase
{
    #[Test]
    public function canNotCreateUriWithoutScheme(): void
    {
        expect(function() { Uri::fromString('stubbles.net'); })
            ->throws(MalformedUri::class);
    }

    #[Test]
    public function canNotCreateUriWithInvalidScheme(): void
    {
        expect(function() {
            Uri::fromString('404://stubbles.net');
        })->throws(MalformedUri::class);
    }

    #[Test]
    public function canNotCreateUriWithInvalidUser(): void
    {
        expect(function() {
            Uri::fromString('http://mi@ss@stubbles.net');
        })->throws(MalformedUri::class);
    }

    #[Test]
    public function canNotCreateUriWithInvalidPassword(): void
    {
        expect(function() {
            Uri::fromString('http://mi:s@s@stubbles.net');
        })->throws(MalformedUri::class);
    }

    #[Test]
    public function canNotCreateUriWithInvalidHost(): void
    {
        expect(function() {
            Uri::fromString('http://_:80');
        })->throws(MalformedUri::class);
    }

    #[Test]
    public function createFromEmptyStringThrowsMalformedUri(): void
    {
        expect(function() { Uri::fromString(''); })
            ->throws(MalformedUri::class);
    }

    #[Test]
    public function schemeIsRecognized(): void
    {
        assertThat(Uri::fromString('http://stubbles.net/')->scheme(), equals('http'));
    }

    #[Test]
    public function schemeIsRecognizedForIpAddresses(): void
    {
        assertThat(Uri::fromString('http://127.0.0.1')->scheme(), equals('http'));
    }

    #[Test]
    public function schemeIsRecognizedIfHostIsMissing(): void
    {
        assertThat(Uri::fromString('file:///home')->scheme(), equals('file'));
    }

    #[Test]
    public function hasDefaultPortReturnsFalseWhenPortSpecified(): void
    {
        assertFalse(
            Uri::fromString('http://stubbles.net:80/')->hasDefaultPort()
        );
    }

    /**
     * @since  8.0.0
     */
    #[Test]
    public function hasDefaultPortReturnsTrueWhenNoPortSpecified(): void
    {
        assertTrue(
            Uri::fromString('http://stubbles.net/')->hasDefaultPort()
        );
    }

    #[Test]
    public function hasNoUserIfNoUserGiven(): void
    {
        assertNull(Uri::fromString('ftp://stubbles.net')->user());
    }

    #[Test]
    public function hasDefaultUserIfNoUserGiven(): void
    {
        assertThat(
            Uri::fromString('ftp://stubbles.net')->user('mikey'),
            equals('mikey')
        );
    }

    #[Test]
    public function hasGivenUser(): void
    {
        assertThat(
            Uri::fromString('ftp://mikey@stubbles.net')->user(),
            equals('mikey')
        );
    }

    #[Test]
    public function hasGivenUserEvenIfDefaultChanged(): void
    {
        assertThat(
            Uri::fromString('ftp://mikey@stubbles.net')->user('other'),
            equals('mikey')
        );
    }

    #[Test]
    public function hasEmptyUser(): void
    {
        assertEmptyString(Uri::fromString('ftp://@stubbles.net')->user());
    }

    #[Test]
    public function hasEmptyUserEvenIfDefaultChanged(): void
    {
        assertEmptyString(Uri::fromString('ftp://@stubbles.net')->user('other'));
    }

    /**
     * @deprecated  since 8.0.0, will be removed with 9.0.0
     */
    #[Test]
    public function hasNoPasswordIfNoUserGiven(): void
    {
        assertNull(Uri::fromString('ftp://stubbles.net')->password());
    }

    /**
     * @deprecated  since 8.0.0, will be removed with 9.0.0
     */
    #[Test]
    public function hasNoDefaultPasswordIfNoUserGiven(): void
    {
        assertNull(Uri::fromString('ftp://stubbles.net')->password('secret'));
    }

    /**
     * @deprecated  since 8.0.0, will be removed with 9.0.0
     */
    #[Test]
    public function hasDefaultPasswordIfUserButNoPasswordGiven(): void
    {
        assertThat(
            Uri::fromString('ftp://mikey@stubbles.net')->password('secret'),
            equals('secret')
        );
    }

    /**
     * @deprecated  since 8.0.0, will be removed with 9.0.0
     */
    #[Test]
    public function hasGivenPassword(): void
    {
        assertThat(
            Uri::fromString('ftp://mikey:secret@stubbles.net')->password(),
            equals('secret')
        );
    }

    /**
     * @deprecated  since 8.0.0, will be removed with 9.0.0
     */
    #[Test]
    public function hasGivenPasswordEvenIfDefaultChanged(): void
    {
        assertThat(
            Uri::fromString('ftp://mikey:secret@stubbles.net')->password('other'),
            equals('secret')
        );
    }

    /**
     * @deprecated  since 8.0.0, will be removed with 9.0.0
     */
    #[Test]
    public function hasEmptyPassword(): void
    {
        assertEmptyString(
            Uri::fromString('ftp://mikey:@stubbles.net')->password()
        );
    }

    /**
     * @deprecated  since 8.0.0, will be removed with 9.0.0
     */
    #[Test]
    public function hasEmptyPasswordEvenIfDefaultChanged(): void
    {
        assertEmptyString(
            Uri::fromString('ftp://mikey:@stubbles.net')->password('other')
        );
    }

    #[Test]
    public function hasHostFromGivenUri(): void
    {
        assertThat(
            Uri::fromString('ftp://stubbles.net:21')->hostname(),
            equals('stubbles.net')
        );
    }

    #[Test]
    public function hostIsTransformedToLowercase(): void
    {
        assertThat(
            Uri::fromString('ftp://stUBBles.net:21')->hostname(),
            equals('stubbles.net')
        );
    }

    #[Test]
    public function hasNoHostIfUriDoesNotContainHost(): void
    {
        assertNull(Uri::fromString('file:///home')->hostname());
    }

    #[Test]
    public function getHostReturnsIpv4(): void
    {
        assertThat(
            Uri::fromString('http://127.0.0.1/')->hostname(),
            equals('127.0.0.1')
        );
    }

    #[Test]
    #[Group('bug258')]
    public function getHostReturnsIpv6ShortNotation(): void
    {
        assertThat(
            Uri::fromString('http://[2001:db8:12:34::1]/')->hostname(),
            equals('[2001:db8:12:34::1]')
        );
    }

    #[Test]
    #[Group('bug258')]
    public function getHostReturnsIpv6LongNotation(): void
    {
        assertThat(
            Uri::fromString('http://[2001:8d8f:1fe:5:abba:dbff:fefe:7755]:80/')
                ->hostname(),
            equals('[2001:8d8f:1fe:5:abba:dbff:fefe:7755]')
        );
    }

    #[Test]
    public function hasNoPortIfNoPortGiven(): void
    {
        assertNull(Uri::fromString('ftp://stubbles.net')->port());
    }

    #[Test]
    public function hasDefaultValueIfNoPortGiven(): void
    {
        assertThat(Uri::fromString('ftp://stubbles.net')->port(303), equals(303));
    }

    #[Test]
    public function hasGivenPortIfPortGiven(): void
    {
        assertThat(Uri::fromString('ftp://stubbles.net:21')->port(), equals(21));
    }

    #[Test]
    public function hasGivenPortFromIpv4Adress(): void
    {
        assertThat(Uri::fromString('ftp://127.0.01:21')->port(), equals(21));
    }

    #[Test]
    #[Group('bug258')]
    public function hasGivenPortFromIpv6AdressShortNotation(): void
    {
        assertThat(Uri::fromString('ftp://[2001:db8:12:34::1]:21')->port(), equals(21));
    }

    #[Test]
    #[Group('bug258')]
    public function hasGivenPortFromIpv6AdressLongNotation(): void
    {
        assertThat(
            Uri::fromString('ftp://[2001:8d8f:1fe:5:abba:dbff:fefe:7755]:21')->port(),
            equals(21)
        );
    }

    #[Test]
    public function hasGivenPortEvenIfDefaultChanged(): void
    {
        assertThat(Uri::fromString('ftp://stubbles.net:21')->port(303), equals(21));
    }

    #[Test]
    public function getPathReturnsEmptyStringIfNoPathInGivenUri(): void
    {
        assertEmptyString(Uri::fromString('http://stubbles.net')->path());
    }

    #[Test]
    public function getPathReturnsGivenPath(): void
    {
        assertThat(
            Uri::fromString('http://stubbles.net/index.php?foo=bar#baz')->path(),
            equals('/index.php')
        );
    }

    #[Test]
    public function getPathReturnsPathEvenIfNoHostPresent(): void
    {
        assertThat(Uri::fromString('file:///home')->path(), equals('/home'));
    }

    #[Test]
    public function hasNoQueryStringIfNoneInOriginalUri(): void
    {
        assertFalse(
            Uri::fromString('http://stubbles.net:80/')->hasQueryString()
        );
    }

    #[Test]
    public function hasQueryStringIfInOriginalUri(): void
    {
        assertTrue(
            Uri::fromString('http://stubbles.net:80/?foo=bar')->hasQueryString()
        );
    }

    #[Test]
    public function hasNoDnsRecordWithoutHost(): void
    {
        $checkdnsrr = NewCallable::of('checkdnsrr');
        assertFalse(Uri::fromString('file:///home/test.txt')->hasDnsRecord($checkdnsrr));
        verify($checkdnsrr)->wasNeverCalled();
    }

    #[Test]
    public function hasDnsRecordForLocalhost(): void
    {
        $checkdnsrr = NewCallable::of('checkdnsrr');
        assertTrue(Uri::fromString('http://localhost')->hasDnsRecord($checkdnsrr));
        verify($checkdnsrr)->wasNeverCalled();
    }

    #[Test]
    public function hasDnsRecordForIpv4Localhost(): void
    {
        $checkdnsrr = NewCallable::of('checkdnsrr');
        assertTrue(Uri::fromString('http://127.0.0.1')->hasDnsRecord($checkdnsrr));
        verify($checkdnsrr)->wasNeverCalled();
    }

    #[Test]
    #[Group('bug258')]
    public function hasDnsRecordForIpv6Localhost(): void
    {
        $checkdnsrr = NewCallable::of('checkdnsrr');
        assertTrue(Uri::fromString('http://[::1]')->hasDnsRecord($checkdnsrr));
        verify($checkdnsrr)->wasNeverCalled();
    }

    /**
     * @since  8.0.0
     */
    #[Test]
    public function hasNoDnsRecordForNonExistingHost(): void
    {
        assertFalse(
            Uri::fromString('http://foobar')->hasDnsRecord(
                NewCallable::of('checkdnsrr')->returns(false)
            )
        );
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function canBeCastedToString(): void
    {
        assertThat(
            (string) Uri::fromString('http://stubbles.net:80/index.php?content=features#top'),
            equals('http://stubbles.net:80/index.php?content=features#top')
        );
    }

    #[Test]
    public function asStringReturnsOriginalGivenUri(): void
    {
        assertThat(
            Uri::fromString('http://stubbles.net:80/index.php?content=features#top')
                ->asString(),
            equals('http://stubbles.net:80/index.php?content=features#top')
        );
    }

    #[Test]
    public function asStringWithoutPortReturnsOriginalGivenUriButWithoutPort(): void
    {
        assertThat(
            Uri::fromString('http://stubbles.net:80/index.php?content=features#top')
                ->asStringWithoutPort(),
            equals('http://stubbles.net/index.php?content=features#top')
        );
    }

    #[Test]
    public function asStringWithNonDefaultPortReturnsOriginalGivenUriWithPort(): void
    {
        assertThat(
            Uri::fromString('http://stubbles.net:80/index.php?content=features#top')
                ->asStringWithNonDefaultPort(),
            equals('http://stubbles.net:80/index.php?content=features#top')
        );
    }

    #[Test]
    public function asStringWithNonDefaultPortReturnsOriginalGivenUriWithoutPort(): void
    {
        assertThat(
            Uri::fromString('http://stubbles.net/index.php?content=features#top')
                ->asStringWithNonDefaultPort(),
            equals('http://stubbles.net/index.php?content=features#top')
        );
    }

    #[Test]
    public function asStringReturnsOriginalGivenUriWithUsernameAndPassword(): void
    {
        assertThat(
            Uri::fromString('http://mikey:secret@stubbles.net:80/index.php?content=features#top')
                ->asString(),
            equals('http://mikey:secret@stubbles.net:80/index.php?content=features#top')
        );
    }

    #[Test]
    public function asStringWithoutPortReturnsOriginalGivenUriWithUsernameAndPasswordWithoutPort(): void
    {
        assertThat(
            Uri::fromString('http://mikey:secret@stubbles.net:80/index.php?content=features#top')
                ->asStringWithoutPort(),
            equals('http://mikey:secret@stubbles.net/index.php?content=features#top')
        );
    }

    #[Test]
    public function asStringReturnsOriginalGivenUriWithUsername(): void
    {
        assertThat(
            Uri::fromString('http://mikey@stubbles.net:80/index.php?content=features#top')
                ->asString(),
            equals('http://mikey@stubbles.net:80/index.php?content=features#top')
        );
    }

    #[Test]
    public function asStringWithoutPortReturnsOriginalGivenUriWithUsernameWithoutPort(): void
    {
        assertThat(
            Uri::fromString('http://mikey@stubbles.net:80/index.php?content=features#top')
                ->asStringWithoutPort(),
            equals('http://mikey@stubbles.net/index.php?content=features#top')
        );
    }

    #[Test]
    public function asStringReturnsOriginalGivenUriWithUsernameAndEmptyPassword(): void
    {
        assertThat(
            Uri::fromString('http://mikey:@stubbles.net:80/index.php?content=features#top')
                ->asString(),
            equals('http://mikey:@stubbles.net:80/index.php?content=features#top')
        );
    }

    #[Test]
    public function asStringWithoutPortReturnsOriginalGivenUriWithUsernameAndEmptyPasswordWithoutPort(): void
    {
        assertThat(
            Uri::fromString('http://mikey:@stubbles.net:80/index.php?content=features#top')
                ->asStringWithoutPort(),
            equals('http://mikey:@stubbles.net/index.php?content=features#top')
        );
    }

    #[Test]
    public function asStringReturnsOriginalGivenUriWithIpv4Address(): void
    {
        assertThat(
            Uri::fromString('http://127.0.0.1:80/index.php?content=features#top')
                ->asString(),
            equals('http://127.0.0.1:80/index.php?content=features#top')
        );
    }

    #[Test]
    public function asStringWithoutPortReturnsOriginalGivenUriButWithoutPortWithIpv4Address(): void
    {
        assertThat(
            Uri::fromString('http://127.0.0.1:80/index.php?content=features#top')
                ->asStringWithoutPort(),
            equals('http://127.0.0.1/index.php?content=features#top')
        );
    }

    #[Test]
    public function asStringWithNonDefaultPortReturnsOriginalGivenUriWithIpv4Address(): void
    {
        assertThat(
                Uri::fromString('http://127.0.0.1:80/index.php?content=features#top')
                        ->asStringWithNonDefaultPort(),
                equals('http://127.0.0.1:80/index.php?content=features#top')
        );
    }

    #[Test]
    #[Group('bug258')]
    public function asStringReturnsOriginalGivenUriWithIpv6AddressShortNotation(): void
    {
        assertThat(
            Uri::fromString('http://[2001:db8:12:34::1]:80/index.php?content=features#top')
                ->asString(),
            equals('http://[2001:db8:12:34::1]:80/index.php?content=features#top')
        );
    }

    #[Test]
    #[Group('bug258')]
    public function asStringWithoutPortReturnsOriginalGivenUriButWithoutPortWithIpv6AddressShortNotation(): void
    {
        assertThat(
            Uri::fromString('http://[2001:db8:12:34::1]:80/index.php?content=features#top')
                ->asStringWithoutPort(),
            equals('http://[2001:db8:12:34::1]/index.php?content=features#top')
        );
    }

    #[Test]
    #[Group('bug258')]
    public function asStringWithNonDefaultPortReturnsOriginalGivenUriWithIpv6AddressShortNotation(): void
    {
        assertThat(
            Uri::fromString('http://[2001:db8:12:34::1]:80/index.php?content=features#top')
                ->asStringWithNonDefaultPort(),
            equals('http://[2001:db8:12:34::1]:80/index.php?content=features#top')
        );
    }

    #[Test]
    #[Group('bug258')]
    public function asStringReturnsOriginalGivenUriWithIpv6AddressLongNotation(): void
    {
        assertThat(
            Uri::fromString('http://[2001:8d8f:1fe:5:abba:dbff:fefe:7755]:80/index.php?content=features#top')
                ->asString(),
            equals('http://[2001:8d8f:1fe:5:abba:dbff:fefe:7755]:80/index.php?content=features#top')
        );
    }

    #[Test]
    #[Group('bug258')]
    public function asStringWithoutPortReturnsOriginalGivenUriButWithoutPortWithIpv6AddressLongNotation(): void
    {
        assertThat(
            Uri::fromString('http://[2001:8d8f:1fe:5:abba:dbff:fefe:7755]:80/index.php?content=features#top')
                ->asStringWithoutPort(),
            equals('http://[2001:8d8f:1fe:5:abba:dbff:fefe:7755]/index.php?content=features#top')
        );
    }

    #[Test]
    #[Group('bug258')]
    public function asStringWithNonDefaultPortReturnsOriginalGivenUriWithIpv6AddressLongNotation(): void
    {
        assertThat(
            Uri::fromString('http://[2001:8d8f:1fe:5:abba:dbff:fefe:7755]:80/index.php?content=features#top')
                ->asStringWithNonDefaultPort(),
            equals('http://[2001:8d8f:1fe:5:abba:dbff:fefe:7755]:80/index.php?content=features#top')
        );
    }

    #[Test]
    public function wrongParams(): void
    {
        expect(function() {
            Uri::fromString('http://example.org/')
                ->addParam('test', new \stdClass());
        })->throws(\InvalidArgumentException::class);
    }

    #[Test]
    public function paramWithoutValue(): void
    {
        assertThat(
            Uri::fromString('http://example.org/?wsdl')->asStringWithoutPort(),
            equals('http://example.org/?wsdl')
        );
    }

    #[Test]
    public function hasParamReturnsTrueIfParamPresent(): void
    {
        assertTrue(
            Uri::fromString('http://example.org/?wsdl')->hasParam('wsdl')
        );
    }

    #[Test]
    public function hasParamReturnsFalseIfParamNotPresent(): void
    {
        assertFalse(
            Uri::fromString('http://example.org/?wsdl')->hasParam('doesNotExist')
        );
    }

    #[Test]
    public function getParamReturnsNullIfParamNotSet(): void
    {
        assertNull(
            Uri::fromString('http://example.org/?foo=bar')->param('bar')
        );
    }

    #[Test]
    public function getParamReturnsDefaultValueIfParamNotSet(): void
    {
        assertThat(
            Uri::fromString('http://example.org/?foo=bar')->param('bar', 'baz'),
            equals('baz')
        );
    }

    #[Test]
    public function getParamReturnsValueIfParamSet(): void
    {
        assertThat(
            Uri::fromString('http://example.org/?foo=bar')->param('foo'),
            equals('bar')
        );
    }

    #[Test]
    public function removeNonExistingParamChangesNothing(): void
    {
        assertThat(
            Uri::fromString('http://example.org/?wsdl')
                ->removeParam('doesNotExist')
                ->asStringWithoutPort(),
            equals('http://example.org/?wsdl')
        );
    }

    #[Test]
    public function removeExistingParamChangesQueryString(): void
    {
        assertThat(
            Uri::fromString('http://example.org/?wsdl&foo=bar')
                ->removeParam('foo')
                ->asStringWithoutPort(),
            equals('http://example.org/?wsdl')
        );
    }

    /**
     * @since  5.1.2
     */
    #[Test]
    public function addParamsChangesQueryString(): void
    {
        assertThat(
            Uri::fromString('http://example.org/?wsdl')
                ->addParams(['foo' => 'bar', 'baz' => '303'])
                ->asStringWithoutPort(),
            equals('http://example.org/?wsdl&foo=bar&baz=303')
        );
    }

    #[Test]
    public function addParamChangesQueryString(): void
    {
        assertThat(
            Uri::fromString('http://example.org/?wsdl')
                ->addParam('foo', 'bar')
                ->asStringWithoutPort(),
            equals('http://example.org/?wsdl&foo=bar')
        );
    }

    #[Test]
    public function fragmentIsNullIfNotInUri(): void
    {
        assertNull(Uri::fromString('http://example.org/?wsdl')->fragment());
    }

    #[Test]
    public function fragmentFromUriIsReturned(): void
    {
        assertThat(
            Uri::fromString('http://example.org/?wsdl#top')->fragment(),
            equals('top')
        );
    }

    #[Test]
    public function parsedUriReturnsNullIfNoSchemeInUri(): void
    {
        expect(fn() => new ParsedUri('://example.org/?wsdl#top'))
            ->throws(MalformedUri::class);
    }

    /**
     * @since  4.0.0
     */
    #[Test]
    public function schemeEqualsOnlyOriginalScheme(): void
    {
        $parsedUri = new ParsedUri('foo://example.org/?wsdl#top');
        assertFalse($parsedUri->schemeEquals('bar'));
        assertTrue($parsedUri->schemeEquals('foo'));
    }

    /**
     * @since  4.0.0
     */
    #[Test]
    public function emptyPortEqualsNull(): void
    {
        $parsedUri = new ParsedUri('foo://example.org/?wsdl#top');
        assertTrue($parsedUri->portEquals(null));
    }

    /**
     * @since  4.0.0
     */
    #[Test]
    public function portEqualsOnlyOriginalPort(): void
    {
        $parsedUri = new ParsedUri('foo://example.org:77/?wsdl#top');
        assertTrue($parsedUri->portEquals(77));
        assertFalse($parsedUri->portEquals(80));
    }

    /**
     * @since  2.1.2
     */
    #[Test]
    public function hasNoQueryStringIfNoneGiven(): void
    {
        assertFalse(
            Uri::fromString('http://example.org/foo')->hasQueryString()
        );
    }

    /**
     * @since  2.1.2
     */
    #[Test]
    public function hasQueryStringIfGiven(): void
    {
        assertTrue(
            Uri::fromString('http://example.org/?foo=bar&baz=true')->hasQueryString()
        );
    }

    /**
     * @since  2.1.2
     */
    #[Test]
    public function hasQueryStringIfParamAdded(): void
    {
        assertTrue(
            Uri::fromString('http://example.org/')
                ->addParam('foo', 'bar')
                ->hasQueryString()
        );
    }

    /**
     * @since  2.1.2
     */
    #[Test]
    public function queryStringIsEmptyIfNoneGiven(): void
    {
        assertEmptyString(
            Uri::fromString('http://example.org/foo')->queryString()
        );
    }

    /**
     * @since  2.1.2
     */
    #[Test]
    public function queryStringEqualsGivenQueryString(): void
    {
        assertThat(
            Uri::fromString('http://example.org/?foo=bar&baz=true')
                ->queryString(),
            equals('foo=bar&baz=true')
        );
    }

    /**
     * @since  2.1.2
     */
    #[Test]
    public function queryStringEqualsAddedParameters(): void
    {
        assertThat(
            Uri::fromString('http://example.org/')
                ->addParam('foo', 'bar')
                ->queryString(),
            equals('foo=bar')
        );
    }

    /**
     * @since  5.0.1
     */
    #[Test]
    #[Group('issue_119')]
    public function illegalArgumentExceptionFromUnbalancedQueryStringTurnedIntoMalformedUri(): void
    {
        expect(function() {
            Uri::fromString('http://example.org/?foo[bar=300&baz=200');
        })->throws(MalformedUri::class);
    }

    /**
     * @since  5.5.0
     */
    #[Test]
    public function withPathExchangesPathCompletely(): void
    {
        assertThat(
            Uri::fromString('http://example.org/foo')->withPath('/bar'),
            equals('http://example.org/bar')
        );
    }

    /**
     * @since  5.5.0
     */
    #[Test]
    public function withPathReturnsNewInstance(): void
    {
        $uri = Uri::fromString('http://example.org/foo');
        assertThat($uri->withPath('/bar'), isNotSameAs($uri));
    }
}
