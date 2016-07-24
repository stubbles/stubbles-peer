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
use org\bovigo\vfs\vfsStream;

use function bovigo\assert\assert;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
/**
 * Test for stubbles\peer\Stream.
 *
 * @group  peer
 * @since  6.0.0
 */
class StreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @type  org\bovigo\vfs\vfsStreamFile
     */
    private $file;
    /**
     * @type  resource
     */
    private $underlyingStream;
    /**
     * @type  \stubbles\peer\Stream
     */
    private $stream;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $root = vfsStream::setup();
        $this->file = vfsStream::newFile('foo.txt')
                ->withContent("bar\nbaz")
                ->at($root);
        $this->underlyingStream = fopen($this->file->url(), 'rb+');
        $this->stream = new Stream($this->underlyingStream);
    }

    /**
     * @test
     */
    public function createWithInvalidResourceThrowsIllegalArgumentException()
    {
        expect(function() { new Stream('foo'); })
                ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function readReturnsDataOfFirstLine()
    {
        assert($this->stream->read(), equals("bar\n"));
    }

    /**
     * @test
     */
    public function readLineReturnsTrimmedDataOfFirstLine()
    {
        assert($this->stream->readLine(), equals('bar'));
    }

    /**
     * @test
     */
    public function readBinaryReturnsData()
    {
        assert($this->stream->readBinary(), equals("bar\nbaz"));
    }

    /**
     * @test
     */
    public function writesToResource()
    {
        assert($this->stream->write('yoyoyoyo'), equals(8));
        assert($this->file->getContent(), equals('yoyoyoyo'));
    }

    /**
     * @test
     */
    public function eofReturnsTrueWhenNotAtEnd()
    {
        assertFalse($this->stream->eof());
    }

    /**
     * @test
     */
    public function eofReturnsTrueWhenAtEnd()
    {
        $this->stream->readBinary();
        assertTrue($this->stream->eof());
    }

    /**
     * @test
     */
    public function nullingTheStreamClosesTheResource()
    {
        $this->stream = null;
        assertFalse(is_resource($this->underlyingStream));
    }
}
