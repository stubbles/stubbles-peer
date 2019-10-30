<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer\http;
use bovigo\callmap\NewInstance;
use PHPUnit\Framework\TestCase;
use stubbles\peer\Stream;
use stubbles\peer\http\HttpConnection;
use stubbles\peer\http\HttpUri;
use stubbles\peer\http\HttpResponse;

use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isInstanceOf;
/**
 * Test for stubbles\peer\http\HttpConnection.
 *
 * @group  peer
 * @group  peer_http
 */
class HttpConnectionTest extends TestCase
{
    /**
     * instance to test
     *
     * @type  \stubbles\peer\http\HttpConnection
     */
    private $httpConnection;
    /**
     * @type  string
     */
    private $memory;

    protected function setUp(): void
    {
        $this->memory = '';
        $socket       = NewInstance::stub(Stream::class)->returns([
                'write' => function(string $line) { $this->memory .= $line; return strlen($line); }
        ]);
        $httpUri      = NewInstance::stub(HttpUri::class)->returns([
                'openSocket'     => $socket,
                'path'           => '/foo/resource',
                'hostname'       => 'example.com',
                'hasQueryString' => true,
                'queryString'    => 'foo=bar'

        ]);
        $this->httpConnection = new HttpConnection($httpUri);
    }

    /**
     * @test
     */
    public function getReturnsHttpResponse()
    {
        assertThat(
                $this->httpConnection->timeout(2)
                        ->asUserAgent('Stubbles HTTP Client')
                        ->referedFrom('http://example.com/')
                        ->withCookie(['foo' => 'bar baz'])
                        ->authorizedAs('user', 'pass')
                        ->usingHeader('X-Binford', 6100)
                        ->get(),
                isInstanceOf(HttpResponse::class)
        );
    }

