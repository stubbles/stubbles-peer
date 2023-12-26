<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer;
use bovigo\callmap\NewCallable;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\{
    assertThat,
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
class SocketTest extends TestCase
{
    /**
     * @test
     */
    public function createWithEmptyHostThrowsIllegalArgumentException(): void
    {
        expect(function() { createSocket(''); })
                ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function createWithNegativePortThrowsIllegalArgumentException(): void
    {
        expect(function() { createSocket('localhost', -1); })
                ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function isNotSecureByDefault(): void
    {
        $socket = createSocket('example.com');
        assertFalse($socket->usesSsl());
    }

    /**
     * @return  array<string[]>
     */
    public static function securePrefixes(): array
    {
        return [['ssl://'], ['tls://']];
    }

    /**
     * @test
     * @dataProvider  securePrefixes
     */
    public function isSecureWhenCorrectPrefixGiven(string $securePrefix): void
    {
        $socket = createSocket('example.com', 443, $securePrefix);
        assertTrue($socket->usesSsl());
    }

    /**
     * @test
     * @since  6.0.0
     */
    public function connectReturnsStream(): void
    {
        $socket = createSocket('localhost', 80)->openWith(
                NewCallable::of('fsockopen')->returns(fopen(__FILE__, 'rb'))
        );
        assertThat(
                $socket->connect(),
                isInstanceOf(Stream::class)
        );
    }

    /**
     * @test
     * @since  6.0.0
     */
    public function connectThrowsConnectionFailureOnFailure(): void
    {
        $socket = createSocket('localhost', 80)->openWith(
                NewCallable::of('fsockopen')->returns(false)
        );
        expect(function() use ($socket) { $socket->connect(); })
                ->throws(ConnectionFailure::class);
    }
}
