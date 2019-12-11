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
use stubbles\peer\HeaderList;
use stubbles\peer\Stream;
use stubbles\peer\http\HttpUri;

use function bovigo\assert\assertThat;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
/**
 * Test for stubbles\peer\http\HttpRequest.
 *
 * @group  peer
 * @group  peer_http
 */
class HttpRequestTest extends TestCase
{
    /**
     * memory to write http request to
     *
     * @var  string
     */
    private $memory;

    protected function setUp(): void
    {
        $this->memory = '';
    }

    /**
     * creates instance to test
     *
     * @param   string  $queryString
     * @return  HttpRequest
     */
    private function createHttpRequest(string $queryString = null): HttpRequest
    {
        $socket   = NewInstance::stub(Stream::class)->returns([
                'write' => function(string $line) { $this->memory .= $line; return strlen($line); }
        ]);

        $uriCalls = [
            'openSocket' => $socket,
            'path'       => '/foo/resource',
            'hostname'   => 'example.com'
        ];
        if (null !== $queryString) {
            $uriCalls['hasQueryString'] = true;
            $uriCalls['queryString'] = $queryString;
        } else {
            $uriCalls['hasQueryString'] = false;
        }

        return HttpRequest::create(
                NewInstance::stub(HttpUri::class)->returns($uriCalls),
                new HeaderList(['X-Binford' => 6100])
        );
    }

