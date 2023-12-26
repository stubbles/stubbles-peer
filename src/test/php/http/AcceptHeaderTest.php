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
 */
#[Group('peer')]
#[Group('peer_http')]
class AcceptHeaderTest extends TestCase
{
    private AcceptHeader $acceptHeader;

    protected function setUp(): void
    {
        $this->acceptHeader = new AcceptHeader();
    }

    /**
     * @since  4.0.0
     */
    #[Test]
    public function emptyAcceptHeaderReturnsInstanceWithoutAcceptables(): void
    {
        assertEmpty(emptyAcceptHeader());
    }

    #[Test]
    public function addAcceptableIncreasesCount(): void
    {
        assertThat($this->acceptHeader->addAcceptable('text/plain'), isOfSize(1));
    }

    /**
     * @return  array<mixed[]>
     */
    public static function provider(): array
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
     * @param  array<mixed>  $expectedList
     */
    #[Test]
    #[DataProvider('provider')]
    public function parseYieldsCorrectValues(string $parseValue, array $expectedList): void
    {
        $acceptHeader = AcceptHeader::parse($parseValue);
        foreach ($expectedList as $mimeType => $priority) {
            assertTrue($acceptHeader->hasSharedAcceptables([$mimeType]));
            assertThat($acceptHeader->priorityFor($mimeType), equals($priority));
        }
    }

    /**
     * @param  array<mixed>  $expectedList
     */
    #[Test]
    #[DataProvider('provider')]
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

    #[Test]
    public function addAcceptableWithPriorityLowerThan0ThrowsIllegalArgumentException(): void
    {
        expect(function() {
                $this->acceptHeader->addAcceptable('text/html', -0.1);
        })->throws(\InvalidArgumentException::class);
    }

    #[Test]
    public function addAcceptableWithPriorityGreaterThan1ThrowsIllegalArgumentException(): void
    {
        expect(function() {
                $this->acceptHeader->addAcceptable('text/html', 1.1);
        })->throws(\InvalidArgumentException::class);
    }

    #[Test]
    public function priorityOnEmptyListReturnsPriorityOf1ForEachAcceptable(): void
    {
        assertThat($this->acceptHeader->priorityFor('text/html'), equals(1.0));
    }

    #[Test]
    public function priorityForNonExistingAcceptableReturns0(): void
    {
        assertThat(
                $this->acceptHeader->addAcceptable('text/plain')
                        ->priorityFor('text/html'),
                equals(0)
        );
    }

    #[Test]
    public function priorityForNonExistingAcceptableReturnsPriorityForGeneralAcceptableIfThisIsInList(): void
    {
        assertThat(
                $this->acceptHeader->addAcceptable('*/*')
                        ->priorityFor('text/html'),
                equals(1.0)
        );
    }

    #[Test]
    public function priorityForNonExistingAcceptableReturnsPriorityForMainTypeAcceptableIfThisIsInList(): void
    {
        assertThat(
                $this->acceptHeader->addAcceptable('text/plain')
                        ->addAcceptable('text/*', 0.5)
                        ->priorityFor('text/html'),
                equals(0.5)
        );
    }

    #[Test]
    public function priorityForExistingAcceptableReturnsItsPriority(): void
    {
        assertThat(
                $this->acceptHeader->addAcceptable('text/html')
                        ->addAcceptable('text/plain', 0.2)
                        ->priorityFor('text/plain'),
                equals(0.2)
        );
    }

    #[Test]
    public function findAcceptableWithGreatestPriorityForEmptyListReturnsNull(): void
    {
        assertNull($this->acceptHeader->findAcceptableWithGreatestPriority());
    }

    #[Test]
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
    public static function acceptedMimetypes(): array
    {
        return [
                'empty list'  => [[]],
                'filled list' => [['text/plain']]
        ];
    }

    /**
     * @param  string[]  $accepted
     */
    #[Test]
    #[DataProvider('acceptedMimetypes')]
    public function doesNotHaveSharedAcceptablesForEmptyList(array $accepted): void
    {
        assertFalse($this->acceptHeader->hasSharedAcceptables($accepted));
    }

    /**
     * @param  string[]  $accepted
     */
    #[Test]
    #[DataProvider('acceptedMimetypes')]
    public function sharedAcceptablesForEmptyListReturnsEmptyArray(array $accepted): void
    {
        assertEmptyArray($this->acceptHeader->sharedAcceptables($accepted));
    }

    /**
     * @param  string[]  $accepted
     */
    #[Test]
    #[DataProvider('acceptedMimetypes')]
    public function doesNotHaveSharedAcceptablesForNonEqualLists(array $accepted): void
    {
        assertFalse(
                $this->acceptHeader->addAcceptable('text/html')
                        ->hasSharedAcceptables($accepted)
        );
    }

    /**
     * @param  string[]  $accepted
     */
    #[Test]
    #[DataProvider('acceptedMimetypes')]
    public function sharedAcceptablesForNonEqualListsReturnsEmptyArray(array $accepted): void
    {
        assertEmptyArray(
                $this->acceptHeader->addAcceptable('text/html')
                        ->sharedAcceptables($accepted)
        );
    }

    #[Test]
    public function hasSharedAcceptablesForCommonLists(): void
    {
        assertTrue(
                $this->acceptHeader->addAcceptable('text/plain', 0.2)
                        ->hasSharedAcceptables(['text/plain', 'text/other'])
        );
    }

    #[Test]
    public function sharedAcceptablesForCommonListsReturnsArrayWithSharedOnes(): void
    {
        assertThat(
                $this->acceptHeader->addAcceptable('text/plain', 0.2)
                        ->sharedAcceptables(['text/plain', 'text/other']),
                equals(['text/plain'])
        );
    }

    #[Test]
    public function findMatchWithGreatestPriorityFromEmptyListReturnsNull(): void
    {
        assertNull(
                $this->acceptHeader->findMatchWithGreatestPriority([
                        'text/plain',
                        'text/other'
                ])
        );
    }

    #[Test]
    public function findMatchWithGreatestPriorityFromAcceptedEmptyListReturnsNull(): void
    {
        assertNull(
                $this->acceptHeader->addAcceptable('text/plain', 0.2)
                        ->addAcceptable('text/html')
                        ->findMatchWithGreatestPriority([])
        );
    }

    #[Test]
    public function findMatchWithGreatestPriorityForNonMatchingListsReturnsNull(): void
    {
        assertNull(
                $this->acceptHeader->addAcceptable('text/plain', 0.2)
                        ->addAcceptable('text/html')
                        ->findMatchWithGreatestPriority(['text/foo', 'text/other'])
        );
    }

    #[Test]
    public function findMatchWithGreatestPriorityForMatchingListsAcceptableWithGreatestPriority(): void
    {
        assertThat(
                $this->acceptHeader->addAcceptable('text/plain', 0.2)
                        ->addAcceptable('text/html')
                        ->findMatchWithGreatestPriority(['text/html', 'text/other']),
                equals('text/html')
        );
    }

    #[Test]
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

    #[Test]
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
