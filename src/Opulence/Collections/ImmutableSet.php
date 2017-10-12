<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Collections;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use RuntimeException;
use Throwable;
use Traversable;

/**
 * Defines an immutable set
 */
class ImmutableSet implements ArrayAccess, Countable, IteratorAggregate
{
    /** @var array The set of values */
    protected $values = [];

    /**
     * @param array $values The set of values
     * @throws RuntimeException Thrown if any of the values could not be serialized
     */
    public function __construct(array $values)
    {
        try {
            foreach ($values as $value) {
                $this->values[(string)$value] = $value;
            }
        } catch (Throwable $ex) {
            throw new RuntimeException('Could not serialize value', 0, $ex);
        }
    }

    /**
     * Gets whether or not the value exists
     *
     * @param mixed $value The value to search for
     * @return bool True if the value exists, otherwise false
     * @throws RuntimeException Thrown if the value cannot be serialized
     */
    public function containsValue($value) : bool
    {
        try {
            return isset($this->values[(string)$value]);
        } catch (Throwable $ex) {
            throw new RuntimeException('Could not serialize value', 0, $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function count() : int
    {
        return count($this->values);
    }

    /**
     * @inheritdoc
     */
    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->values);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($index) : bool
    {
        throw new RuntimeException('Cannot use isset on set - use containsValue() instead');
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($index)
    {
        throw new RuntimeException('Cannot get a value from a set');
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($index, $value) : void
    {
        throw new RuntimeException('Cannot add values to an immutable sets');
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($index) : void
    {
        throw new RuntimeException('Cannot use unset on immutable sets');
    }

    /**
     * Sorts the values of the set
     *
     * @param callable $comparer The comparer to sort with
     */
    public function sort(callable $comparer) : void
    {
        usort($this->values, $comparer);
    }

    /**
     * Gets all of the values as an array
     *
     * @return array All of the values
     */
    public function toArray() : array
    {
        return array_values($this->values);
    }
}
