<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer\http;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isNotOfSize;
/**
 * Test for stubbles\peer\http\Http.
 *
 * @since  2.0.0
 */
#[Group('peer')]
#[Group('peer_http')]
class HttpTest extends TestCase
{
    /**
     * @return  array<mixed[]>
     */
    public static function statusCodeClassTuples(): array
    {
        return [[100, Http::STATUS_CLASS_INFO],
                [101, Http::STATUS_CLASS_INFO],
                [102, Http::STATUS_CLASS_INFO],
                [118, Http::STATUS_CLASS_INFO],
                [200, Http::STATUS_CLASS_SUCCESS],
                [201, Http::STATUS_CLASS_SUCCESS],
                [202, Http::STATUS_CLASS_SUCCESS],
                [203, Http::STATUS_CLASS_SUCCESS],
                [204, Http::STATUS_CLASS_SUCCESS],
                [205, Http::STATUS_CLASS_SUCCESS],
                [206, Http::STATUS_CLASS_SUCCESS],
                [207, Http::STATUS_CLASS_SUCCESS],
                [300, Http::STATUS_CLASS_REDIRECT],
                [301, Http::STATUS_CLASS_REDIRECT],
                [302, Http::STATUS_CLASS_REDIRECT],
                [303, Http::STATUS_CLASS_REDIRECT],
                [304, Http::STATUS_CLASS_REDIRECT],
                [305, Http::STATUS_CLASS_REDIRECT],
                [307, Http::STATUS_CLASS_REDIRECT],
                [400, Http::STATUS_CLASS_ERROR_CLIENT],
                [401, Http::STATUS_CLASS_ERROR_CLIENT],
                [402, Http::STATUS_CLASS_ERROR_CLIENT],
                [403, Http::STATUS_CLASS_ERROR_CLIENT],
                [404, Http::STATUS_CLASS_ERROR_CLIENT],
                [405, Http::STATUS_CLASS_ERROR_CLIENT],
                [406, Http::STATUS_CLASS_ERROR_CLIENT],
                [407, Http::STATUS_CLASS_ERROR_CLIENT],
                [408, Http::STATUS_CLASS_ERROR_CLIENT],
                [409, Http::STATUS_CLASS_ERROR_CLIENT],
                [410, Http::STATUS_CLASS_ERROR_CLIENT],
                [411, Http::STATUS_CLASS_ERROR_CLIENT],
                [412, Http::STATUS_CLASS_ERROR_CLIENT],
                [413, Http::STATUS_CLASS_ERROR_CLIENT],
                [414, Http::STATUS_CLASS_ERROR_CLIENT],
                [415, Http::STATUS_CLASS_ERROR_CLIENT],
                [416, Http::STATUS_CLASS_ERROR_CLIENT],
                [417, Http::STATUS_CLASS_ERROR_CLIENT],
                [418, Http::STATUS_CLASS_ERROR_CLIENT],
                [421, Http::STATUS_CLASS_ERROR_CLIENT],
                [422, Http::STATUS_CLASS_ERROR_CLIENT],
                [423, Http::STATUS_CLASS_ERROR_CLIENT],
                [424, Http::STATUS_CLASS_ERROR_CLIENT],
                [425, Http::STATUS_CLASS_ERROR_CLIENT],
                [426, Http::STATUS_CLASS_ERROR_CLIENT],
                [500, Http::STATUS_CLASS_ERROR_SERVER],
                [501, Http::STATUS_CLASS_ERROR_SERVER],
                [502, Http::STATUS_CLASS_ERROR_SERVER],
                [503, Http::STATUS_CLASS_ERROR_SERVER],
                [504, Http::STATUS_CLASS_ERROR_SERVER],
                [505, Http::STATUS_CLASS_ERROR_SERVER],
                [506, Http::STATUS_CLASS_ERROR_SERVER],
                [507, Http::STATUS_CLASS_ERROR_SERVER],
                [509, Http::STATUS_CLASS_ERROR_SERVER],
                [510, Http::STATUS_CLASS_ERROR_SERVER],
                [909, Http::STATUS_CLASS_UNKNOWN]
        ];
    }

    #[Test]
    #[DataProvider('statusCodeClassTuples')]
    public function detectCorrectStatusClass(int $statusCode, string $statusClass): void
    {
        assertThat(Http::statusClassFor($statusCode), equals($statusClass));
    }

    #[Test]
    public function returnsListOfStatusCodes(): void
    {
        assertThat(Http::statusCodes(), isNotOfSize(0));
    }

    /**
     * @return  array<mixed[]>
     */
    public static function statusCodeReasonPhraseTuples(): array
    {
        $tuples = [];
        foreach (Http::statusCodes() as $statusCode => $reasonPhrase) {
            $tuples[] = [$statusCode, $reasonPhrase];
        }

        return $tuples;
    }

    #[Test]
    #[DataProvider('statusCodeReasonPhraseTuples')]
    public function returnsCorrectReasonPhrase(int $statusCode, string $reasonPhrase): void
    {
        assertThat(Http::reasonPhraseFor($statusCode), equals($reasonPhrase));
    }

    #[Test]
    public function getReasonPhraseForUnknownStatusCodeThrowsIllegalArgumentException(): void
    {
        expect(function() {
                Http::reasonPhraseFor(1);
        })->throws(\InvalidArgumentException::class);
    }

    #[Test]
    public function addsLineEnding(): void
    {
        assertThat(Http::line('foo'), equals('foo' . Http::END_OF_LINE));
    }

    #[Test]
    public function emptyLineReturnsLineEndingOnly(): void
    {
        assertThat(Http::emptyLine(), equals(Http::END_OF_LINE));
    }

    /**
     * @since  4.0.0
     */
    #[Test]
    public function linesConvertsAllLines(): void
    {
        assertThat(
                Http::lines(
                            'HEAD /foo/resource HTTP/1.1',
                            'Host: example.com',
                            'Connection: close',
                            '',
                            'bodyline1',
                            'bodyline2'
                ),
                equals(
                        Http::line('HEAD /foo/resource HTTP/1.1')
                        . Http::line('Host: example.com')
                        . Http::line('Connection: close')
                        . Http::emptyLine()
                        . 'bodyline1'
                        . 'bodyline2'
                )
        );
    }

    /**
     * @since  8.0.0
     * @return  array<string[]>
     */
    public static function validRfcs(): array
    {
        return [[Http::RFC_2616], [Http::RFC_7230]];
    }

    /**
     * @since  8.0.0
     */
    #[Test]
    #[DataProvider('validRfcs')]
    public function validRfcsAreValid(string $rfc): void
    {
        assertTrue(Http::isValidRfc($rfc));
    }

    /**
     * @since  8.0.0
     */
    #[Test]
    public function invalidRfcsAreInvalid(): void
    {
        assertFalse(Http::isValidRfc('RFC 0815'));
    }
}
