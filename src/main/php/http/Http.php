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

/**
 * Container for http constants.
 *
 * @since  2.0.0
 */
class Http
{
    /**
     * default uri scheme
     */
    public const string SCHEME = 'http';
    /**
     * uri scheme for ssl
     */
    public const string SCHEME_SSL = 'https';
    /**
     * default non-ssl port
     */
    public const int PORT = 80;
    /**
     * default ssl port
     */
    public const int PORT_SSL = 443;
    /**
     * request method type: GET
     */
    public const string GET = 'GET';
    /**
     * request method type: POST
     */
    public const string POST = 'POST';
    /**
     * request method type: HEAD
     */
    public const string HEAD = 'HEAD';
    /**
     * request method type: PUT
     */
    public const string PUT = 'PUT';
    /**
     * request method type: DELETE
     */
    public const string DELETE = 'DELETE';
    /**
     * request method type: OPTIONS
     *
     * @since  4.0.0
     */
    public const string OPTIONS = 'OPTIONS';

    /**
     * end-of-line marker
     */
    public const string END_OF_LINE = "\r\n";

    /**
     * response status class: informational (100-199)
     */
    public const string STATUS_CLASS_INFO = 'Informational';
    /**
     * response status class: successful request (200-299)
     */
    public const string STATUS_CLASS_SUCCESS = 'Success';
    /**
     * response status class: redirection (300-399)
     */
    public const string STATUS_CLASS_REDIRECT = 'Redirection';
    /**
     * response status class: errors by client (400-499)
     */
    public const string STATUS_CLASS_ERROR_CLIENT = 'Client Error';
    /**
     * response status class: errors on server (500-599)
     */
    public const string STATUS_CLASS_ERROR_SERVER = 'Server Error';
    /**
     * response status class: unknown status code
     */
    public const string STATUS_CLASS_UNKNOWN = 'Unknown';
    /**
     * reference to RFC 2616 which defined HTTP/1.1 first
     *
     * @link   http://tools.ietf.org/html/rfc2616
     * @since  4.0.0
     */
    public const string RFC_2616  = 'RFC 2616';
    /**
     * reference to RFC 7230, a revised version of HTTP/1.1
     *
     * @link    http://tools.ietf.org/html/rfc7230
     * @since  4.0.0
     */
    public const string RFC_7230 = 'RFC 7230';

    /**
     * map of status code classes
     *
     * @var array<int,string>
     */
    private const array STATUS_CLASS = [
        0 => Http::STATUS_CLASS_UNKNOWN,
        1 => Http::STATUS_CLASS_INFO,
        2 => Http::STATUS_CLASS_SUCCESS,
        3 => Http::STATUS_CLASS_REDIRECT,
        4 => Http::STATUS_CLASS_ERROR_CLIENT,
        5 => Http::STATUS_CLASS_ERROR_SERVER
    ];
    /**
     * map of status codes to reason phrases
     *
     * @var  array<int,string>
     */
    private const array REASON_PHRASES = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        118 => 'Connection timed out',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a Teapot',
        421 => 'There are too many connections from your internet address',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended'
    ];

    /**
     * returns status class for given status code
     *
     * Returns null if given status code is empty.
     *
     * @api
     * @since  4.0.0
     */
    public static function statusClassFor(int $statusCode): string
    {
        $class = substr((string) $statusCode, 0, 1);
        return self::STATUS_CLASS[$class] ?? self::STATUS_CLASS_UNKNOWN;
    }

    /**
     * returns list of known status codes
     *
     * @api
     * @return  array<int,string>
     * @since   4.0.0
     */
    public static function statusCodes(): array
    {
        return self::REASON_PHRASES;
    }

    /**
     * returns reason phrase for given status code
     *
     * @api
     * @throws  InvalidArgumentException
     * @since   4.0.0
     */
    public static function reasonPhraseFor(int $statusCode): string
    {
        if (isset(self::REASON_PHRASES[$statusCode])) {
            return self::REASON_PHRASES[$statusCode];
        }

        throw new InvalidArgumentException(
            'Invalid or unknown HTTP status code ' . $statusCode
        );
    }

    /**
     * creates valid http line
     */
    public static function line(string $line): string
    {
        return $line . self::END_OF_LINE;
    }

    /**
     * creates valid http lines from given input lines
     *
     * If the array contains an empty line all lines after this empty line are
     * considered to belong to the body and will be returned as they are.
     *
     * @since  4.0.0
     */
    public static function lines(string ...$lines): string
    {
        $head = true;
        return join(
            '',
            array_map(
                function(string $line) use (&$head): string
                {
                    if (empty($line) && $head) {
                        $head = false;
                        return self::emptyLine();
                    }

                    if ($head) {
                        return self::line($line);
                    }

                    return $line;
                },
                $lines
            )
        );
    }

    /**
     * creates empty http line
     */
    public static function emptyLine(): string
    {
        return self::END_OF_LINE;
    }

    /**
     * checks if given RFC is a valid and known RFC
     */
    public static function isValidRfc(string $rfc): bool
    {
        return in_array($rfc, [self::RFC_2616, self::RFC_7230], true);
    }
}
