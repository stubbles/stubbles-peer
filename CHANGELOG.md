# Changelog

## 10.0.0 (2023-12-26)

### BC breaks

* raised minimum required PHP version to 8.2
* `stubbles\peer\http\HttpUri::exists()` now accepts strings or instances of `stubbles\peer\http\HttpUri` only, previously it accepted all types and returned false for non-URIs.
* removed `stubbles\peer\ParsedUri::hasPath()`, deprecated since 8.0.0

## 9.0.2 (2020-01-04)

* fixed type error in `stubbles\peer\QueryString` with weird query strings

## 9.0.1 (2019-12-12)

* minor fixes through improved type hinting and type checks

## 9.0.0 (2019-10-30)

### BC breaks

* raised minimum required PHP version to 7.3
* `stubbles\peer\IpAddress::isValid()`, `stubbles\peer\IpAddress::isValidV4()` and `stubbles\peer\IpAddress::isValidV6()` now always expect a string to test
* removed `stubbles\peer\http\AcceptHeader::getSharedAcceptables()`, deprecated since 8.0.0

### Other changes

* fixed various possible bugs due to incorrect type usage

## 8.1.0 (2016-07-28)

* added optional parameter `$checkWith` to influence which function is used for dns checks:
  * `stubbles\peer\Uri::hasDnsRecord()`
  * `stubbles\peer\http\HttpUri::exists()`
  * `stubbles\peer\http\HttpUri::hasDnsRecord()`
* added optional parameter `$openWith` to influence which function is used to open sockets:
  * `stubbles\peer\IpAddress::openSocket()`
  * `stubbles\peer\IpAddress::openSecureSocket()`
  * `stubbles\peer\http\HttpUri::openSocket()`
* added `stubbles\peer\Socket::openWith()` to change which function is used to open sockets

## 8.0.0 (2016-07-26)

### BC breaks

* raised minimum required PHP version to 7.0.0
* introduced scalar type hints and strict type checking
* calling `stubbles\peer\http\HttpUri::fromString()` or `stubbles\peer\Uri::fromString()` with an empty string now throws `stubbles\peer\MalformedUri` instead of returning `null`
* creating a URI or HTTP Uri with empty scheme will now throw `stubbles\peer\MalformedUri`
* deprecated support for password in URIs, passing a password via URI is inherintly insecure, will be removed with 9.0.0
* all `stubbles\peer\http\HttpResonse` methods now throw a `stubbles\peer\ProtocolViolation` when the response can not be read properly
* `stubbles\peer\Timeout` now extends `stubbles\peer\ConnectionFailure`
* `stubbles\peer\Uri::hasDefaultPort()` will now return `true` instead of `false` when the string it was constructed from doesn't contain a port
* deprecated `stubbles\peer\http\AcceptHeader::getSharedAcceptables()`, use `stubbles\peer\http\AcceptHeader::sharedAcceptables()` instead, will be removed with 9.0.0

### Other changes

* fixed bug in `stubbles\peer\http\HttpUri::toHttp()` and `stubbles\peer\http\HttpUri::toHttps()` not changing the port when the only the port must be changed

## 7.1.0 (2016-06-08)

* added `stubbles\peer\isMailAddress()`
* added `stubbles\peer\IpAddress::isValid()`
* added `stubbles\peer\http\HttpUri::isValid()`
* added `stubbles\peer\http\HttpUri::exists()`
* added integration with `stubbles\values\Value` when present

## 7.0.0 (2016-01-11)

* split off from [stubbles/core](https://github.com/stubbles/stubbles-core)

### BC breaks

* removed `stubbles\peer\HeaderList::size()`, use `stubbles\peer\HeaderList::count()` or `count($headerlist)` instead
* removed classes and methods that relied on other classes in _stubbles/core_
  * `stubbles\peer\SocketInputStream`
  * `stubbles\peer\SocketOutputStream`
  * `stubbles\peer\Stream::in()`
  * `stubbles\peer\Stream::out()`
* removed `stubbles\peer\SocketDomain` which was used by `stubbles\peer\BsdSocket` which was already removed in 6.0.0
* renamed `stubbles\peer\ConnectionException` to `stubbles\peer\ConnectionFailure`
* renamed `stubbles\peer\MalformedUriException` to `stubbles\peer\MalformedUri`
* `stubbles\peer\http\Http::lines()` no longer accepts an array, but an arbitrary amount of strings instead
