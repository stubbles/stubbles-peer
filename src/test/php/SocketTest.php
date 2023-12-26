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
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
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
 */
#[Group('peer')]
#[Group('socket')]
class SocketTest extends TestCase
{
    #[Test]
    public function createWithEmptyHostThrowsIllegalArgumentException(): void
    {
        expect(function(): never { createSocket(''); })
            ->throws(InvalidArgumentException::class);
    }

    #[Test]
    public function createWithNegativePortThrowsIllegalArgumentException(): void
    {
        expect(function(): never { createSocket('localhost', -1); })
            ->throws(InvalidArgumentException::class);
    }

    #[Test]
    public function isNotSecureByDefault(): void
    {
        $socket = createSocket('example.com');
        assertFalse($socket->usesSsl());
    }

    #[Test]
    #[TestWith(['ssl://'])]
    #[TestWith(['tls://'])]
    public function isSecureWhenCorrectPrefixGiven(string $securePrefix): void
    {
        $socket = createSocket('example.com', 443, $securePrefix);
        assertTrue($socket->usesSsl());
    }

    /**
     * @since  6.0.0
     */
    #[Test]
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
     * @since  6.0.0
     */
    #[Test]
    public function connectThrowsConnectionFailureOnFailure(): void
    {
        $socket = createSocket('localhost', 80)->openWith(
            fn(): bool => false
        );
        expect(fn(): never => $socket->connect())
            ->throws(ConnectionFailure::class);
    }
}
