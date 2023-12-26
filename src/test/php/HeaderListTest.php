<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\assertThat;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertNull;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isOfSize;
/**
 * Test for stubbles\peer\HeaderList.
 */
#[Group('peer')]
class HeaderListTest extends TestCase
{
    private HeaderList $headerList;

    protected function setUp(): void
    {
        $this->headerList = headers();
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function hasNoHeadersByDefault(): void
    {
        assertThat($this->headerList, isOfSize(0));
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function containsGivenHeader(): void
    {
        $headerList = headers(['Binford' => 6100]);
        assertTrue($headerList->containsKey('Binford'));
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function initialSizeEqualsAmountOfGivenHeaders(): void
    {
        $headerList = headers(['Binford' => 6100]);
        assertThat($headerList, isOfSize(1));
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function returnsValeOfGivenHeader(): void
    {
        $headerList = headers(['Binford' => 6100]);
        assertThat($headerList->get('Binford'), equals('6100'));
    }

    #[Test]
    public function addingHeaderIncreasesSize(): void
    {
        assertThat($this->headerList->put('Binford', 6100), isOfSize(1));
    }

    #[Test]
    public function containsAddedHeader(): void
    {
        assertTrue(
            $this->headerList->put('Binford', 6100)
                ->containsKey('Binford')
        );
    }

    #[Test]
    public function returnsValueOfAddedHeader(): void
    {
        assertThat(
            $this->headerList->put('Binford', 6100)->get('Binford'),
            equals('6100')
        );
    }

    /**
     * helper method to assert presence and content of binford headers
     */
    private function assertBinford(HeaderList $headerList): void
    {
        assertTrue($headerList->containsKey('Binford'));
        assertTrue($headerList->containsKey('X-Power'));
        assertThat($headerList, isOfSize(2));
        assertThat($headerList->get('Binford'), equals('6100'));
        assertThat($headerList->get('X-Power'), equals('More power!'));
    }

    #[Test]
    public function containsAllHeadersFromParsedString(): void
    {
        $this->assertBinford(parseHeaders("Binford: 6100\r\nX-Power: More power!"));
    }

    /**
     * @since 2.1.2
     */
    #[Test]
    public function doubleOccurenceOfColonSplitsOnFirstColon(): void
    {
        $headerList = parseHeaders(
            "Binford: 6100\r\nX-Powered-By: Servlet 2.4; JBoss-4.2.2.GA (build: SVNTag=JBoss_4_2_2_GA date=200710231031)/Tomcat-5.5\r\nContent-Type: text/html\r\n"
        );
        assertTrue($headerList->containsKey('Binford'));
        assertThat($headerList->get('Binford'), equals('6100'));
        assertTrue($headerList->containsKey('X-Powered-By'));
        assertThat(
                $headerList->get('X-Powered-By'),
                equals('Servlet 2.4; JBoss-4.2.2.GA (build: SVNTag=JBoss_4_2_2_GA date=200710231031)/Tomcat-5.5')
        );
        assertTrue($headerList->containsKey('Content-Type'));
        assertThat($headerList->get('Content-Type'), equals('text/html'));
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function appendAddsHeadersFromString(): void
    {
        $this->assertBinford(
            $this->headerList->put('Binford', '6000')
                ->append("Binford: 6100\r\nX-Power: More power!")
        );
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function appendAddsHeadersFromArray(): void
    {
        $this->assertBinford(
            $this->headerList->put('Binford', '6000')
                ->append([
                    'Binford' => '6100',
                    'X-Power' => 'More power!'
                ])
        );
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function appendAddsHeadersFromOtherInstance(): void
    {
        $this->assertBinford(
            $this->headerList->put('Binford', '6000')
                ->append(
                    headers([
                        'Binford' => '6100',
                        'X-Power' => 'More power!'
                    ])
                )
        );
    }

    #[Test]
    public function putArrayThrowsIllegalArgumentException(): void
    {
        expect(function() {
            $this->headerList->put('Binford', [6100]);
        })->throws(\InvalidArgumentException::class);
    }

    #[Test]
    public function putObjectThrowsIllegalArgumentException(): void
    {
        expect(function() {
            $this->headerList->put('Binford', new \stdClass());
        })->throws(\InvalidArgumentException::class);
    }

    #[Test]
    public function remove(): void
    {
        assertFalse(
            $this->headerList->put('Binford', '6100')
                ->remove('Binford')
                ->containsKey('Binford')
        );
    }

    #[Test]
    public function putUserAgent(): void
    {
        assertTrue(
            $this->headerList->putUserAgent('Binford 6100')
                    ->containsKey('User-Agent')
        );
        assertThat($this->headerList->get('User-Agent'), equals('Binford 6100'));
    }


    #[Test]
    public function putReferer(): void
    {
        assertTrue(
            $this->headerList->putReferer('http://example.com/')
                ->containsKey('Referer')
        );
        assertThat(
            $this->headerList->get('Referer'),
            equals('http://example.com/')
        );
    }


    #[Test]
    public function putCookie(): void
    {
        assertTrue(
            $this->headerList->putCookie(['testcookie1' => 'testvalue1 %&'])
                ->containsKey('Cookie')
        );
        assertThat(
            $this->headerList->get('Cookie'),
            equals('testcookie1=' . urlencode('testvalue1 %&') . ';')
        );
    }


    #[Test]
    public function putAuthorization(): void
    {
        assertTrue(
            $this->headerList->putAuthorization('user', 'pass')
                ->containsKey('Authorization')
        );
        assertThat(
            $this->headerList->get('Authorization'),
            equals('BASIC ' . base64_encode('user:pass'))
        );
    }

    #[Test]
    public function hasNoDateByDefault(): void
    {
        assertFalse($this->headerList->containsKey('Date'));
    }

    #[Test]
    public function putDateWithoutValueGiven(): void
    {
        assertTrue($this->headerList->putDate()->containsKey('Date'));
    }

    #[Test]
    public function putDateWithGivenValue(): void
    {
        $time = time();
        assertTrue($this->headerList->putDate($time)->containsKey('Date'));
        assertThat(
            $this->headerList->get('Date'),
            equals(gmdate('D, d M Y H:i:s', $time) . ' GMT')
        );
    }

    #[Test]
    public function enablePower(): void
    {
        assertTrue($this->headerList->enablePower()->containsKey('X-Binford'));
        assertThat($this->headerList->get('X-Binford'), equals('More power!'));
    }

    #[Test]
    public function returnsFalseOnCheckForNonExistingHeader(): void
    {
        assertFalse($this->headerList->containsKey('foo'));
    }

    #[Test]
    public function returnsNullForNonExistingHeader(): void
    {
        assertNull($this->headerList->get('foo'));
    }

    #[Test]
    public function returnsDefaultValueForNonExistingHeader(): void
    {
        assertThat($this->headerList->get('foo', 'bar'), equals('bar'));
    }

    #[Test]
    public function returnsAddedValueForExistingHeader(): void
    {
        assertThat($this->headerList->put('foo', 'baz')->get('foo'), equals('baz'));
    }

    #[Test]
    public function returnsAddedValueForExistingHeaderWhenDefaultSupplied(): void
    {
        assertThat(
            $this->headerList->put('foo', 'baz')->get('foo', 'bar'),
            equals('baz')
        );
    }

    #[Test]
    public function clearRemovesAllHeaders(): void
    {
        assertThat(
            $this->headerList->putUserAgent('Binford 6100')
                ->putReferer('Home Improvement')
                ->putCookie(['testcookie1' => 'testvalue1 %&'])
                ->putAuthorization('user', 'pass')
                ->putDate(time())
                ->enablePower()
                ->clear(),
            isOfSize(0)
        );
    }

    #[Test]
    public function iteratorIsEmptyForDefaultHeaderList(): void
    {
        $counter = 0;
        foreach ($this->headerList as $key => $value) {
            $counter++;
        }

        assertThat($counter, equals(0));
    }

    #[Test]
    public function iterableOverAddedHeaders(): void
    {
        $counter = 0;
        $this->headerList->putUserAgent('Binford 6100')
                         ->put('X-TV', 'Home Improvement');
        foreach ($this->headerList as $key => $value) {
            $counter++;
        }

        assertThat($counter, equals(2));
    }

    /**
     * @since  7.0.0
     */
    #[Test]
    public function headerListCanBeCastedToString(): void
    {
        $headers = "Binford: 6100\r\nX-Power: More power!";
        assertThat(
            (string) parseHeaders($headers),
            equals($headers)
        );
    }
}
