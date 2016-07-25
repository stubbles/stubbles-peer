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
use org\bovigo\vfs\vfsStream;
use stubbles\peer\ProtocolViolation;
use stubbles\peer\Stream;

use function bovigo\assert\{
    assert,
    assertEmpty,
    assertNull,
    expect,
    predicate\equals
};
/**
 * Test for stubbles\peer\http\HttpResponse.
 *
 * @group  peer
 * @group  peer_http
 */
class HttpResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * creates instance to test
     *
     * @param   string  $response  content of response
     * @return  HttpResponse
     */
    private function createResponse(string $response): HttpResponse
    {
        $file = vfsStream::newFile('response')
                ->withContent($response)
                ->at(vfsStream::setup());
        return HttpResponse::create(new Stream(fopen($file->url(), 'rb+')));
    }

    /**
     * @test
     */
    public function chunkedResponseCanBeRead()
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
        assert($httpResponse->body(), equals('foobar'));
        $headerList = $httpResponse->headers();
        assert($headerList->get('Host'), equals('localhost'));
        assert($headerList->get('Content-Length'), equals(6));

    }

    /**
     * @test
     */
    public function nonChunkedResponseWithoutContentLengthHeaderCanBeRead()
    {
        $httpResponse = $this->createResponse(Http::lines(
                'HTTP/1.1 200 OK',
                'Host: localhost',
                '',
                'foobar'
        ));
        $headerList = $httpResponse->headers();
        assert($headerList->get('Host'), equals('localhost'));
        assert($httpResponse->body(), equals('foobar'));
    }

    /**
     * @test
     */
    public function nonChunkedResponseWithContentLengthHeaderCanBeRead()
    {
        $httpResponse = $this->createResponse(Http::lines(
                'HTTP/1.1 200 OK',
                'Host: localhost',
                'Content-Length: 6',
                '',
                'foobar'
        ));
        $headerList = $httpResponse->headers();
        assert($headerList->get('Host'), equals('localhost'));
        assert($headerList->get('Content-Length'), equals(6));
        assert($httpResponse->body(), equals('foobar'));
    }

    /**
     * @test
     */
    public function canReadResponseTwice()
    {
        $httpResponse = $this->createResponse(Http::lines(
                'HTTP/1.1 200 OK',
                'Host: localhost',
                'Content-Length: 6',
                '',
                'foobar'
        ));
        assert($httpResponse->body(), equals('foobar'));
        assert($httpResponse->body(), equals('foobar'));
    }

    /**
     * @test
     */
    public function continuesOnStatusCode100()
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
        assert($headerList->get('Host'), equals('localhost'));
        assert($httpResponse->statusLine(), equals('HTTP/1.0 200 OK'));
        assert($httpResponse->httpVersion(), equals(new HttpVersion(1, 0)));
        assert($httpResponse->statusCode(), equals(200));
        assert($httpResponse->reasonPhrase(), equals('OK'));
        assert($httpResponse->statusCodeClass(), equals(Http::STATUS_CLASS_SUCCESS));
        assert($httpResponse->body(), equals('foobar'));
    }

    /**
     * @test
     */
    public function continuesOnStatusCode102()
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
        assert($headerList->get('Host'), equals('localhost'));
        assert($httpResponse->statusLine(), equals('HTTP/1.1 404 Not Found'));
        assert($httpResponse->httpVersion(), equals(new HttpVersion(1, 1)));
        assert($httpResponse->statusCode(), equals(404));
        assert($httpResponse->reasonPhrase(), equals('Not Found'));
        assert($httpResponse->statusCodeClass(), equals(Http::STATUS_CLASS_ERROR_CLIENT));
        assert($httpResponse->body(), equals('foobar'));
    }

    /**
     * @since  8.0.0
     */
    public function responseInstanceMethods(): array
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

    /**
     * @test
     * @dataProvider  responseInstanceMethods
     */
    public function illegalStatusLineLeadsToProtocolViolation($method)
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
     * @test
     * @dataProvider  responseInstanceMethods
     * @since  4.0.0
     */
    public function statusLineWithInvalidHttpVersionLeadsToProtocolViolation($method)
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
