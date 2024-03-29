<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer\http;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isSameAs;
/**
 * Test for stubbles\peer\http\HttpVersion.
 *
 * @since  4.0.0
 */
#[Group('peer')]
#[Group('peer_http')]
class HttpVersionTest extends TestCase
{
    #[Test]
    public function parseFromEmptyStringThrowsIllegalArgumentException(): void
    {
        expect(function() {
            HttpVersion::fromString('');
        })
            ->throws(InvalidArgumentException::class)
            ->withMessage('Given HTTP version is empty');
    }

    #[Test]
    public function parseFromStringThrowsIllegalArgumentExceptionWhenParsingFails(): void
    {
        expect(function() {
            HttpVersion::fromString('invalid');
        })
            ->throws(InvalidArgumentException::class)
            ->withMessage('Given HTTP version "invalid" can not be parsed');
    }

    #[Test]
    public function fromStringDetectsCorrectMajorVersion(): void
    {
        assertThat(HttpVersion::fromString('HTTP/1.2')->major(), equals(1));
    }

    #[Test]
    public function fromStringDetectsCorrectMinorVersion(): void
    {
        assertThat(HttpVersion::fromString('HTTP/1.2')->minor(), equals(2));
    }

    #[Test]
    public function constructWithInvalidMajorArgumentThrowsIllegalArgumentException(): void
    {
        expect(function() { new HttpVersion('foo', 1); })
            ->throws(InvalidArgumentException::class)
            ->withMessage('Given major version "foo" is not an integer.');
    }

    #[Test]
    public function constructWithInvalidMinorArgumentThrowsIllegalArgumentException(): void
    {
        expect(function() { new HttpVersion(1, 'foo'); })
            ->throws(InvalidArgumentException::class)
            ->withMessage('Given minor version "foo" is not an integer.');
    }

    #[Test]
    public function constructWithNegativeMajorVersionThrowsIllegalArgumentException(): void
    {
        expect(function() { new HttpVersion(-2, 1); })
            ->throws(InvalidArgumentException::class)
            ->withMessage('Major version can not be negative.');
    }

    #[Test]
    public function parseFromStringWithNegativeMajorNumberThrowsIllegalArgumentExceptionWhenParsingFails(): void
    {
        expect(function() { HttpVersion::fromString('HTTP/-2.1'); })
          ->throws(InvalidArgumentException::class)
          ->withMessage('Major version can not be negative.');
    }

    #[Test]
    public function constructWithNegativeMinorVersionThrowsIllegalArgumentException(): void
    {
        expect(function() { new HttpVersion(1, -2); })
            ->throws(InvalidArgumentException::class)
            ->withMessage('Minor version can not be negative.');
    }

    #[Test]
    public function parseFromStringWithNegativeMinorNumberThrowsIllegalArgumentExceptionWhenParsingFails(): void
    {
        expect(function() { HttpVersion::fromString('HTTP/2.-1'); })
            ->throws(InvalidArgumentException::class)
            ->withMessage('Minor version can not be negative.');
    }

    #[Test]
    public function castToStringReturnsCorrectVersionString(): void
    {
        $versionString = 'HTTP/1.1';
        assertThat(
            (string) HttpVersion::fromString($versionString),
            equals($versionString)
        );
    }

    #[Test]
    public function castFromEmptyWithoutDefaultThrowsIllegalArgumentException(): void
    {
        expect(function() { HttpVersion::castFrom(''); })
            ->throws(\InvalidArgumentException::class)
            ->withMessage('Given HTTP version is empty');
    }

    #[Test]
    public function castFromInstanceReturnsInstance(): void
    {
        $httpVersion = new HttpVersion(1, 1);
        assertThat(HttpVersion::castFrom($httpVersion), isSameAs($httpVersion));
    }

    #[Test]
    public function castFromStringReturnsInstance(): void
    {
        assertThat(HttpVersion::castFrom('HTTP/1.1'), equals(new HttpVersion(1, 1)));
    }

    #[Test]
    public function doesNotEqualEmptyVersion(): void
    {
        assertFalse(HttpVersion::fromString(HttpVersion::HTTP_1_1)->equals(''));
    }

    #[Test]
    public function doesNotEqualInvalidVersion(): void
    {
        assertFalse(
            HttpVersion::fromString(HttpVersion::HTTP_1_1)
                ->equals('HTTP/404')
        );
    }

    #[Test]
    public function doesNotEqualWhenMajorVersionDiffers(): void
    {
        assertFalse(
            HttpVersion::fromString(HttpVersion::HTTP_1_1)
                ->equals('HTTP/2.0')
        );
    }

    #[Test]
    public function doesNotEqualWhenMinorVersionDiffers(): void
    {
        assertFalse(
            HttpVersion::fromString(HttpVersion::HTTP_1_1)
                ->equals(HttpVersion::HTTP_1_0)
        );
    }

    #[Test]
    public function isEqualWhenMajorAndMinorVersionEqual(): void
    {
        assertTrue(
            HttpVersion::fromString(HttpVersion::HTTP_1_1)
                ->equals(HttpVersion::HTTP_1_1)
        );
    }
}
