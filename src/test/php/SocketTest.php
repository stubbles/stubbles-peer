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
namespace stubbles\peer;
use bovigo\callmap\NewCallable;

use function bovigo\assert\{
    assert,
    assertFalse,
    assertTrue,
    expect,
    predicate\isInstanceOf
};
/**
 * Test for stubbles\peer\Socket.
 *
 * @group  peer
 * @group  socket
 */
class SocketTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function createWithEmptyHostThrowsIllegalArgumentException()
    {
        expect(function() { createSocket(''); })
                ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function createWithNegativePortThrowsIllegalArgumentException()
    {
        expect(function() { createSocket('localhost', -1); })
                ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function isNotSecureByDefault()
    {
        $socket = createSocket('example.com');
        assertFalse($socket->usesSsl());
    }

    public function securePrefixes(): array
    {
        return [['ssl://'], ['tls://']];
    }

    /**
     * @test
     * @dataProvider  securePrefixes
     */
    public function isSecureWhenCorrectPrefixGiven(string $securePrefix)
    {
        $socket = createSocket('example.com', 443, $securePrefix);
        assertTrue($socket->usesSsl());
    }

    /**
     * @test
     * @since  6.0.0
     */
    public function connectReturnsStream()
    {
        $socket = createSocket('localhost', 80)->openWith(
                NewCallable::of('fsockopen')->mapCall(fopen(__FILE__, 'rb'))
        );
        assert(
                $socket->connect(),
                isInstanceOf(Stream::class)
        );
    }

    /**
     * @test
     * @since  6.0.0
     */
    public function connectThrowsConnectionFailureOnFailure()
    {
        $socket = createSocket('localhost', 80)->openWith(
                NewCallable::of('fsockopen')->mapCall(false)
        );
        expect(function() use ($socket) { $socket->connect(); })
                ->throws(ConnectionFailure::class);
    }
}
