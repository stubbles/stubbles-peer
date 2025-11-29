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
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stubbles\peer\HeaderList;
use stubbles\peer\Stream;
use stubbles\peer\http\HttpUri;

use function bovigo\assert\assertThat;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
/**
 * Test for stubbles\peer\http\HttpRequest.
 */
#[Group('peer')]
#[Group('peer_http')]
class HttpRequestTest extends TestCase
{
    private string $memory;

    protected function setUp(): void
    {
        $this->memory = '';
    }

    private function createHttpRequest(?string $queryString = null): HttpRequest
    {
        $socket = NewInstance::stub(Stream::class)->returns([
                'write' => function(string $line): int { $this->memory .= $line; return strlen($line); }
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

    #[Test]
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
     */
    #[Test]
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

    #[Test]
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
     */
    public static function invalidHttpVersions(): Generator
    {
        yield ['invalid'];
        yield [new HttpVersion(10, 9)];
    }


    #[Test]
    #[DataProvider('invalidHttpVersions')]
    public function getWithInvalidHttpVersionThrowsIllegalArgumentException(string|HttpVersion $httpVersion): void
    {
        expect(function() use ($httpVersion) {
                $this->createHttpRequest()->get(5, $httpVersion);
        })->throws(InvalidArgumentException::class);
    }

    #[Test]
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
     */
    #[Test]
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

    #[Test]
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

    #[Test]
    #[DataProvider('invalidHttpVersions')]
    public function headWithInvalidHttpVersionThrowsIllegalArgumentException(string|HttpVersion $httpVersion): void
    {
        expect(function() use ($httpVersion) {
                $this->createHttpRequest()->head(5, $httpVersion);
        })->throws(InvalidArgumentException::class);
    }

    #[Test]
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
     */
    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    #[DataProvider('invalidHttpVersions')]
    public function postWithInvalidHttpVersionThrowsIllegalArgumentException(string|HttpVersion $httpVersion): void
    {
        expect(function() use ($httpVersion) {
                $this->createHttpRequest()->post('foobar', 5, $httpVersion);
        })->throws(InvalidArgumentException::class);
    }

    /**
     * @since   2.0.0
     */
    #[Test]
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
     */
    #[Test]
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
     */
    #[Test]
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
     */
    #[Test]
    public function putWithInvalidHttpVersionThrowsIllegalArgumentException(): void
    {
        expect(function() {
                $this->createHttpRequest()->put('foobar', 5, 'invalid');
        })->throws(InvalidArgumentException::class);
    }

    /**
     * @since   2.0.0
     */
    #[Test]
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
     */
    #[Test]
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
     */
    #[Test]
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

    #[Test]
    #[DataProvider('invalidHttpVersions')]
    public function deleteWithInvalidHttpVersionThrowsIllegalArgumentException(string|HttpVersion $httpVersion): void
    {
        expect(function() use ($httpVersion) {
                $this->createHttpRequest()->delete(5, $httpVersion);
        })->throws(InvalidArgumentException::class);
    }
}
