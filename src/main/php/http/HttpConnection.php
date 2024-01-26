<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer\http;
use stubbles\peer\HeaderList;
/**
 * Class for connections to URIs of HTTP/HTTPS.
 */
class HttpConnection
{
    private HeaderList $headers;
    private int $timeout = 30;

    public function __construct(private HttpUri $httpUri, ?HeaderList $headers = null)
    {
        $this->headers = $headers ?? new HeaderList();
    }

    /**
     * set timeout for connection
     *
     * @api
     */
    public function timeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * do the request with the given user agent header
     *
     * @api
     */
    public function asUserAgent(string $userAgent): self
    {
        $this->headers->putUserAgent($userAgent);
        return $this;
    }

    /**
     * say the connection was refered from given uri
     *
     * @api
     */
    public function referedFrom(string $referer): self
    {
        $this->headers->putReferer($referer);
        return $this;
    }

    /**
     * add some cookie data to the request
     *
     * @api
     * @param  array<string,string>  $cookieValues  list of key-value pairs
     */
    public function withCookie(array $cookieValues): self
    {
        $this->headers->putCookie($cookieValues);
        return $this;
    }

    /**
     * authorize with given credentials
     *
     * @api
     */
    public function authorizedAs(string $user, string $password): self
    {
        $this->headers->putAuthorization($user, $password);
        return $this;
    }

    /**
     * adds any arbitrary header
     *
     * @api
     * @param  scalar  $value  value of header
     */
    public function usingHeader(string $key, mixed $value): self
    {
        $this->headers->put($key, $value);
        return $this;
    }

    /**
     * returns response object for given URI after GET request
     *
     * @api
     */
    public function get(string $version = HttpVersion::HTTP_1_1): HttpResponse
    {
        return HttpRequest::create($this->httpUri, $this->headers)
            ->get($this->timeout, $version);
    }

    /**
     * returns response object for given URI after HEAD request
     *
     * @api
     */
    public function head(string $version = HttpVersion::HTTP_1_1): HttpResponse
    {
        return HttpRequest::create($this->httpUri, $this->headers)
            ->head($this->timeout, $version);
    }

    /**
     * returns response object for given URI after POST request
     *
     * @api
     * @param  string|array<string,string>  $body
     */
    public function post(string|array $body, string $version = HttpVersion::HTTP_1_1): HttpResponse
    {
        return HttpRequest::create($this->httpUri, $this->headers)
            ->post($body, $this->timeout, $version);
    }

    /**
     * returns response object for given URI after PUT request
     *
     * @api
     * @since   2.0.0
     */
    public function put(string $body, string $version = HttpVersion::HTTP_1_1): HttpResponse
    {
        return HttpRequest::create($this->httpUri, $this->headers)
            ->put($body, $this->timeout, $version);
    }

    /**
     * returns response object for given URI after DELETE request
     *
     * @api
     * @since   2.0.0
     */
    public function delete(string $version = HttpVersion::HTTP_1_1): HttpResponse
    {
        return HttpRequest::create($this->httpUri, $this->headers)
            ->delete($this->timeout, $version);
    }
}
