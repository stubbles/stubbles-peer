stubbles/peer
=================

Help with socket operations.


Build status
------------

![Tests](https://github.com/stubbles/stubbles-peer/workflows/Tests/badge.svg) [![Coverage Status](https://coveralls.io/repos/github/stubbles/stubbles-peer/badge.svg?branch=master)](https://coveralls.io/github/stubbles/stubbles-peer?branch=master)

[![Latest Stable Version](https://poser.pugx.org/stubbles/peer/version.png)](https://packagist.org/packages/stubbles/peer) [![Latest Unstable Version](https://poser.pugx.org/stubbles/peer/v/unstable.png)](//packagist.org/packages/stubbles/peer)


Installation
------------

_stubbles/peer_ is distributed as [Composer](https://getcomposer.org/)
package. To install it as a dependency of your package use the following
command:

    composer require "stubbles/peer": "^10.0"


Requirements
------------

_stubbles/peer_ requires at least PHP 8.2.


Working with URIs
-----------------

Sometimes it's useful to have a URI wrapped into a class which provides methods
to work with this URI. Stubbles Core provides `stubbles\peer\Uri` for such cases.

New instances can be created via `Uri::fromString('ftp://user@example.net/');`.
The following rules apply:

 * If the supplied uri string is empty no instance will be returned, but `null`
   instead.
 * If the supplied uri string is not a valid URI a `stubbles\peer\MalformedUri`
   will be thrown.
 * For all other cases, an instance of `stubbles\peer\Uri` is returned.
 * Since release 8.0.0 using passwords in URIs is discouraged, and support for
   passwords in URIs will be removed with 9.0.0. Generally, protocols provide
   other and better means to transport the password, as using it in URIs is
   inherently insecure.

In order for a uri string to be a valid URI it must adhere to the specification
laid out in [RFC 3986](https://www.ietf.org/rfc/rfc3986.txt).

Please note that hostnames will be normalized, which means if the given hostname
is e.g. _eXAMple.net_, it will be normalized to _example.net_ and always
returned in normalized form.

For the methods, the following rules apply:

 * `hasDnsRecord()` returns `false` if the URI does not contain a host.
 * `hasDnsRecord()`always returns true for _localhost_, _127.0.0.1_ and _[::1]_.
 * `hasDefaultPort()` returns `false` when a port is specified, even if it might
   be the default port for the scheme. This method is meant for child classes
   which provide additional methods for certain protocols.

URI instances can only be changed regarding their URI parameters. It is not
possible to change the scheme, host, user, password, port, or fragment of the
URI.


Working with HTTP URIs
----------------------

While the basic implementation for URIs already provides useful help when
working with URIs, sometimes one needs slightly better support for HTTP URIs.
_stubbles/peer_ provides `stubbles\peer\http\HttpUri` for such cases.

New instances can be created via `HttpUri::fromString('http://example.net/');`.
The following rules apply:

 * If the supplied uri string is empty no instance will be returned, but `null`
  instead.
 * If the supplied uri string is not a valid HTTP URI a `stubbles\peer\MalformedUri`
   will be thrown.
 * For all other cases, an instance of `stubbles\peer\http\HttpUri` is returned.

In order for a uri string to be a valid URI it must adhere to the specification
laid out in [RFC 7230](https://www.ietf.org/rfc/rfc7230.txt). Any uri strings
with other schemes than _http_ or _https_ are rejected and lead to a thrown
`stubbles\peer\MalformedUri`.

Additionally, instances can be created using `HttpUri::fromParts($scheme, $host,
$port = null, $path = '/', $queryString = null)`. _(Available since release 4.0.0.)_

### Rules for specific methods

 * `hasDefaultPort()` returns `true` if the scheme is _http_ and the port is 80.
    In case no port was originally supplied, port 80 is assumed. The method also
    returns `true` if the scheme is _https_ and the port is 443. In case no port
    was originally supplied, port 443 is assumed. In any other case the method
    returns `false`.
 * `port()` will return the port if it was originally supplied. If it was not
    supplied and scheme is _http_ return value will be 80, and if scheme  is
    _https_ return value will be 443.

### Changing portions of the HTTP URI

Instances of `HttpUri` can only be changed regarding their URI parameters. It is
not possible to change the host, user, password, port, or fragment of the URI.
Additionally it is possible to change the scheme, but this will return a new instance:

 * `toHttp()`: If the scheme is _http_ the same instance will be returned. If
    the scheme is _https_ a new instance with the same URI but scheme _http_
    will be returned.
 * `toHttps()`: If the scheme is _https_ the same instance will be returned. If
    the scheme is _http_ a new instance with the same URI but scheme _https_
    will be returned.

The current scheme can be checked with `isHttp()` and `isHttps()`.

### Establish connections to HTTP URIs

Additionally, the class provides possibilities to establish connections to the
name HTTP URI:

 * `openSocket()` will create a `stubbles\peer\Socket` instance to which one can
    connect. See section on sockets for more details.
 * `connect()` provides higher level access with a full HTTP connection. The
    method can optionally take an instance of `stubbles\peer\HeaderList` from
    which headers will be applied to the request.

### Establishing a HTTP connection

A HTTP request to the target URI can be done in the following way:

```php
$response = $httpUri->connect()
        ->asUserAgent('Not Mozilla')
        ->timeout(5)
        ->usingHeader('X-Money', 'Euro')
        ->get();
```

Please note that the call to `connect()` does not open the connection, but
establishes it locally only.  Rather, it can be used to add some more headers to
the request: user agent, referer, cookies or any other header. Only the last
method really opens the connection. Currently, _GET_, _HEAD_, _POST_, _PUT_ and
_DELETE_ requests are supported:

```php
$response = $httpUri->connect()->get();
$response = $httpUri->connect()->head();
$response = $httpUri->connect()->post($postBody);
$response = $httpUri->connect()->put($putBody);
$response = $httpUri->connect()->delete();
```

For _POST_ and _PUT_ there is one required parameter which should contain the
post/put body. For _POST_ alternatively an associative `array` can be supplied
which will be transformed into form post values, which will lead to an
automatically added _Content-type: application/x-www-form-urlencoded_ header to
the request.

The response can then be read. It provides access to all single details of the
HTTP response.


Socket operations
-----------------

Socket operations can be done using `stubbles\peer\Socket`. A socket can be
created by supplying the host to the constructor, and optionally a port. If no
port is specified it will fall back to port 80.

On construction only the socket instance is created. To actually open the
connection the `connect()` method must be called. Optionally a time-out for
establishing the connection can be supplied, if none given its 2 seconds. When
succesfully established, it returns a `stubbles\peer\Stream` instance which
provides methods to read from and write to the socket.


Other utility classes
---------------------

### `stubbles\peer\HeaderList`

This class provides possibilities to work with headers, mainly parsing a string
which contains headers and maintaining a list of headers.


### `stubbles\peer\http\AcceptHeader`

This class can parse the [accept header](http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html)
from HTTP and provides access to check if a certain type is accepted, what it's
priority is, and to find the best match if the accept header is compared against
a selection of types. It can cope with _Accept_, _Accept-Charset_ and
_Accept-Encoding_.


### `stubbles\peer\ParsedUri`

Takes a uri string as construction argument and provides access to each part of
the uri. In constrast to `stubbles\peer\Uri` and `stubbles\peer\http\HttpUri` no
checks are done on the url string which means you can construct instances from
invalid url strings, which is not possible with both other classes.


### `stubbles\peer\QueryString`

Takes a query string as construction argument and provides access to all of the
parameters within the query string to modify and remove them or to add other
parameters; and to rebuild a complete query string from this.


### `stubbles\peer\IpAddress`

_Available since release 4.0.0_

Represents an ip address and possible operations on an ip address.


Integration with _stubbles/values_
----------------------------------

In case the package _stubbles/values_ is present a recognition for
`stubbles\values\Parse` to parse http URIs to instances of
`stubbles\peer\http\HttpUri` will automatically be added.

Also, some checks are added to `stubbles\values\Value` (_available since release 7.1.0_):
- `isMailAddress()`
- `isIpAddress()`
- `isIpV4Address()`
- `isIpV6Address()`
- `isHttpUri()`
- `isExistingHttpUri()`
