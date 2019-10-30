<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer;
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
 *
 * @group  peer
 */
class HeaderListTest extends TestCase
{
    /**
     * instance to test
     *
     * @type  HeaderList
     */
    protected $headerList;

    protected function setUp(): void
    {
        $this->headerList = headers();
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function hasNoHeadersByDefault()
    {
        assertThat($this->headerList, isOfSize(0));
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function containsGivenHeader()
    {
        $headerList = headers(['Binford' => 6100]);
        assertTrue($headerList->containsKey('Binford'));
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function initialSizeEqualsAmountOfGivenHeaders()
    {
        $headerList = headers(['Binford' => 6100]);
        assertThat($headerList, isOfSize(1));
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function returnsValeOfGivenHeader()
    {
        $headerList = headers(['Binford' => 6100]);
        assertThat($headerList->get('Binford'), equals('6100'));
    }

    /**
     * @test
     */
    public function addingHeaderIncreasesSize()
    {
        assertThat($this->headerList->put('Binford', 6100), isOfSize(1));
    }

    /**
     * @test
     */
    public function containsAddedHeader()
    {
        assertTrue(
                $this->headerList->put('Binford', 6100)
                        ->containsKey('Binford')
        );
    }

    /**
     * @test
     */
    public function returnsValueOfAddedHeader()
    {
        assertThat(
                $this->headerList->put('Binford', 6100)
                        ->get('Binford'),
                equals('6100')
        );
    }

    /**
     * helper method to assert presence and content of binford headers
     *
     * @param  stubbles\peer\HeaderList  $headerList
     */
    protected function assertBinford(HeaderList $headerList)
    {
        assertTrue($headerList->containsKey('Binford'));
        assertTrue($headerList->containsKey('X-Power'));
        assertThat($headerList, isOfSize(2));
        assertThat($headerList->get('Binford'), equals('6100'));
        assertThat($headerList->get('X-Power'), equals('More power!'));
    }

    /**
     * @test
     */
    public function containsAllHeadersFromParsedString()
    {
        $this->assertBinford(parseHeaders("Binford: 6100\r\nX-Power: More power!"));
    }

    /**
     * @since 2.1.2
     * @test
     */
    public function doubleOccurenceOfColonSplitsOnFirstColon()
    {
        $headerList = parseHeaders("Binford: 6100\r\nX-Powered-By: Servlet 2.4; JBoss-4.2.2.GA (build: SVNTag=JBoss_4_2_2_GA date=200710231031)/Tomcat-5.5\r\nContent-Type: text/html\r\n");
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
     * @test
     */
    public function appendingInvalidHeaderStructureThrowsIllegalArgumentException()
    {
        expect(function() {
                $this->headerList->append(400);
        })->throws(\InvalidArgumentException::class);
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function appendAddsHeadersFromString()
    {
        $this->assertBinford(
                $this->headerList->put('Binford', '6000')
                        ->append("Binford: 6100\r\nX-Power: More power!")
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function appendAddsHeadersFromArray()
    {
        $this->assertBinford(
                $this->headerList->put('Binford', '6000')
                        ->append(
                                ['Binford' => '6100',
                                 'X-Power' => 'More power!'
                                ]
                        )

        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function appendAddsHeadersFromOtherInstance()
    {
        $this->assertBinford(
                $this->headerList->put('Binford', '6000')
                        ->append(
                                headers(
                                        ['Binford' => '6100',
                                         'X-Power' => 'More power!'
                                        ]
                                )
                        )

        );
    }

    /**
     * @test
     */
    public function putArrayThrowsIllegalArgumentException()
    {
        expect(function() {
                $this->headerList->put('Binford', [6100]);
        })->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function putObjectThrowsIllegalArgumentException()
    {
        expect(function() {
                $this->headerList->put('Binford', new \stdClass());
        })->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function remove()
    {
        assertFalse(
                $this->headerList->put('Binford', '6100')
                        ->remove('Binford')
                        ->containsKey('Binford')
        );
    }

    /**
     * @test
     */
    public function putUserAgent()
    {
        assertTrue(
                $this->headerList->putUserAgent('Binford 6100')
                        ->containsKey('User-Agent')
        );
        assertThat($this->headerList->get('User-Agent'), equals('Binford 6100'));
    }


    /**
     * @test
     */
    public function putReferer()
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


    /**
     * @test
     */
    public function putCookie()
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


    /**
     * @test
     */
    public function putAuthorization()
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

    /**
     * @test
     */
    public function hasNoDateByDefault()
    {
        assertFalse($this->headerList->containsKey('Date'));
    }

    /**
     * @test
     */
    public function putDateWithoutValueGiven()
    {
        assertTrue($this->headerList->putDate()->containsKey('Date'));
    }

    /**
     * @test
     */
    public function putDateWithGivenValue()
    {
        $time = time();
        assertTrue($this->headerList->putDate($time)->containsKey('Date'));
        assertThat(
                $this->headerList->get('Date'),
                equals(gmdate('D, d M Y H:i:s', $time) . ' GMT')
        );
    }

    /**
     * @test
     */
    public function enablePower()
    {
        assertTrue($this->headerList->enablePower()->containsKey('X-Binford'));
        assertThat($this->headerList->get('X-Binford'), equals('More power!'));
    }

    /**
     * @test
     */
    public function returnsFalseOnCheckForNonExistingHeader()
    {
        assertFalse($this->headerList->containsKey('foo'));
    }

    /**
     * @test
     */
    public function returnsNullForNonExistingHeader()
    {
        assertNull($this->headerList->get('foo'));
    }

    /**
     * @test
     */
    public function returnsDefaultValueForNonExistingHeader()
    {
        assertThat($this->headerList->get('foo', 'bar'), equals('bar'));
    }

    /**
     * @test
     */
    public function returnsAddedValueForExistingHeader()
    {
        assertThat($this->headerList->put('foo', 'baz')->get('foo'), equals('baz'));
    }

    /**
     * @test
     */
    public function returnsAddedValueForExistingHeaderWhenDefaultSupplied()
    {
        assertThat(
                $this->headerList->put('foo', 'baz')->get('foo', 'bar'),
                equals('baz')
        );
    }

    /**
     * @test
     */
    public function clearRemovesAllHeaders()
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

    /**
     * @test
     */
    public function iteratorIsEmptyForDefaultHeaderList()
    {
        $counter = 0;
        foreach ($this->headerList as $key => $value) {
            $counter++;
        }

        assertThat($counter, equals(0));
    }

    /**
     * @test
     */
    public function iterableOverAddedHeaders()
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
     * @test
     * @since  7.0.0
     */
    public function headerListCanBeCastedToString()
    {
        $headers = "Binford: 6100\r\nX-Power: More power!";
        assertThat(
                (string) parseHeaders($headers),
                equals($headers)
        );
    }
}
