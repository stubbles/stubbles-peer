<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer\http;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stubbles\peer\ProtocolViolation;
use stubbles\peer\Stream;

use function bovigo\assert\{
    assertThat,
    assertEmpty,
    assertNull,
    expect,
    fail,
    predicate\equals
};
/**
 * Test for stubbles\peer\http\HttpResponse.
 */
#[Group('peer')]
#[Group('peer_http')]
class HttpResponseTest extends TestCase
{
    private function createResponse(string $response): HttpResponse
    {
        $file = vfsStream::newFile('response')
                ->withContent($response)
                ->at(vfsStream::setup());
        $fp = fopen($file->url(), 'rb+');
        if (false === $fp) {
            throw new \RuntimeException('Could not open vfsStream file.');
        }

        return HttpResponse::create(new Stream($fp));
    }

    #[Test]
    public function chunkedResponseCanBeRead(): void
    {
        $httpResponse = $this->createResponse(Http::lines(
                'HTTP/1.1 200 OK',
                'Host: localhost',
                'Transfer-Encoding: chunked',
                '',
                dechex(3) . " ext\r\n",
                "foo\r\n",
                dechex(3) . "\r\n",
                "bar\r\n",
                dechex(0)
        ));
        assertThat($httpResponse->body(), equals('foobar'));
        $headerList = $httpResponse->headers();
        assertThat($headerList->get('Host'), equals('localhost'));
        assertThat($headerList->get('Content-Length'), equals(6));

    }

    #[Test]
    public function nonChunkedResponseWithoutContentLengthHeaderCanBeRead(): void
    {
        $httpResponse = $this->createResponse(Http::lines(
                'HTTP/1.1 200 OK',
                'Host: localhost',
                '',
                'foobar'
        ));
        $headerList = $httpResponse->headers();
        assertThat($headerList->get('Host'), equals('localhost'));
        assertThat($httpResponse->body(), equals('foobar'));
    }

    #[Test]
    public function nonChunkedResponseWithContentLengthHeaderCanBeRead(): void
    {
        $httpResponse = $this->createResponse(Http::lines(
                'HTTP/1.1 200 OK',
                'Host: localhost',
                'Content-Length: 6',
                '',
                'foobar'
        ));
        $headerList = $httpResponse->headers();
        assertThat($headerList->get('Host'), equals('localhost'));
        assertThat($headerList->get('Content-Length'), equals(6));
        assertThat($httpResponse->body(), equals('foobar'));
    }

    #[Test]
    public function canReadResponseTwice(): void
    {
        $httpResponse = $this->createResponse(Http::lines(
                'HTTP/1.1 200 OK',
                'Host: localhost',
                'Content-Length: 6',
                '',
                'foobar'
        ));
        assertThat($httpResponse->body(), equals('foobar'));
        assertThat($httpResponse->body(), equals('foobar'));
    }

    #[Test]
    public function continuesOnStatusCode100(): void
    {
        $httpResponse = $this->createResponse(
                Http::line('HTTP/1.0 100 Continue')
                . Http::line('Host: localhost')
                . Http::emptyLine()
                . Http::line('HTTP/1.0 100 Continue')
                . Http::emptyLine()
                . Http::line('HTTP/1.0 200 OK')
                . Http::emptyLine()
                . 'foobar'
        );
        $headerList = $httpResponse->headers();
        assertThat($headerList->get('Host'), equals('localhost'));
        assertThat($httpResponse->statusLine(), equals('HTTP/1.0 200 OK'));
        assertThat($httpResponse->httpVersion(), equals(new HttpVersion(1, 0)));
        assertThat($httpResponse->statusCode(), equals(200));
        assertThat($httpResponse->reasonPhrase(), equals('OK'));
        assertThat($httpResponse->statusCodeClass(), equals(Http::STATUS_CLASS_SUCCESS));
        assertThat($httpResponse->body(), equals('foobar'));
    }

    #[Test]
    public function continuesOnStatusCode102(): void
    {
        $httpResponse = $this->createResponse(
                Http::line('HTTP/1.0 102 Processing')
                . Http::line('Host: localhost')
                . Http::emptyLine()
                . Http::line('HTTP/1.0 102 Processing')
                . Http::emptyLine()
                . Http::line('HTTP/1.1 404 Not Found')
                . Http::emptyLine()
                . 'foobar'
        );
        $headerList = $httpResponse->headers();
        assertThat($headerList->get('Host'), equals('localhost'));
        assertThat($httpResponse->statusLine(), equals('HTTP/1.1 404 Not Found'));
        assertThat($httpResponse->httpVersion(), equals(new HttpVersion(1, 1)));
        assertThat($httpResponse->statusCode(), equals(404));
        assertThat($httpResponse->reasonPhrase(), equals('Not Found'));
        assertThat($httpResponse->statusCodeClass(), equals(Http::STATUS_CLASS_ERROR_CLIENT));
        assertThat($httpResponse->body(), equals('foobar'));
    }

    /**
     * @since  8.0.0
     * @return  array<string[]>
     */
    public static function responseInstanceMethods(): array
    {
        return [
                ['statusLine'],
                ['httpVersion'],
                ['statusCode'],
                ['reasonPhrase'],
                ['statusCodeClass'],
                ['headers'],
                ['body']
        ];
    }

    #[Test]
    #[DataProvider('responseInstanceMethods')]
    public function illegalStatusLineLeadsToProtocolViolation(string $method): void
    {
        $httpResponse = $this->createResponse(Http::lines(
                "Illegal Response containing \36 dangerous \0 characters",
                'Host: localhost',
                ''
        ));
        expect(function() use ($httpResponse, $method) { $httpResponse->$method(); } )
                ->throws(ProtocolViolation::class)
                ->withMessage(
                        'Received status line "Illegal Response containing \036'
                        . ' dangerous \000 characters" does not match expected'
                        . ' format "=^(HTTP/\d+\.\d+) (\d{3}) ([^\r]*)="'
                );
    }

    /**
     * @since  4.0.0
     */
    #[Test]
    #[DataProvider('responseInstanceMethods')]
    public function statusLineWithInvalidHttpVersionLeadsToProtocolViolation(string $method): void
    {
        $httpResponse = $this->createResponse(Http::lines(
                'HTTP/400 102 Processing',
                'Host: localhost',
                ''
        ));
        expect(function() use ($httpResponse, $method) { $httpResponse->$method(); } )
                ->throws(ProtocolViolation::class)
                ->withMessage(
                        'Received status line "HTTP/400 102 Processing" does not match'
                        . ' expected format "=^(HTTP/\d+\.\d+) (\d{3}) ([^\r]*)="'
                );
    }
}
