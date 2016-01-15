# php-set
`java.utils.Set` for PHP implementing `ArrayAccess`, `Countable`, `IteratorAggregate` with _O(1)_ conversion to/from array keys.

Uses `array_replace()`, `array_intersect_key()` and `array_diff_key()` for fast union, intersection and difference (`addAll()`, `retainAll()`, `removeAll()`).

```php
use JesseSchalken\Set;

// Create a set from an array, Set or Traversable
$set = new Set([1, 2, 3]);
$set = new Set($set);
$set = new Set(new \ArrayIterator($set->toArray()));

// Add and remove elements
$set->add(4);
$set->remove(2);
var_export($set->contains(4)); // true
var_export($set->contains(2)); // false
$set->clear();
var_export($set->isEmpty()); // true

// Add and remove other arrays, Sets or Traversables 
$set->addAll(['red', 'green', 'blue', 54]);
$set->removeAll(new \ArrayObject(['green', 54]));
var_export($set->containsAll(new Set(['red', 'blue']))); // true
var_export($set->toArray()); // ['red', 'blue']
$set->retainAll(['blue', 'green']);
var_export($set->toArray()); // ['blue']
var_export($set->size()); // 1
var_export($set->equals(['blue'])); // true

// Convert to and from array keys
$set = new Set([1, 2]);
var_export($set->toArrayKeys()); // [1 => true, 2 => true]
$set = Set::fromArrayKeys([7 => true, 3 => 'string']);
var_export($set->toArray()); // [7, 3]

// Use ArrayAccess, IteratorAggregate and Countable
$set = new Set();
$set['red']   = true; // add
$set['green'] = true; // add
$set['red']   = false; // remove
var_export($set['green']); // true
var_export($set['blue']); // false
var_export($set['red']); // false

foreach ($set as $v) {
    var_export($v); // blue
}

var_export(count($set)); // 1
```
