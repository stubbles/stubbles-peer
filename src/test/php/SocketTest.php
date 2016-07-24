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
use function bovigo\assert\assert;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\isInstanceOf;
/**
 * Test for stubbles\peer\Socket.
 *
 * @group  peer
 */
class SocketTest extends \PHPUnit_Framework_TestCase
{
    /**
     * clean up test environment
     */
    public function tearDown()
    {
        FsockopenResult::$return = null;
    }

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
        FsockopenResult::$return = fopen(__FILE__, 'rb');
        assert(
                createSocket('localhost', 80)->connect(),
                isInstanceOf(Stream::class)
        );
    }

    /**
     * @test
     * @since  6.0.0
     */
    public function connectThrowsConnectionFailureOnFailure()
    {
        FsockopenResult::$return = false;
        expect(function() { createSocket('localhost', 80)->connect(); })
                ->throws(ConnectionFailure::class);
    }
}
