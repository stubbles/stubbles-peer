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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\assertThat;
use function bovigo\assert\assertEmptyString;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertNull;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
/**
 * Test for stubbles\peer\QueryString.
 */
#[Group('peer')]
class QueryStringTest extends TestCase
{
    private QueryString $emptyQueryString;
    private QueryString $prefilledQueryString;

    protected function setUp(): void
    {
        $this->emptyQueryString     = new QueryString();
        $this->prefilledQueryString = new QueryString(
            'foo.hm=bar&baz[dummy]=blubb&baz[]=more&empty=&set'
        );
    }

    #[Test]
    public function constructorThrowsIllegalArgumentExceptionIfQueryStringContainsErrors(): void
    {
        expect(function(): never {
            new QueryString('foo.hm=bar&baz[dummy]=blubb&baz[=more&empty=&set');
        })->throws(InvalidArgumentException::class);
    }

    #[Test]
    public function emptyHasNoParametersByDefault(): void
    {
        assertFalse($this->emptyQueryString->hasParams());
    }

    #[Test]
    public function prefilledHasParametersFromInitialQueryString(): void
    {
        assertTrue($this->prefilledQueryString->hasParams());
    }

    /**
     * @return  array<array<mixed>>
     */
    public static function parsedParameters(): array
    {
        return [
                ['foo.hm', 'bar'],
                ['baz', ['dummy' => 'blubb', 'more']],
                ['empty', ''],
                ['set', null]
        ];
    }

    #[Test]
    #[DataProvider('parsedParameters')]
    public function parsedParametersAreCorrect(string $paramName, mixed $expectedValue): void
    {
        assertThat(
            $this->prefilledQueryString->param($paramName),
            equals($expectedValue)
        );
    }

    #[Test]
    public function buildEmptQueryStringReturnsEmptyString(): void
    {
        assertEmptyString($this->emptyQueryString->build());
    }

    #[Test]
    public function buildNonEmptQueryStringReturnsString(): void
    {
        assertThat(
                $this->prefilledQueryString->build(),
                equals('foo.hm=bar&baz[dummy]=blubb&baz[]=more&empty=&set')
        );
    }

    #[Test]
    public function checkForNonExistingParamReturnsFalse(): void
    {
        assertFalse($this->emptyQueryString->containsParam('doesNotExist'));
    }

    #[Test]
    public function checkForExistingParamReturnsTrue(): void
    {
        assertTrue($this->prefilledQueryString->containsParam('foo.hm'));
    }

    #[Test]
    public function checkForExistingEmptyParamReturnsTrue(): void
    {
        assertTrue($this->prefilledQueryString->containsParam('empty'));
    }

    #[Test]
    public function checkForExistingNullValueParamReturnsTrue(): void
    {
        assertTrue($this->prefilledQueryString->containsParam('set'));
    }

    #[Test]
    public function getNonExistingParamReturnsNullByDefault(): void
    {
        assertNull($this->emptyQueryString->param('doesNotExist'));
    }

    #[Test]
    public function getNonExistingParamReturnsDefaultValue(): void
    {
        assertThat(
            $this->emptyQueryString->param('doesNotExist', 'example'),
            equals('example')
        );
    }

    #[Test]
    public function getExistingParamReturnsValue(): void
    {
        assertThat($this->prefilledQueryString->param('foo.hm'), equals('bar'));
    }

    #[Test]
    public function removeNonExistingParamDoesNothing(): void
    {
        assertThat(
            $this->prefilledQueryString->removeParam('doesNotExist')->build(),
            equals('foo.hm=bar&baz[dummy]=blubb&baz[]=more&empty=&set')
        );
    }

    #[Test]
    public function removeExistingEmptyParam(): void
    {
        assertThat(
            $this->prefilledQueryString->removeParam('empty')->build(),
            equals('foo.hm=bar&baz[dummy]=blubb&baz[]=more&set')
        );
    }

    #[Test]
    public function removeExistingNullValueParam(): void
    {
        assertThat(
            $this->prefilledQueryString->removeParam('set')->build(),
            equals('foo.hm=bar&baz[dummy]=blubb&baz[]=more&empty=')
        );
    }

    #[Test]
    public function removeExistingArrayParam(): void
    {
        assertThat(
            $this->prefilledQueryString->removeParam('baz')->build(),
            equals('foo.hm=bar&empty=&set')
        );
    }

    #[Test]
    public function addIllegalParamThrowsIllegalArgumentException(): void
    {
        expect(function(): never {
            $this->emptyQueryString->addParam('some', new \stdClass());
        })->throws(InvalidArgumentException::class);
    }

    /**
     * @since  5.3.1
     */
    #[Test]
    public function allowsToAddObjectWithToStringMethodAsParam(): void
    {
        assertThat(
            $this->emptyQueryString->addParam(
                'some',
                new IpAddress('127.0.0.1')
            )->build(),
            equals('some=127.0.0.1')
        );
    }

    #[Test]
    public function addNullValueAddsParamNameOnly(): void
    {
        assertThat(
            $this->emptyQueryString->addParam('some', null)->build(),
            equals('some')
        );
    }

    #[Test]
    public function addEmptyValueAddsParamNameAndEqualsign(): void
    {
        assertThat(
            $this->emptyQueryString->addParam('some', '')->build(),
            equals('some=')
        );
    }

    #[Test]
    public function addValueAddsParamNameWithValue(): void
    {
        assertThat(
            $this->emptyQueryString->addParam('some', 'bar')->build(),
            equals('some=bar')
        );
    }

    #[Test]
    public function addArrayAddsParam(): void
    {
        assertThat(
            $this->emptyQueryString->addParam(
                'some', ['foo' => 'bar', 'baz']
            )->build(),
            equals('some[foo]=bar&some[]=baz')
        );
    }

    #[Test]
    public function addFalseValueTranslatesFalseTo0(): void
    {
        assertThat(
            $this->emptyQueryString->addParam('some', false)->build(),
            equals('some=0')
        );
    }

    #[Test]
    public function addTrueValueTranslatesFalseTo1(): void
    {
        assertThat(
            $this->emptyQueryString->addParam('some', true)->build(),
            equals('some=1')
        );
    }

    /**
     * @since  7.0.0
     */
    #[Test]
    public function canBeCastedToString(): void
    {
        assertThat(
            (string) $this->prefilledQueryString,
            equals('foo.hm=bar&baz[dummy]=blubb&baz[]=more&empty=&set')
        );
    }

    /**
     * @since  9.0.2
     */
    #[Test]
    #[Group('typeerror_urldecode')]
    public function weirdQueryStringDoesNotThrowErrors(): void
    {
        expect(function() {
            new QueryString('/core.users.UserLogin/PHPSESSID/3685f296713d2352ba34f3bab22d9cee/redirect%5B%5D/users/redirect%5B%5D/1');
        })->doesNotThrow();
    }

    /**
     * @since  9.0.2
     */
    #[Test]
    #[Group('typeerror_urldecode')]
    public function weirdQueryStringIsNotBrokenIntoArrayDespiteArraySyntaxInString(): void
    {
        $q = new QueryString('/core.users.UserLogin/PHPSESSID/3685f296713d2352ba34f3bab22d9cee/redirect%5B%5D/users/redirect%5B%5D/1');
        assertThat(
            (string) $q,
            equals('%2Fcore.users.UserLogin%2FPHPSESSID%2F3685f296713d2352ba34f3bab22d9cee%2Fredirect%5B%5D%2Fusers%2Fredirect%5B%5D%2F1')
        );
    }
}
