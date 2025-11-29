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

/**
 * Query string handling.
 *
 * @internal
 */
class QueryString
{
    /**
     * @var  array<string,mixed>
     */
    protected array $parameters = [];

    /**
     * Does not use parse_str() as this breaks param names containing dots or
     * spaces.
     *
     * @throws  InvalidArgumentException
     */
    public function __construct(?string $queryString = null)
    {
        if (!empty($queryString)) {
            foreach (explode('&', $queryString) as $param) {
                list($name, $value) = $this->extract($param);
                $end = strpos($name, ']', -1);
                // is the last character of name a closing parenthesis, and do we have at least one
                // opening parenthesis?
                if (false !== $end && $end === strlen($name) - 1 && $start = strpos($name, '[')) {
                    $this->parseArrayParam($name, $value, $start);
                } elseif (null !== $value) {
                    $this->parameters[$name] = urldecode($value);
                } else {
                    $this->parameters[$name] = null;
                }
            }
        }
    }

    /**
     * @throws  InvalidArgumentException
     */
    private function extract(string $param): array
    {
        $name = $value = null;
        sscanf($param, "%[^=]=%[^\r]", $name, $value);
        if (null === $value && substr($param, -1) == '=') {
            $value = '';
        }

        $name = urldecode($name);
        if (substr_count($name, '[') !== substr_count($name, ']')) {
            throw new InvalidArgumentException('Unbalanced [] in query string');
        }

        return [$name, $value];
    }

    private function parseArrayParam(string $name, mixed $value, int $start)
    {
        $base = substr($name, 0, $start);
        if (!isset($this->parameters[$base])) {
            $this->parameters[$base] = [];
        }

        $ptr    = &$this->parameters[$base];
        $offset = 0;
        do {
            $end = strpos($name, ']', $offset);
            if ($start === $end - 1) {
                $ptr = &$ptr[];
            } else {
                $end += substr_count($name, '[', $start + 1, $end - $start - 1);
                $ptr  = &$ptr[substr($name, $start + 1, $end - $start - 1)];
            }

            $offset = $end + 1;
        } while ($start = strpos($name, '[', $offset));

        if (null !== $value) {
            $value = urldecode($value);
        }

        $ptr = $value;
    }

    public function build(): string
    {
        if (empty($this->parameters)) {
            return '';
        }

        $queryString = '';
        foreach ($this->parameters as $name => $value) {
            $queryString .= $this->buildQuery($name, $value);
        }

        return substr($queryString, 1);
    }

    protected function buildQuery(string $name, mixed $value, string $postfix= ''): string
    {
        $query = '';
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                if (\is_int($k)) {
                    $query .= $this->buildQuery('', $v, $postfix . $name .'[]');
                } else {
                    $query .= $this->buildQuery('', $v, $postfix . $name . '[' . $k . ']');
                }
            }
        } elseif (null === $value) {
            $query .= '&' . urlencode($name) . $postfix;
        } elseif (false === $value) {
            $query .= '&' . urlencode($name) . $postfix . '=0';
        } elseif (true === $value) {
            $query .= '&' . urlencode($name) . $postfix . '=1';
        } else {
            $query .= '&' . urlencode($name) . $postfix . '=' . urlencode($value);
        }

        return $query;
    }

    public function hasParams(): bool
    {
        return count($this->parameters) > 0;
    }

    /**
     * @throws  InvalidArgumentException
     */
    public function addParam(string $name, mixed $value): self
    {
        if (!is_array($value) && !is_scalar($value) && null !== $value) {
            if (is_object($value) && method_exists($value, '__toString')) {
                $value = (string) $value;
            } else {
                throw new InvalidArgumentException(
                    'Argument 2 passed to ' . __METHOD__ . '() must be'
                    . ' a string, array, object with __toString() method'
                    . ' or any other scalar value.'
                );
            }
        }

        $this->parameters[$name] = $value;
        return $this;
    }

    public function removeParam(string $name): self
    {
        if (\array_key_exists($name, $this->parameters)) {
            unset($this->parameters[$name]);
        }

        return $this;
    }

    public function containsParam(string $name): bool
    {
        return \array_key_exists($name, $this->parameters);
    }

    public function param(string $name, mixed $defaultValue = null): mixed
    {
        if (array_key_exists($name, $this->parameters)) {
            return $this->parameters[$name];
        }

        return $defaultValue;
    }

    /**
     * @XmlIgnore
     */
    public function __toString(): string
    {
        return $this->build();
    }
}
