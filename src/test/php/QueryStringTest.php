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
use function bovigo\assert\assertEmptyString;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertNull;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
/**
 * Test for stubbles\peer\QueryString.
 *
 * @group  peer
 */
class QueryStringTest extends TestCase
{
    /**
     * empty instance to test
     *
     * @var  QueryString
     */
    protected $emptyQueryString;
    /**
     * prefilled instance to test
     *
     * @var  QueryString
     */
    protected $prefilledQueryString;

    protected function setUp(): void
    {
        $this->emptyQueryString     = new QueryString();
        $this->prefilledQueryString = new QueryString(
                'foo.hm=bar&baz[dummy]=blubb&baz[]=more&empty=&set'
        );
    }

    /**
     * @test
     */
    public function constructorThrowsIllegalArgumentExceptionIfQueryStringContainsErrors(): void
    {
        expect(function() {
                new QueryString('foo.hm=bar&baz[dummy]=blubb&baz[=more&empty=&set');
        })->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function emptyHasNoParametersByDefault(): void
    {
        assertFalse($this->emptyQueryString->hasParams());
    }

    /**
     * @test
     */
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

    /**
     * @param  string  $paramName
     * @param  mixed   $expectedValue
     * @test
     * @dataProvider  parsedParameters
     */
    public function parsedParametersAreCorrect(string $paramName, $expectedValue): void
    {
        assertThat(
                $this->prefilledQueryString->param($paramName),
                equals($expectedValue)
        );
    }

    /**
     * @test
     */
    public function buildEmptQueryStringReturnsEmptyString(): void
    {
        assertEmptyString($this->emptyQueryString->build());
    }

    /**
     * @test
     */
    public function buildNonEmptQueryStringReturnsString(): void
    {
        assertThat(
                $this->prefilledQueryString->build(),
                equals('foo.hm=bar&baz[dummy]=blubb&baz[]=more&empty=&set')
        );
    }

    /**
     * @test
     */
    public function checkForNonExistingParamReturnsFalse(): void
    {
        assertFalse($this->emptyQueryString->containsParam('doesNotExist'));
    }

    /**
     * @test
     */
    public function checkForExistingParamReturnsTrue(): void
    {
        assertTrue($this->prefilledQueryString->containsParam('foo.hm'));
    }

    /**
     * @test
     */
    public function checkForExistingEmptyParamReturnsTrue(): void
    {
        assertTrue($this->prefilledQueryString->containsParam('empty'));
    }

    /**
     * @test
     */
    public function checkForExistingNullValueParamReturnsTrue(): void
    {
        assertTrue($this->prefilledQueryString->containsParam('set'));
    }

    /**
     * @test
     */
    public function getNonExistingParamReturnsNullByDefault(): void
    {
        assertNull($this->emptyQueryString->param('doesNotExist'));
    }

    /**
     * @test
     */
    public function getNonExistingParamReturnsDefaultValue(): void
    {
        assertThat(
                $this->emptyQueryString->param('doesNotExist', 'example'),
                equals('example')
        );
    }

    /**
     * @test
     */
    public function getExistingParamReturnsValue(): void
    {
        assertThat($this->prefilledQueryString->param('foo.hm'), equals('bar'));
    }

    /**
     * @test
     */
    public function removeNonExistingParamDoesNothing(): void
    {
        assertThat(
                $this->prefilledQueryString->removeParam('doesNotExist')->build(),
                equals('foo.hm=bar&baz[dummy]=blubb&baz[]=more&empty=&set')
        );
    }

    /**
     * @test
     */
    public function removeExistingEmptyParam(): void
    {
        assertThat(
                $this->prefilledQueryString->removeParam('empty')->build(),
                equals('foo.hm=bar&baz[dummy]=blubb&baz[]=more&set')
        );
    }

    /**
     * @test
     */
    public function removeExistingNullValueParam(): void
    {
        assertThat(
                $this->prefilledQueryString->removeParam('set')->build(),
                equals('foo.hm=bar&baz[dummy]=blubb&baz[]=more&empty=')
        );
    }

    /**
     * @test
     */
    public function removeExistingArrayParam(): void
    {
        assertThat(
                $this->prefilledQueryString->removeParam('baz')->build(),
                equals('foo.hm=bar&empty=&set')
        );
    }

    /**
     * @test
     */
    public function addIllegalParamThrowsIllegalArgumentException(): void
    {
        expect(function() {
                $this->emptyQueryString->addParam('some', new \stdClass());
        })->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     * @since  5.3.1
     */
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

    /**
     * @test
     */
    public function addNullValueAddsParamNameOnly(): void
    {
        assertThat(
                $this->emptyQueryString->addParam('some', null)->build(),
                equals('some')
        );
    }

    /**
     * @test
     */
    public function addEmptyValueAddsParamNameAndEqualsign(): void
    {
        assertThat(
                $this->emptyQueryString->addParam('some', '')->build(),
                equals('some=')
        );
    }

    /**
     * @test
     */
    public function addValueAddsParamNameWithValue(): void
    {
        assertThat(
                $this->emptyQueryString->addParam('some', 'bar')->build(),
                equals('some=bar')
        );
    }

    /**
     * @test
     */
    public function addArrayAddsParam(): void
    {
        assertThat(
                $this->emptyQueryString->addParam(
                        'some', ['foo' => 'bar', 'baz']
                )->build(),
                equals('some[foo]=bar&some[]=baz')
        );
    }

    /**
     * @test
     */
    public function addFalseValueTranslatesFalseTo0(): void
    {
        assertThat(
                $this->emptyQueryString->addParam('some', false)->build(),
                equals('some=0')
        );
    }

    /**
     * @test
     */
    public function addTrueValueTranslatesFalseTo1(): void
    {
        assertThat(
                $this->emptyQueryString->addParam('some', true)->build(),
                equals('some=1')
        );
    }

    /**
     * @test
     * @since  7.0.0
     */
    public function canBeCastedToString(): void
    {
        assertThat(
                (string) $this->prefilledQueryString,
                equals('foo.hm=bar&baz[dummy]=blubb&baz[]=more&empty=&set')
        );
    }

    /**
     * @test
     * @since  9.0.2
     * @group  typeerror_urldecode
     */
    public function weirdQueryStringDoesNotThrowErrors(): void
    {
        expect(function() {
            new QueryString('/core.users.UserLogin/PHPSESSID/3685f296713d2352ba34f3bab22d9cee/redirect%5B%5D/users/redirect%5B%5D/1');
        })->doesNotThrow();
    }

    /**
     * @test
     * @since  9.0.2
     * @group  typeerror_urldecode
     */
    public function weirdQueryStringIsNotBrokenIntoArrayDespiteArraySyntaxInString(): void
    {
        $q = new QueryString('/core.users.UserLogin/PHPSESSID/3685f296713d2352ba34f3bab22d9cee/redirect%5B%5D/users/redirect%5B%5D/1');
        assertThat(
            (string) $q,
            equals('%2Fcore.users.UserLogin%2FPHPSESSID%2F3685f296713d2352ba34f3bab22d9cee%2Fredirect%5B%5D%2Fusers%2Fredirect%5B%5D%2F1')
        );
    }
}
