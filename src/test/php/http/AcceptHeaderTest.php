<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer\http;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\assertThat;
use function bovigo\assert\assertEmpty;
use function bovigo\assert\assertEmptyArray;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertNull;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isOfSize;
/**
 * Test for stubbles\peer\http\AcceptHeader.
 *
 * @group  peer
 * @group  peer_http
 */
class AcceptHeaderTest extends TestCase
{
    /**
     * instance to test
     *
     * @var  AcceptHeader
     */
    protected $acceptHeader;

    protected function setUp(): void
    {
        $this->acceptHeader = new AcceptHeader();
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function emptyAcceptHeaderReturnsInstanceWithoutAcceptables(): void
    {
        assertEmpty(emptyAcceptHeader());
    }

    /**
     * @test
     */
    public function addAcceptableIncreasesCount(): void
    {
        assertThat($this->acceptHeader->addAcceptable('text/plain'), isOfSize(1));
    }

    /**
     * @return  array<mixed[]>
     */
    public function provider(): array
    {
        return [['text/plain;q=0.5',
                 ['text/plain' => 0.5],
                 'text/plain;q=0.5'
                ],
                ['text/plain;level=2;q=0.5',
                 ['text/plain;level=2' => 0.5],
                 'text/plain;level=2;q=0.5'
                ],
                ['text/plain; q=0.5',
                 ['text/plain' => 0.5],
                 'text/plain;q=0.5'
                ],
                ['text/plain;level=2; q=0.5',
                 ['text/plain;level=2' => 0.5],
                 'text/plain;level=2;q=0.5'
                ],
                ['text/plain;q=1',
                 ['text/plain' => 1.0],
                 'text/plain'
                ],
                ['text/plain; q=1',
                 ['text/plain' => 1.0],
                 'text/plain'
                ],
                ['text/plain',
                 ['text/plain' => 1.0],
                 'text/plain'
                ],
                ['text/plain;level3',
                 ['text/plain;level3' => 1.0],
                 'text/plain;level3'
                ],
                ['text/*;q=0.3, text/html;q=0.7, text/html;level=1,text/html;level=2;q=0.4, */*;q=0.5',
                 ['text/*'            => 0.3,
                  'text/html'         => 0.7,
                  'text/html;level=1' => 1.0,
                  'text/html;level=2' => 0.4,
                  '*/*'               => 0.5
                 ],
                 'text/*;q=0.3,text/html;q=0.7,text/html;level=1,text/html;level=2;q=0.4,*/*;q=0.5'
                ],
                ['text/plain; q=0.5, text/html,text/x-dvi; q=0.8, text/x-c',
                 ['text/plain'        => 0.5,
                  'text/html'         => 1.0,
                  'text/x-dvi'        => 0.8,
                  'text/x-c'          => 1.0
                 ],
                 'text/plain;q=0.5,text/html,text/x-dvi;q=0.8,text/x-c'
                ]
               ];
    }

    /**
     * @param  string        $parseValue
     * @param  array<mixed>  $expectedList
     * @test
     * @dataProvider  provider
     */
    public function parseYieldsCorrectValues(string $parseValue, array $expectedList): void
    {
        $acceptHeader = AcceptHeader::parse($parseValue);
        foreach ($expectedList as $mimeType => $priority) {
            assertTrue($acceptHeader->hasSharedAcceptables([$mimeType]));
            assertThat($acceptHeader->priorityFor($mimeType), equals($priority));
        }
    }

    /**
     * @param  string        $parseValue
     * @param  array<mixed>  $expectedList
     * @test
     * @dataProvider  provider
     */
    public function parsedStringCanBeRecreated(
            string $parseValue,
            array $expectedList,
            string $expectedString
    ): void {
        assertThat(
                (string) AcceptHeader::parse($parseValue),
                equals($expectedString)
        );
    }

    /**
     * @test
     */
    public function addAcceptableWithPriorityLowerThan0ThrowsIllegalArgumentException(): void
    {
        expect(function() {
                $this->acceptHeader->addAcceptable('text/html', -0.1);
        })->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function addAcceptableWithPriorityGreaterThan1ThrowsIllegalArgumentException(): void
    {
        expect(function() {
                $this->acceptHeader->addAcceptable('text/html', 1.1);
        })->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function priorityOnEmptyListReturnsPriorityOf1ForEachAcceptable(): void
    {
        assertThat($this->acceptHeader->priorityFor('text/html'), equals(1.0));
    }

    /**
     * @test
     */
    public function priorityForNonExistingAcceptableReturns0(): void
    {
        assertThat(
                $this->acceptHeader->addAcceptable('text/plain')
                        ->priorityFor('text/html'),
                equals(0)
        );
    }

    /**
     * @test
     */
    public function priorityForNonExistingAcceptableReturnsPriorityForGeneralAcceptableIfThisIsInList(): void
    {
        assertThat(
                $this->acceptHeader->addAcceptable('*/*')
                        ->priorityFor('text/html'),
                equals(1.0)
        );
    }

    /**
     * @test
     */
    public function priorityForNonExistingAcceptableReturnsPriorityForMainTypeAcceptableIfThisIsInList(): void
    {
        assertThat(
                $this->acceptHeader->addAcceptable('text/plain')
                        ->addAcceptable('text/*', 0.5)
                        ->priorityFor('text/html'),
                equals(0.5)
        );
    }

    /**
     * @test
     */
    public function priorityForExistingAcceptableReturnsItsPriority(): void
    {
        assertThat(
                $this->acceptHeader->addAcceptable('text/html')
                        ->addAcceptable('text/plain', 0.2)
                        ->priorityFor('text/plain'),
                equals(0.2)
        );
    }

    /**
     * @test
     */
    public function findAcceptableWithGreatestPriorityForEmptyListReturnsNull(): void
    {
        assertNull($this->acceptHeader->findAcceptableWithGreatestPriority());
    }

    /**
     * @test
     * @group foo
     */
    public function findAcceptableWithGreatestPriority(): void
    {
        $this->acceptHeader->addAcceptable('text/plain', 0.2);
        assertThat(
                $this->acceptHeader->findAcceptableWithGreatestPriority(),
                equals('text/plain')
        );
        $this->acceptHeader->addAcceptable('text/html');
        assertThat(
                $this->acceptHeader->findAcceptableWithGreatestPriority(),
                equals('text/html')
        );
        $this->acceptHeader->addAcceptable('text/other');
        assertThat(
                $this->acceptHeader->findAcceptableWithGreatestPriority(),
                equals('text/other')->or(equals('text/html')) // order depends on PHP version
        );
    }

    /**
     * @return  array<string, mixed[]>
     */
    public function acceptedMimetypes(): array
    {
        return [
                'empty list'  => [[]],
                'filled list' => [['text/plain']]
        ];
    }

    /**
     * @param  string[]  $accepted
     * @test
     * @dataProvider  acceptedMimetypes
     */
    public function doesNotHaveSharedAcceptablesForEmptyList(array $accepted): void
    {
        assertFalse($this->acceptHeader->hasSharedAcceptables($accepted));
    }

    /**
     * @param  string[]  $accepted
     * @test
     * @dataProvider  acceptedMimetypes
     */
    public function sharedAcceptablesForEmptyListReturnsEmptyArray(array $accepted): void
    {
        assertEmptyArray($this->acceptHeader->sharedAcceptables($accepted));
    }

    /**
     * @param  string[]  $accepted
     * @test
     * @dataProvider  acceptedMimetypes
     */
    public function doesNotHaveSharedAcceptablesForNonEqualLists(array $accepted): void
    {
        assertFalse(
                $this->acceptHeader->addAcceptable('text/html')
                        ->hasSharedAcceptables($accepted)
        );
    }

    /**
     * @param  string[]  $accepted
     * @test
     * @dataProvider  acceptedMimetypes
     */
    public function sharedAcceptablesForNonEqualListsReturnsEmptyArray(array $accepted): void
    {
        assertEmptyArray(
                $this->acceptHeader->addAcceptable('text/html')
                        ->sharedAcceptables($accepted)
        );
    }

    /**
     * @test
     */
    public function hasSharedAcceptablesForCommonLists(): void
    {
        assertTrue(
                $this->acceptHeader->addAcceptable('text/plain', 0.2)
                        ->hasSharedAcceptables(['text/plain', 'text/other'])
        );
    }

    /**
     * @test
     */
    public function sharedAcceptablesForCommonListsReturnsArrayWithSharedOnes(): void
    {
        assertThat(
                $this->acceptHeader->addAcceptable('text/plain', 0.2)
                        ->sharedAcceptables(['text/plain', 'text/other']),
                equals(['text/plain'])
        );
    }

    /**
     * @test
     */
    public function findMatchWithGreatestPriorityFromEmptyListReturnsNull(): void
    {
        assertNull(
                $this->acceptHeader->findMatchWithGreatestPriority([
                        'text/plain',
                        'text/other'
                ])
        );
    }

    /**
     * @test
     */
    public function findMatchWithGreatestPriorityFromAcceptedEmptyListReturnsNull(): void
    {
        assertNull(
                $this->acceptHeader->addAcceptable('text/plain', 0.2)
                        ->addAcceptable('text/html')
                        ->findMatchWithGreatestPriority([])
        );
    }

    /**
     * @test
     */
    public function findMatchWithGreatestPriorityForNonMatchingListsReturnsNull(): void
    {
        assertNull(
                $this->acceptHeader->addAcceptable('text/plain', 0.2)
                        ->addAcceptable('text/html')
                        ->findMatchWithGreatestPriority(['text/foo', 'text/other'])
        );
    }

    /**
     * @test
     */
    public function findMatchWithGreatestPriorityForMatchingListsAcceptableWithGreatestPriority(): void
    {
        assertThat(
                $this->acceptHeader->addAcceptable('text/plain', 0.2)
                        ->addAcceptable('text/html')
                        ->findMatchWithGreatestPriority(['text/html', 'text/other']),
                equals('text/html')
        );
    }

    /**
     * @test
     */
    public function findMatchWithGreatestPriorityWithNonSharedAcceptablesButGeneralAllowedAcceptable(): void
    {
        assertThat(
                $this->acceptHeader->addAcceptable('*/*', 0.2)
                        ->addAcceptable('text/html')
                        ->findMatchWithGreatestPriority([
                                'application/json',
                                'text/other'
                        ]),
                equals('application/json')
        );
    }

    /**
     * @test
     */
    public function findMatchWithGreatestPriorityWithNonSharedAcceptablesButMainTypeAllowedAcceptable(): void
    {
        assertThat(
                $this->acceptHeader->addAcceptable('text/*', 0.2)
                        ->addAcceptable('text/html')
                        ->findMatchWithGreatestPriority([
                                'application/json',
                                'text/other'
                ]),
                equals('text/other')
        );
    }
}
