<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer;

use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
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
 * @since  6.0.0
 */
#[Group('peer')]
class StreamTest extends TestCase
{
    private vfsStreamFile $file;
    /** @var  resource */
    private $underlyingStream;
    private Stream $stream;

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

    #[Test]
    public function createWithInvalidResourceThrowsIllegalArgumentException(): void
    {
        expect(fn() => new Stream('foo'))
            ->throws(InvalidArgumentException::class);
    }

    #[Test]
    public function readReturnsDataOfFirstLine(): void
    {
        assertThat($this->stream->read(), equals("bar\n"));
    }

    #[Test]
    public function readLineReturnsTrimmedDataOfFirstLine(): void
    {
        assertThat($this->stream->readLine(), equals('bar'));
    }

    #[Test]
    public function readBinaryReturnsData(): void
    {
        assertThat($this->stream->readBinary(), equals("bar\nbaz"));
    }

    #[Test]
    public function writesToResource(): void
    {
        assertThat($this->stream->write('yoyoyoyo'), equals(8));
        assertThat($this->file->getContent(), equals('yoyoyoyo'));
    }

    #[Test]
    public function eofReturnsTrueWhenNotAtEnd(): void
    {
        assertFalse($this->stream->eof());
    }

    #[Test]
    public function eofReturnsTrueWhenAtEnd(): void
    {
        $this->stream->readBinary();
        assertTrue($this->stream->eof());
    }

    #[Test]
    public function nullingTheStreamClosesTheResource(): void
    {
        $stream = new Stream($this->underlyingStream);
        $stream->write('foo');
        $stream = null;
        assertFalse(is_resource($this->underlyingStream));
    }
}
