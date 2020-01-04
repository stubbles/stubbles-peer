<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\peer;
/**
 * Query string handling.
 *
 * @internal
 */
class QueryString
{
    /**
     * parameters for uri
     *
     * @var  array<string,mixed>
     */
    protected $parameters = [];

    /**
     * constructor
     *
     * Does not use parse_str() as this breaks param names containing dots or
     * spaces.
     *
     * @param   string  $queryString
     * @throws  \InvalidArgumentException
     */
    public function __construct(string $queryString = null)
    {
        if (!empty($queryString)) {
            foreach (\explode('&', $queryString) as $param) {
                $name = $value = null;
                \sscanf($param, "%[^=]=%[^\r]", $name, $value);
                if (null === $value && \substr($param, -1) == '=') {
                    $value = '';
                }

                $name = \urldecode($name);
                if (\substr_count($name, '[') !== \substr_count($name, ']')) {
                    throw new \InvalidArgumentException('Unbalanced [] in query string');
                }
                
                if ($start = \strpos($name, '[')) {
                  $base = \substr($name, 0, $start);
                  if (!isset($this->parameters[$base])) {
                      $this->parameters[$base] = [];
                  }

                  $ptr    = &$this->parameters[$base];
                  $offset = 0;
                  do {
                    $end = \strpos($name, ']', $offset);
                    if ($start === $end - 1) {
                      $ptr = &$ptr[];
                    } else {
                      $end += \substr_count($name, '[', $start + 1, $end - $start - 1);
                      $ptr  = &$ptr[\substr($name, $start + 1, $end - $start - 1)];
                    }

                    $offset = $end + 1;
                  } while ($start = \strpos($name, '[', $offset));

                  if (null !== $value) {
                      $value = \urldecode($value);
                  }

                  $ptr = $value;
                } elseif (null !== $value) {
                    $this->parameters[$name] = \urldecode($value);
                } else {
                    $this->parameters[$name] = null;
                }
            }
        }
    }

    /**
     * build the query from parameters
     *
     * @return  string
     */
    public function build(): string
    {
        if (\count($this->parameters) === 0) {
            return '';
        }

        $queryString = '';
        foreach ($this->parameters as $name => $value) {
            $queryString .= $this->buildQuery($name, $value);
        }

        return \substr($queryString, 1);
    }

    /**
     * Calculates query string
     *
     * @param   string  $name
     * @param   mixed   $value
     * @param   string  $postfix  The postfix to use for each variable (defaults to '')
     * @return  string
     */
    protected function buildQuery(string $name, $value, string $postfix= ''): string
    {
        $query = '';
        if (\is_array($value)) {
            foreach ($value as $k => $v) {
                if (\is_int($k)) {
                    $query .= $this->buildQuery('', $v, $postfix . $name .'[]');
                } else {
                    $query .= $this->buildQuery('', $v, $postfix . $name . '[' . $k . ']');
                }
            }
        } elseif (null === $value) {
            $query .= '&' . \urlencode($name) . $postfix;
        } elseif (false === $value) {
            $query .= '&' . \urlencode($name) . $postfix . '=0';
        } elseif (true === $value) {
            $query .= '&' . \urlencode($name) . $postfix . '=1';
        } else {
            $query .= '&' . \urlencode($name) . $postfix . '=' . \urlencode($value);
        }

        return $query;
    }

    /**
     * checks whether query string contains any parameters
     *
     * @return  bool
     */
    public function hasParams(): bool
    {
        return (\count($this->parameters) > 0);
    }

    /**
     * add a parameter
     *
     * @param   string  $name   name of parameter
     * @param   mixed   $value  value of parameter
     * @return  \stubbles\peer\QueryString
     * @throws  \InvalidArgumentException
     */
    public function addParam(string $name, $value): self
    {
        if (!\is_array($value) && !\is_scalar($value) && null !== $value) {
            if (\is_object($value) && \method_exists($value, '__toString')) {
                $value = (string) $value;
            } else {
                throw new \InvalidArgumentException(
                        'Argument 2 passed to ' . __METHOD__ . '() must be'
                        . ' a string, array, object with __toString() method'
                        . ' or any other scalar value.'
                );
            }
        }

        $this->parameters[$name] = $value;
        return $this;
    }

    /**
     * remove a param
     *
     * @param   string  $name  name of parameter
     * @return  \stubbles\peer\QueryString
     */
    public function removeParam(string $name): self
    {
        if (\array_key_exists($name, $this->parameters)) {
            unset($this->parameters[$name]);
        }

        return $this;
    }

    /**
     * checks whether a certain param is set
     *
     * @param   string  $name
     * @return  bool
     */
    public function containsParam(string $name): bool
    {
        return \array_key_exists($name, $this->parameters);
    }

    /**
     * returns the value of a param
     *
     * @param   string  $name          name of the param
     * @param   mixed   $defaultValue  default value to return if param is not set
     * @return  mixed
     */
    public function param(string $name, $defaultValue = null)
    {
        if (\array_key_exists($name, $this->parameters)) {
            return $this->parameters[$name];
        }

        return $defaultValue;
    }

    /**
     * returns a string representation of the class
     *
     * @XmlIgnore
     * @return  string
     */
    public function __toString(): string
    {
        return $this->build();
    }
}
