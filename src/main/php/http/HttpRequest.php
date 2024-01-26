<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer\http;

use InvalidArgumentException;
use stubbles\peer\HeaderList;
use stubbles\peer\Stream;
/**
 * Class for sending a HTTP request.
 *
 * @internal
 */
class HttpRequest
{
    public function __construct(protected HttpUri $httpUri, protected HeaderList $headers) { }

    /**
     * @since  2.0.0
     */
    public static function create(HttpUri $httpUri, HeaderList $headers): self
    {
        return new self($httpUri, $headers);
    }

    public function get(
        int $timeout = 30,
        string|HttpVersion $version = HttpVersion::HTTP_1_1
    ): HttpResponse {
        $socket = $this->httpUri->openSocket($timeout);
        $this->processHeader($socket, Http::GET, $version);
        return HttpResponse::create($socket);
    }

    public function head(
        int $timeout = 30,
        string|HttpVersion $version = HttpVersion::HTTP_1_1
    ): HttpResponse {
        $socket = $this->httpUri->openSocket($timeout);
        $this->headers->put('Connection', 'close');
        $this->processHeader($socket, Http::HEAD, $version);
        return HttpResponse::create($socket);
    }

    /**
     * initializes a post request
     *
     * The body can either be given as string or as array, which is considered
     * to be a map of key-value pairs denoting post request parameters. If the
     * latter is the case an post form submit content type will be added to the
     * request.
     *
     * @param  string|array<string,string>  $body  post request body
     */
    public function post(
        string|array $body,
        int $timeout = 30,
        string|HttpVersion $version = HttpVersion::HTTP_1_1
    ): HttpResponse {
        if (is_array($body)) {
            $body = $this->transformPostValues($body);
            $this->headers->put('Content-Type', 'application/x-www-form-urlencoded');
        }

        $this->headers->put('Content-Length', strlen($body));
        $socket = $this->httpUri->openSocket($timeout);
        $this->processHeader($socket, Http::POST, $version);
        $socket->write($body);
        return HttpResponse::create($socket);
    }

    /**
     * @since  2.0.0
     */
    public function put(
        string $body,
        int $timeout = 30,
        string|HttpVersion $version = HttpVersion::HTTP_1_1
    ): HttpResponse {
        $this->headers->put('Content-Length', strlen($body));
        $socket = $this->httpUri->openSocket($timeout);
        $this->processHeader($socket, Http::PUT, $version);
        $socket->write($body);
        return HttpResponse::create($socket);
    }

    /**
     * @since  2.0.0
     */
    public function delete(
        int $timeout = 30,
        string|HttpVersion $version = HttpVersion::HTTP_1_1
    ): HttpResponse {
        $socket = $this->httpUri->openSocket($timeout);
        $this->processHeader($socket, Http::DELETE, $version);
        return HttpResponse::create($socket);
    }

    /**
     * transforms post values to post body
     *
     * @param  array<string,string>  $postValues
     */
    private function transformPostValues(array $postValues): string
    {
        $body = '';
        foreach ($postValues as $key => $value) {
            $body .= urlencode($key) . '=' . urlencode($value) . '&';
        }

        return $body;
    }

    /**
     * @throws  InvalidArgumentException
     */
    private function processHeader(
        Stream $socket,
        string $method,
        string|HttpVersion $version
    ): void {
        $version = HttpVersion::castFrom($version);
        if (!$version->equals(HttpVersion::HTTP_1_0) && !$version->equals(HttpVersion::HTTP_1_1)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid HTTP version %s, please use either %s or %s.',
                    $version,
                    HttpVersion::HTTP_1_0,
                    HttpVersion::HTTP_1_1
                )
            );
        }

        $path = $this->httpUri->path();
        if ($this->httpUri->hasQueryString() && $this->methodAllowsQueryString($method)) {
            $path .= '?' . $this->httpUri->queryString();
        }

        $socket->write(Http::line($method . ' ' . $path . ' ' . $version));
        $socket->write(Http::line('Host: ' . $this->httpUri->hostname()));
        foreach ($this->headers as $key => $value) {
            $socket->write(Http::line($key . ': ' . $value));
        }

        $socket->write(Http::emptyLine());
    }

    private function methodAllowsQueryString(string $method): bool
    {
        return Http::GET === $method || Http::HEAD === $method;
    }
}
