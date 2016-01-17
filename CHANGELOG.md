7.1.0 (2016-01-??)
------------------

  * added `stubbles\peer\isMailAddress()`
  * added `stubbles\peer\IpAddress::isValid()`
  * added `stubbles\peer\http\HttpUri::isValid()`
  * added `stubbles\peer\http\HttpUri::exists()`


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
