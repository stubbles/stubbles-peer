<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\fail;
use function bovigo\assert\predicate\equals;
/**
 * Test for stubbles\peer\Stream.
 *
 * @group  peer
 * @since  6.0.0
 */
class StreamTest extends TestCase
{
    /**
     * @var  \org\bovigo\vfs\vfsStreamFile
     */
    private $file;
    /**
     * @var  resource
     */
    private $underlyingStream;
    /**
     * @var  \stubbles\peer\Stream
     */
    private $stream;

    protected function setUp(): void
    {
        $root = vfsStream::setup();
        $this->file = vfsStream::newFile('foo.txt')
                ->withContent("bar\nbaz")
                ->at($root);
        $handle = fopen($this->file->url(), 'rb+');
        if (false === $handle) {
            fail('Could not open vfsStream url');
        }

        $this->underlyingStream = $handle;
        $this->stream = new Stream($this->underlyingStream);
    }

    /**
     * @test
     */
    public function createWithInvalidResourceThrowsIllegalArgumentException(): void
    {
        expect(function() { new Stream('foo'); })
                ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function readReturnsDataOfFirstLine(): void
    {
        assertThat($this->stream->read(), equals("bar\n"));
    }

    /**
     * @test
     */
    public function readLineReturnsTrimmedDataOfFirstLine(): void
    {
        assertThat($this->stream->readLine(), equals('bar'));
    }

    /**
     * @test
     */
    public function readBinaryReturnsData(): void
    {
        assertThat($this->stream->readBinary(), equals("bar\nbaz"));
    }

    /**
     * @test
     */
    public function writesToResource(): void
    {
        assertThat($this->stream->write('yoyoyoyo'), equals(8));
        assertThat($this->file->getContent(), equals('yoyoyoyo'));
    }

    /**
     * @test
     */
    public function eofReturnsTrueWhenNotAtEnd(): void
    {
        assertFalse($this->stream->eof());
    }

    /**
     * @test
     */
    public function eofReturnsTrueWhenAtEnd(): void
    {
        $this->stream->readBinary();
        assertTrue($this->stream->eof());
    }

    /**
     * @test
     */
    public function nullingTheStreamClosesTheResource(): void
    {
        $stream = new Stream($this->underlyingStream);
        $stream = null;
        assertFalse(is_resource($this->underlyingStream));
    }
}
