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

/**
 * Class to work with all kinds of Accept* headers.
 *
 * @api
 */
class AcceptHeader implements \Countable
{
    /**
     * list of acceptables
     *
     * @var  array<string, float>
     */
    private array $acceptables = [];

    /**
     * method to create an instance from a string header value
     */
    public static function parse(string $headerValue): self
    {
        $self = new self();
        foreach (explode(',', $headerValue) as $acceptable) {
            // seems to be impossible to parse acceptables with regular
            // expressions or even scanf(), so we do some string crunching here
            if (strstr($acceptable, 'q=') !== false) {
                list($acceptable, $priority) = explode('q=', trim($acceptable));
            } else {
                $priority = 1.0;
            }

            $acceptable = trim($acceptable);
            if (substr($acceptable, -1) === ';') {
                $acceptable = substr($acceptable, 0, -1);
            }

            settype($priority, 'float');
            // cast anyway when passing argument because phpstan doesn't account for settype
            $self->addAcceptable($acceptable, (float) $priority);
        }

        return $self;
    }

    /**
     * amount of acceptables
     */
    public function count(): int
    {
        return count($this->acceptables);
    }

    /**
     * add an acceptable to the list
     *
     * @throws  InvalidArgumentException
     */
    public function addAcceptable(string $acceptable, float $priority = 1.0): self
    {
        if (0 > $priority || 1.0 < $priority) {
            throw new InvalidArgumentException(
                'Invalid priority, must be between 0 and 1.0'
            );
        }

        $this->acceptables[$acceptable] = $priority;
        return $this;
    }

    /**
     * returns priority for given acceptable
     *
     * If returned priority is 0 the requested acceptable is not in the list. In
     * case no acceptables were added before every requested acceptable has a
     * priority of 1.0.
     */
    public function priorityFor(string $mimeType): float
    {
        if (!isset($this->acceptables[$mimeType])) {
            if ($this->count() === 0) {
                return 1.0;
            } elseif (isset($this->acceptables['*/*'])) {
                return $this->acceptables['*/*'];
            }

            list($maintype) = explode('/', $mimeType);
            return $this->acceptables[$maintype . '/*'] ?? 0;
        }

        return $this->acceptables[$mimeType];
    }

    /**
     * find match with highest priority
     *
     * Checks given list of mime types if they are in the list, and returns the
     * one with the greatest priority. If return value is null none of the given
     * mime types matches any in the list.
     *
     * @param  string[]  $mimeTypes
     */
    public function findMatchWithGreatestPriority(array $mimeTypes): ?string
    {
        $sharedAcceptables = array_intersect_key(
                $this->acceptables,
                array_flip($this->sharedAcceptables($mimeTypes))
        );
        if (count($sharedAcceptables) > 0) {
            return $this->selectAcceptableWithGreatestPriority($sharedAcceptables);
        }

        foreach ($mimeTypes as $acceptable) {
            list($maintype) = explode('/', $acceptable);
            if (isset($this->acceptables[$maintype . '/*'])) {
                return $acceptable;
            }
        }

        if (isset($this->acceptables['*/*'])) {
            return array_shift($mimeTypes);
        }

        return null;
    }

    /**
     * helper method to find the acceptable with the greatest priority from a given list of acceptables
     *
     * @param  array<string,float>  $acceptables
     */
    private function selectAcceptableWithGreatestPriority(array $acceptables): ?string
    {
        arsort($acceptables);
        return array_key_first($acceptables);
    }

    /**
     * returns the acceptable with the greatest priority
     *
     * If two acceptables have the same priority the last one added wins.
     */
    public function findAcceptableWithGreatestPriority(): ?string
    {
        return $this->selectAcceptableWithGreatestPriority($this->acceptables);
    }

    /**
     * checks whether there are shares acceptables in header and given list
     *
     * @param  string[]  $acceptables
     */
    public function hasSharedAcceptables(array $acceptables): bool
    {
        return count($this->sharedAcceptables($acceptables)) > 0;
    }

    /**
     * returns a list of acceptables that are both in header and given list
     *
     * @param   string[]  $acceptables
     * @return  string[]
     */
    public function sharedAcceptables(array $acceptables): array
    {
        return array_intersect(array_keys($this->acceptables), $acceptables);
    }

    /**
     * returns current list as string
     */
    public function asString(): string
    {
        $parts = [];
        foreach ($this->acceptables as $acceptable => $priority) {
            if (1.0 === $priority) {
                $parts[] = $acceptable;
            } else {
                $parts[] = $acceptable . ';q=' . $priority;
            }
        }

        return join(',', $parts);
    }

    /**
     * returns a string representation of the class
     *
     * @XmlIgnore
     */
    public function __toString(): string
    {
        return $this->asString();
    }
}
