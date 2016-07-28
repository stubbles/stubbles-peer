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
namespace stubbles\peer {
    use \stubbles\peer\http\HttpConnection;
    use \stubbles\peer\http\HttpUri;

    /**
     * creates a http connection to specified uri
     * @param   string                         $uri
     * @param   \stubbles\peer\HeaderList  $headers
     * @return  \stubbles\peer\http\HttpConnection
     * @since   3.1.0
     * @api
     */
    function http(string $uri, HeaderList $headers = null): HttpConnection
    {
        return HttpUri::fromString($uri)->connect($headers);
    }

    /**
     * creates a list of headers from given map
     *
     * @param   array  $headers
     * @return  \stubbles\peer\HeaderList
     * @since   3.1.0
     * @api
     */
    function headers(array $headers = []): HeaderList
    {
        return new HeaderList($headers);
    }

    /**
     * creates a list of headers from given header string
     *
     * @param   string  $headers
     * @return  \stubbles\peer\HeaderList
     * @since   3.1.0
     * @api
     */
    function parseHeaders(string $headers): HeaderList
    {
        return HeaderList::fromString($headers);
    }

    /**
     * creates a new socket
     *
     * @param   string  $host    host to open socket to
     * @param   int     $port    port to use for opening the socket
     * @param   string  $prefix  prefix for host, e.g. ssl://
     * @return  \stubbles\peer\Socket
     * @since   3.1.0
     * @api
     */
    function createSocket(string $host, int $port = 80, string $prefix = null): Socket
    {
        return new Socket($host, $port, $prefix);
    }

    /**
     * checks if given value is a valid mail address
     *
     * @param   string  $value
     * @return  bool
     * @since   7.1.0
     */
    function isMailAddress($value): bool
    {
        if (null == $value || strlen($value) == 0) {
            return false;
        }

        if (false === filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return true;
    }

    if (class_exists('stubbles\values\Value')) {
        \stubbles\values\Value::defineCheck('isMailAddress', 'stubbles\peer\isMailAddress');
        \stubbles\values\Value::defineCheck('isIpAddress', [IpAddress::class, 'isValid']);
        \stubbles\values\Value::defineCheck('isIpV4Address', [IpAddress::class, 'isValidV4']);
        \stubbles\values\Value::defineCheck('isIpV6Address', [IpAddress::class, 'isValidV6']);
    }
}
/**
 * Functions in namespace stubbles\peer\http.
 */
namespace stubbles\peer\http {

    /**
     * returns an empty accept header representation
     *
     * @return  \stubbles\peer\http\AcceptHeader
     * @since   4.0.0
     * @api
     */
    function emptyAcceptHeader(): AcceptHeader
    {
        return new AcceptHeader();
    }

    if (class_exists('stubbles\values\Parse')) {
        \stubbles\values\Parse::addRecognition(
                function($string)
                {
                    if (substr($string, 0, 4) === Http::SCHEME) {
                        try {
                            return HttpUri::fromString($string);
                        } catch (\stubbles\peer\MalformedUri $murle) { }
                    }

                    return;

                },
                HttpUri::class
        );
    }

    if (class_exists('stubbles\values\Value')) {
        \stubbles\values\Value::defineCheck('isHttpUri', [HttpUri::class, 'isValid']);
        \stubbles\values\Value::defineCheck('isExistingHttpUri', [HttpUri::class, 'exists']);
    }
}
