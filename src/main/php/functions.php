<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\peer
 */
namespace stubbles\peer {
    use \stubbles\peer\http\HttpUri;

    /**
     * creates a http connection to specified uri
     * @param   string                         $uri
     * @param   \stubbles\peer\HeaderList  $headers
     * @return  \stubbles\peer\http\HttpConnection
     * @since   3.1.0
     * @api
     */
    function http($uri, HeaderList $headers = null)
    {
        return HttpUri::fromString($uri)
                      ->connect($headers);
    }

    /**
     * creates a list of headers from given map
     *
     * @param   array  $headers
     * @return  \stubbles\peer\HeaderList
     * @since   3.1.0
     * @api
     */
    function headers(array $headers = [])
    {
        return new HeaderList($headers);
    }

    /**
     * creates a list of headers from given header string
     *
     * @param   array  $headers
     * @return  \stubbles\peer\HeaderList
     * @since   3.1.0
     * @api
     */
    function parseHeaders($headers)
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
    function createSocket($host, $port = 80, $prefix = null)
    {
        return new \stubbles\peer\Socket($host, $port, $prefix);
    }

    /**
     * mock PHP's own fsockopen()
     *
     * @param   string  $hostname
     * @param   int     $port      port number to connect to
     * @param   int     $errno     holds the system level error number that occurred in the system-level connect() call
     * @param   string  $errstr    error message as a string.
     * @param   float   $timeout   connection timeout, in seconds
     * @return  bool|resource
     * @since   6.0.0
     */
    function fsockopen($hostname, $port = -1, &$errno = null, &$errstr = null, $timeout = null)
    {
        if (FsockopenResult::$return !== null) {
            return FsockopenResult::$return;
        }

        if (null === $timeout) {
            $timeout = ini_get('default_socket_timeout');
        }

        return @\fsockopen($hostname, $port, $errno, $errstr, $timeout);
    }

    /**
     * helper class for tests
     *
     * @since  6.0.0
     */
    class FsockopenResult
    {
        public static $return = null;
    }

    /**
     * checks if given value is a valid mail address
     *
     * @param   string  $value
     * @return  bool
     * @since   7.1.0
     */
    function isMailAddress($value)
    {
        if (null == $value || strlen($value) == 0) {
            return false;
        }

        $url = @parse_url('mailto://' . $value);
        if (!isset($url['host']) || !preg_match('/^([a-zA-Z0-9-]*)\.([a-zA-Z]{2,4})$/', $url['host'])) {
            return false;
        }

        if (!isset($url['user']) || strlen($url['user']) == 0 || !preg_match('/^[0-9a-zA-Z]([-_\.]?[0-9a-zA-Z])*$/', $url['user'])) {
            return false;
        }

        return true;
    }

    if (class_exists('stubbles\values\Value')) {
        \stubbles\values\Value::defineCheck('isMailAddress', 'stubbles\peer\isMailAddress');
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
    function emptyAcceptHeader()
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
        \stubbles\values\Value::defineCheck('isIpAddress', [\stubbles\peer\IpAddress::class, 'isValid']);
        \stubbles\values\Value::defineCheck('isIpV4Address', [\stubbles\peer\IpAddress::class, 'isValidV4']);
        \stubbles\values\Value::defineCheck('isIpV6Address', [\stubbles\peer\IpAddress::class, 'isValidV6']);
    }
}
