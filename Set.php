<?php

namespace JesseSchalken;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Copy of java.utils.Set implementing ArrayAccess, Countable and IteratorAggregate with O(1) conversion to/from array
 * keys. Only integers and strings (keys of an array) are supported.
 *
 * ArrayAccess is implemented as follows:
 *
 * - `$set[$value]`          is the same as   `$set->contains($value)`
 * - `$set[$value] = true`   is the same as   `$set->add($value)`
 * - `$set[$value] = false`  is the same as   `$set->remove($value)`
 * - `isset($set[$value])`   is the same an   _error_
 * - `unset($set[$value])`   is the same an   _error_
 *
 * IteratorAggregate::getIterator() returns an iterator producing the elements as values and 0,1,2...n as keys.
 *
 * @link https://docs.oracle.com/javase/7/docs/api/java/util/Set.html
 */
class Set implements ArrayAccess, Countable, IteratorAggregate {
    /**
     * @param (array|Traversable)[] $sets
     * @return self
     */
    public static function unionAll(array $sets) {
        $self = new self();
        foreach ($sets as $set) {
            $self->addAll($set);
        }
        return $self;
    }

    /**
     * @param array|Traversable $a
     * @param array|Traversable $b
     * @return self
     */
    public static function intersect($a, $b) {
        $a = new self($a);
        $a->retainAll($b);
        return $a;
    }

    /**
     * _O(1)_ Create a set from the keys of an array. Note that `$array === Set::fromArrayKeys($array)->toArrayKeys()`.
     * @param array $array
     * @return Set
     */
    public static function fromArrayKeys(array $array) {
        $set      = new self();
        $set->set = $array;
        return $set;
    }

    /**
     * @param array|Traversable $t
     * @return array
     */
    private static function toKeys($t) {
        if ($t instanceof self) {
            return $t->set;
        } else if (is_array($t)) {
            return array_fill_keys($t, true);
        } else if ($t instanceof Traversable) {
            $s = array();
            foreach ($t as $v) {
                $s[$v] = true;
            }
            return $s;
        } else {
            $type = is_object($t) ? get_class($t) : gettype($t);
            throw new \InvalidArgumentException("Cannot use a '$type' as a set");
        }
    }

    /** @var array */
    private $set;

    /**
     * _O(n)_ Create a set.
     * @param array|Traversable $contents
     */
    public function __construct($contents = array()) {
        $this->set = self::toKeys($contents);
    }

    #region java.util.Set

    /**
     * _O(1)_ Adds the specified value to the set, if not already present.
     * @param int|string $e
     * @return void
     */
    public function add($e) {
        $this[$e] = true;
    }

    /**
     * _O(n)_ Set union. Adds all the values in the specified array or Traversable to the set, if not already present.
     * @param array|Traversable $c
     * @return void
     */
    public function addAll($c) {
        $this->set = array_replace($this->set, self::toKeys($c));
    }

    /**
     * _O(1)_ Removes all of the values from the set.
     * @return void
     */
    public function clear() {
        $this->set = array();
    }

    /**
     * _O(1)_ Returns true if this set contains the specified value.
     * @param int|string $e
     * @return bool
     */
    public function contains($e) {
        return $this[$e];
    }

    /**
     * _O(n)_ Returns true if this set contains all of the values of the specified array or Traversable.
     * @param array|Traversable $c
     * @return bool
     */
    public function containsAll($c) {
        return !array_diff_key(self::toKeys($c), $this->set);
    }

    /**
     * _O(n)_ Returns true if this set contains exactly the values of the specified array or Traversable.
     * @param array|Traversable $c
     * @return bool
     */
    public function equals($c) {
        $c = self::toKeys($c);
        return
            !array_diff_key($this->set, $c) &&
            !array_diff_key($c, $this->set);
    }

    /**
     * _O(1)_ Return true if this set is empty.
     * @return bool
     */
    public function isEmpty() {
        return !$this->set;
    }

    /**
     * Returns an iterator producing the elements as values and integers 0,1,2...n as keys.
     * @return \Iterator
     */
    public function iterator() {
        return $this->getIterator();
    }

    /**
     * _O(1)_ Removes the specified value from the set, if present.
     * @param int|string $e
     * @return void
     */
    public function remove($e) {
        $this[$e] = false;
    }

    /**
     * _O(n)_ Set difference. Removes all of the values from the specified array or Traversable from this set, if present.
     * @param array|Traversable $c
     * @return void
     */
    public function removeAll($c) {
        $this->set = array_diff_key($this->set, self::toKeys($c));
    }

    /**
     * _O(n)_ Set intersection. Removes all of the values *not* in the specified array or Traversable from this set.
     * @param array|Traversable $c
     * @return void
     */
    public function retainAll($c) {
        $this->set = array_intersect_key($this->set, self::toKeys($c));
    }

    /**
     * _O(1)_ Returns the number of values in this set.
     * @return int
     */
    public function size() {
        return $this->count();
    }

    /**
     * _O(n)_ Returns an array containing the elements of this set as values.
     * @return array
     */
    public function toArray() {
        return array_keys($this->set);
    }

    #endregion

    /**
     * _O(1)_ Returns an array containing the elements of this set as keys. The value may be anything (even null) and
     * you should *not* depend on what is used as the value.
     * @return array
     */
    public function toArrayKeys() {
        return $this->set;
    }

    #region ArrayAccess

    /**
     * _O(1)_
     * @param int|string $key
     * @return bool
     */
    public function offsetGet($key) {
        return array_key_exists($key, $this->set);
    }

    /**
     * _O(1)_
     * @param int|string $key
     * @param bool       $value
     * @return void
     */
    public function offsetSet($key, $value) {
        if ($value) {
            $this->set[$key] = true;
        } else {
            unset($this->set[$key]);
        }
    }

    /**
     * @param mixed $key
     * @return bool|void
     * @throws \BadMethodCallException
     * @deprecated
     */
    public function offsetExists($key) {
        throw new \BadMethodCallException(__METHOD__ . ' is not supported');
    }

    /**
     * @param mixed $key
     * @throws \BadMethodCallException
     * @deprecated
     */
    public function offsetUnset($key) {
        throw new \BadMethodCallException(__METHOD__ . ' is not supported');
    }

    #endregion

    #region IteratorAggregate

    public function getIterator() {
        return new ArrayKeyIterator($this->set);
    }

    #endregion

    #region Countable

    /**
     * _O(1)_ Returns the number of values in the set.
     * @return int
     */
    public function count() {
        return count($this->set);
    }

    #endregion
}

class ArrayKeyIterator extends \ArrayIterator {
    private $i = 0;

    public function current() {
        return parent::key();
    }

    public function next() {
        parent::next();
        $this->i++;
    }

    public function key() {
        return $this->i;
    }

    public function rewind() {
        parent::rewind();
        $this->i = 0;
    }
}