    /**
     * @test
     */
    public function getWritesProperRequestLines()
    {
        $this->httpConnection->timeout(2)
                ->asUserAgent('Stubbles HTTP Client')
                ->referedFrom('http://example.com/')
                ->withCookie(['foo' => 'bar baz'])
                ->authorizedAs('user', 'pass')
                ->usingHeader('X-Binford', 6100)
                ->get();
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'GET /foo/resource?foo=bar HTTP/1.1',
                        'Host: example.com',
                        'User-Agent: Stubbles HTTP Client',
                        'Referer: http://example.com/',
                        'Cookie: foo=bar+baz;',
                        'Authorization: BASIC ' . base64_encode('user:pass'),
                        'X-Binford: 6100',
                        ''
                ))
        );
    }

    /**
     * @test
     */
    public function headReturnsHttpResponse()
    {
        assertThat(
                $this->httpConnection->timeout(2)
                            ->asUserAgent('Stubbles HTTP Client')
                            ->referedFrom('http://example.com/')
                            ->withCookie(['foo' => 'bar baz'])
                            ->authorizedAs('user', 'pass')
                            ->usingHeader('X-Binford', 6100)
                            ->head(),
                isInstanceOf(HttpResponse::class)
        );
    }

    /**
     * @test
     */
    public function headWritesProperRequestLines()
    {
        $this->httpConnection->timeout(2)
                    ->asUserAgent('Stubbles HTTP Client')
                    ->referedFrom('http://example.com/')
                    ->withCookie(['foo' => 'bar baz'])
                    ->authorizedAs('user', 'pass')
                    ->usingHeader('X-Binford', 6100)
                    ->head();
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'HEAD /foo/resource?foo=bar HTTP/1.1',
                        'Host: example.com',
                        'User-Agent: Stubbles HTTP Client',
                        'Referer: http://example.com/',
                        'Cookie: foo=bar+baz;',
                        'Authorization: BASIC ' . base64_encode('user:pass'),
                        'X-Binford: 6100',
                        'Connection: close',
                        ''
                ))
        );
    }

    /**
     * @test
     */
    public function postReturnsHttpResponse()
    {
        assertThat(
                $this->httpConnection->timeout(2)
                        ->asUserAgent('Stubbles HTTP Client')
                        ->referedFrom('http://example.com/')
                        ->withCookie(['foo' => 'bar baz'])
                        ->authorizedAs('user', 'pass')
                        ->usingHeader('X-Binford', 6100)
                        ->post('foobar'),
                isInstanceOf(HttpResponse::class)
        );
    }

    /**
     * @test
     */
    public function postWritesProperHttpRequestLinesWithRequestBody()
    {
        $this->httpConnection->timeout(2)
                ->asUserAgent('Stubbles HTTP Client')
                ->referedFrom('http://example.com/')
                ->withCookie(['foo' => 'bar baz'])
                ->authorizedAs('user', 'pass')
                ->usingHeader('X-Binford', 6100)
                ->post('foobar');
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'POST /foo/resource HTTP/1.1',
                        'Host: example.com',
                        'User-Agent: Stubbles HTTP Client',
                        'Referer: http://example.com/',
                        'Cookie: foo=bar+baz;',
                        'Authorization: BASIC ' . base64_encode('user:pass'),
                        'X-Binford: 6100',
                        'Content-Length: 6',
                        '',
                        'foobar'
                ))
        );
    }

    /**
     * @test
     */
    public function postWritesProperHttpRequestLinesWithRequestValues()
    {
        $this->httpConnection->timeout(2)
                ->asUserAgent('Stubbles HTTP Client')
                ->referedFrom('http://example.com/')
                ->withCookie(['foo' => 'bar baz'])
                ->authorizedAs('user', 'pass')
                ->usingHeader('X-Binford', 6100)
                ->post(['foo' => 'bar', 'ba z' => 'dum my']);
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'POST /foo/resource HTTP/1.1',
                        'Host: example.com',
                        'User-Agent: Stubbles HTTP Client',
                        'Referer: http://example.com/',
                        'Cookie: foo=bar+baz;',
                        'Authorization: BASIC ' . base64_encode('user:pass'),
                        'X-Binford: 6100',
                        'Content-Type: application/x-www-form-urlencoded',
                        'Content-Length: 20',
                        '',
                        'foo=bar&ba+z=dum+my&'
                ))
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function putReturnsHttpResponse()
    {
        assertThat(
                $this->httpConnection->timeout(2)
                        ->asUserAgent('Stubbles HTTP Client')
                        ->referedFrom('http://example.com/')
                        ->withCookie(['foo' => 'bar baz'])
                        ->authorizedAs('user', 'pass')
                        ->usingHeader('X-Binford', 6100)
                        ->put('foobar'),
                isInstanceOf(HttpResponse::class)
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function putWritesProperHttpRequestLines()
    {
        $this->httpConnection->timeout(2)
                ->asUserAgent('Stubbles HTTP Client')
                ->referedFrom('http://example.com/')
                ->withCookie(['foo' => 'bar baz'])
                ->authorizedAs('user', 'pass')
                ->usingHeader('X-Binford', 6100)
                ->put('foobar');
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'PUT /foo/resource HTTP/1.1',
                        'Host: example.com',
                        'User-Agent: Stubbles HTTP Client',
                        'Referer: http://example.com/',
                        'Cookie: foo=bar+baz;',
                        'Authorization: BASIC ' . base64_encode('user:pass'),
                        'X-Binford: 6100',
                        'Content-Length: 6',
                        '',
                        'foobar'
                ))
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function deleteReturnsHttpResponse()
    {
        assertThat(
                $this->httpConnection->timeout(2)
                        ->asUserAgent('Stubbles HTTP Client')
                        ->referedFrom('http://example.com/')
                        ->withCookie(['foo' => 'bar baz'])
                        ->authorizedAs('user', 'pass')
                        ->usingHeader('X-Binford', 6100)
                        ->delete(),
                isInstanceOf(HttpResponse::class)
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function deleteWritesProperHttpRequestLines()
    {
        $this->httpConnection->timeout(2)
                ->asUserAgent('Stubbles HTTP Client')
                ->referedFrom('http://example.com/')
                ->withCookie(['foo' => 'bar baz'])
                ->authorizedAs('user', 'pass')
                ->usingHeader('X-Binford', 6100)
                ->delete();
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'DELETE /foo/resource HTTP/1.1',
                        'Host: example.com',
                        'User-Agent: Stubbles HTTP Client',
                        'Referer: http://example.com/',
                        'Cookie: foo=bar+baz;',
                        'Authorization: BASIC ' . base64_encode('user:pass'),
                        'X-Binford: 6100',
                        ''
                ))
        );
    }

    /**
     * @since  3.1.0
     * @test
     */
    public function functionShortcut()
    {
        assertThat(
                \stubbles\peer\http('http://example.net/'),
                isInstanceOf(HttpConnection::class)
        );
    }
}