    /**
     * @test
     */
    public function getWritesCorrectRequest(): void
    {
        $this->createHttpRequest()->get();
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'GET /foo/resource HTTP/1.1',
                        'Host: example.com',
                        'X-Binford: 6100',
                        ''
                ))
        );
    }

    /**
     * @since   2.1.2
     * @test
     */
    public function getWritesCorrectRequestWithQueryString(): void
    {
        $this->createHttpRequest('foo=bar&baz=1')->get();
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'GET /foo/resource?foo=bar&baz=1 HTTP/1.1',
                        'Host: example.com',
                        'X-Binford: 6100',
                        ''
                ))
        );
    }

    /**
     * @test
     */
    public function getWritesCorrectRequestWithVersion(): void
    {
        $this->createHttpRequest()->get(5, HttpVersion::HTTP_1_0);
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'GET /foo/resource HTTP/1.0',
                        'Host: example.com',
                        'X-Binford: 6100',
                        ''
                ))
        );
    }

    /**
     * @since   8.0.0
     * @return  array<mixed[]>
     */
    public function invalidHttpVersions(): array
    {
        return [['invalid'], [new HttpVersion(10, 9)]];
    }

    /**
     * @param  mixed  $httpVersion
     * @test
     * @dataProvider  invalidHttpVersions
     */
    public function getWithInvalidHttpVersionThrowsIllegalArgumentException($httpVersion): void
    {
        expect(function() use ($httpVersion) {
                $this->createHttpRequest()->get(5, $httpVersion);
        })->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function headWritesCorrectRequest(): void
    {
        $this->createHttpRequest()->head();
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'HEAD /foo/resource HTTP/1.1',
                        'Host: example.com',
                        'X-Binford: 6100',
                        'Connection: close',
                        ''
                ))
        );
    }

    /**
     * @since   2.1.2
     * @test
     */
    public function headWritesCorrectRequestWithQueryString(): void
    {
        $this->createHttpRequest('foo=bar&baz=1')->head();
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'HEAD /foo/resource?foo=bar&baz=1 HTTP/1.1',
                        'Host: example.com',
                        'X-Binford: 6100',
                        'Connection: close',
                        ''
                ))
        );
    }

    /**
     * @test
     */
    public function headWritesCorrectRequestWithVersion(): void
    {
        $this->createHttpRequest()->head(5, HttpVersion::HTTP_1_0);
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'HEAD /foo/resource HTTP/1.0',
                        'Host: example.com',
                        'X-Binford: 6100',
                        'Connection: close',
                        ''
                ))
        );
    }

    /**
     * @param  mixed  $httpVersion
     * @test
     * @dataProvider  invalidHttpVersions
     */
    public function headWithInvalidHttpVersionThrowsIllegalArgumentException($httpVersion): void
    {
        expect(function() use ($httpVersion) {
                $this->createHttpRequest()->head(5, $httpVersion);
        })->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function postWritesCorrectRequest(): void
    {
        $this->createHttpRequest()->post('foobar');
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'POST /foo/resource HTTP/1.1',
                        'Host: example.com',
                        'X-Binford: 6100',
                        'Content-Length: 6',
                        '',
                        'foobar'
                ))
        );
    }

    /**
     * @since   2.1.2
     * @test
     */
    public function postIgnoresQueryString(): void
    {
        $this->createHttpRequest('foo=bar&baz=1')->post('foobar');
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'POST /foo/resource HTTP/1.1',
                        'Host: example.com',
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
    public function postWritesCorrectRequestWithVersion(): void
    {
        $this->createHttpRequest()->post('foobar', 5, HttpVersion::HTTP_1_0);
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'POST /foo/resource HTTP/1.0',
                        'Host: example.com',
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
    public function postWritesCorrectRequestUsingEmptyPostValues(): void
    {
        $this->createHttpRequest()->post([]);
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'POST /foo/resource HTTP/1.1',
                        'Host: example.com',
                        'X-Binford: 6100',
                        'Content-Type: application/x-www-form-urlencoded',
                        'Content-Length: 0',
                        ''
                ))
        );
    }

    /**
     * @test
     */
    public function postWritesCorrectRequestUsingPostValues(): void
    {
        $this->createHttpRequest()->post(['foo' => 'bar', 'ba z' => 'dum my']);
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'POST /foo/resource HTTP/1.1',
                        'Host: example.com',
                        'X-Binford: 6100',
                        'Content-Type: application/x-www-form-urlencoded',
                        'Content-Length: 20',
                        '',
                        'foo=bar&ba+z=dum+my&'
                ))
        );
    }

    /**
     * @test
     */
    public function postWritesCorrectRequestUsingPostValuesWithVersion(): void
    {
        $this->createHttpRequest()->post(
                ['foo' => 'bar', 'ba z' => 'dum my'],
                5,
                HttpVersion::HTTP_1_0
        );
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'POST /foo/resource HTTP/1.0',
                        'Host: example.com',
                        'X-Binford: 6100',
                        'Content-Type: application/x-www-form-urlencoded',
                        'Content-Length: 20',
                        '',
                        'foo=bar&ba+z=dum+my&'
                ))
        );
    }

    /**
     * @param  mixed  $httpVersion
     * @test
     * @dataProvider  invalidHttpVersions
     */
    public function postWithInvalidHttpVersionThrowsIllegalArgumentException($httpVersion): void
    {
        expect(function() use ($httpVersion) {
                $this->createHttpRequest()->post('foobar', 5, $httpVersion);
        })->throws(\InvalidArgumentException::class);
    }

    /**
     * @since   2.0.0
     * @test
     */
    public function putWritesCorrectRequest(): void
    {
        $this->createHttpRequest()->put('foobar');
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'PUT /foo/resource HTTP/1.1',
                        'Host: example.com',
                        'X-Binford: 6100',
                        'Content-Length: 6',
                        '',
                        'foobar'
                ))
        );
    }

    /**
     * @since   2.1.2
     * @test
     */
    public function putIgnoresQueryString(): void
    {
        $this->createHttpRequest('foo=bar&baz=1')->put('foobar');
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'PUT /foo/resource HTTP/1.1',
                        'Host: example.com',
                        'X-Binford: 6100',
                        'Content-Length: 6',
                        '',
                        'foobar'
                ))
        );
    }

    /**
     * @since   2.0.0
     * @test
     */
    public function putWritesCorrectRequestWithVersion(): void
    {
        $this->createHttpRequest()->put('foobar', 5, HttpVersion::HTTP_1_0);
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'PUT /foo/resource HTTP/1.0',
                        'Host: example.com',
                        'X-Binford: 6100',
                        'Content-Length: 6',
                        '',
                        'foobar'
                ))
        );
    }

    /**
     * @since   2.0.0
     * @test
     */
    public function putWithInvalidHttpVersionThrowsIllegalArgumentException(): void
    {
        expect(function() {
                $this->createHttpRequest()->put('foobar', 5, 'invalid');
        })->throws(\InvalidArgumentException::class);
    }

    /**
     * @since   2.0.0
     * @test
     */
    public function deleteWritesCorrectRequest(): void
    {
        $this->createHttpRequest()->delete();
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'DELETE /foo/resource HTTP/1.1',
                        'Host: example.com',
                        'X-Binford: 6100',
                        ''
                ))
        );
    }

    /**
     * @since   2.1.2
     * @test
     */
    public function deleteIgnoresQueryString(): void
    {
        $this->createHttpRequest('foo=bar&baz=1')->delete();
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'DELETE /foo/resource HTTP/1.1',
                        'Host: example.com',
                        'X-Binford: 6100',
                        ''
                ))
        );
    }

    /**
     * @since   2.0.0
     * @test
     */
    public function deleteWritesCorrectRequestWithVersion(): void
    {
        $this->createHttpRequest()->delete(5, HttpVersion::HTTP_1_0);
        assertThat(
                $this->memory,
                equals(Http::lines(
                        'DELETE /foo/resource HTTP/1.0',
                        'Host: example.com',
                        'X-Binford: 6100',
                        ''
                ))
        );
    }

    /**
     * @param  mixed  $httpVersion
     * @since  2.0.0
     * @test
     * @dataProvider  invalidHttpVersions
     */
    public function deleteWithInvalidHttpVersionThrowsIllegalArgumentException($httpVersion): void
    {
        expect(function() use ($httpVersion) {
                $this->createHttpRequest()->delete(5, $httpVersion);
        })->throws(\InvalidArgumentException::class);
    }
}
