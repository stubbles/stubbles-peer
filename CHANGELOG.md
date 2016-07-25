8.0.0 (2016-07-??)
------------------

### BC breaks

  * raised minimum required PHP version to 7.0.0
  * introduced scalar type hints and strict type checking
  * calling `stubbles\peer\http\HttpUri::fromString()` or `stubbles\peer\Uri::fromString()` with an empty string now throws `stubbles\peer\MalformedUri` instead of returning `null`
  * creating a URI or HTTP Uri with empty scheme will now throw `stubbles\peer\MalformedUri`
  * deprecated support for password in URIs, passing a password via URI is inherintly insecure, will be removed with 9.0.0
  * all `stubbles\peer\http\HttpResonse` methods now throw a `stubbles\peer\ProtocolViolation` when the response can not be read properly
  * `stubbles\peer\Timeout` now extends `stubbles\peer\ConnectionFailure`


7.1.0 (2016-06-08)
------------------

  * added `stubbles\peer\isMailAddress()`
  * added `stubbles\peer\IpAddress::isValid()`
  * added `stubbles\peer\http\HttpUri::isValid()`
  * added `stubbles\peer\http\HttpUri::exists()`
  * added integration with `stubbles\values\Value` when present


7.0.0 (2016-01-11)
------------------

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
