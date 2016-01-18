<?php

namespace JesseSchalken;

class SetTest extends \PHPUnit_Framework_TestCase {
    public function testCreate() {
        $set = new Set(['a', 'b']);
        $this->assertTrue($set->contains('a'));
        $this->assertTrue($set->contains('b'));
        $this->assertFalse($set->contains('c'));
        $this->assertEquals(['a', 'b'], $set->toArray());
    }

    public function testCreateTraversable() {
        $set = new Set(new \ArrayIterator([10, 11]));
        $this->assertTrue($set->containsAll([11, 10]));
        $this->assertFalse($set->containsAll([-9, 11, 10]));
    }

    public function testCreateCopy() {
        $set1 = new Set();
        $set1->add('an string');
        $set1->add('100');

        // Copy via constructor
        $set2 = new Set($set1);
        $this->assertEquals($set2->toArray(), ['an string', 100]);

        // Copy using to/from array keys
        $set3 = Set::fromArrayKeys($set2->toArrayKeys());
        $this->assertEquals($set3->toArray(), ['an string', 100]);

        // Just make sure they're not the same object before testing equals()
        $this->assertFalse($set1 === $set2);
        $this->assertFalse($set2 === $set3);

        $this->assertTrue($set1->equals($set2));
        $this->assertTrue($set2->equals($set3));
        $this->assertFalse($set1->equals([]));
        $this->assertFalse($set1->equals(['lol']));
    }

    public function testAddAll() {
        $set1 = new Set(['lol']);
        $set2 = new Set(['foo']);
        $set1->addAll($set2);
        $set2->addAll($set1);
        $this->assertTrue($set1->equals($set2));
    }

    public function testClear() {
        $set1 = new Set([1, 6, 4, 3]);
        $this->assertEquals($set1->size(), 4);
        $set1->clear();
        $this->assertTrue($set1->isEmpty());
        $this->assertEquals($set1->size(), 0);
        $set1->add('foo');
        $this->assertEquals($set1->count(), 1);
    }

    public function testIterate() {
        $array = [
            'foo',
            'bar',
            'baz',
            9000,
        ];

        $set = new Set($array);
        $this->assertEquals(iterator_to_array($set), $array);
        $this->assertEquals(iterator_to_array($set->iterator()), $array);
    }

    public function testArrayAcces() {
        $set          = new Set;
        $set[0]       = true;
        $set[-900]    = true;
        $set[0]       = false;
        $set['set']   = true;
        $set['noset'] = false;
        $this->assertEquals($set->toArray(), [-900, 'set']);
    }

    public function testRemove() {
        $set1 = new Set();
        $set2 = new Set([100, 200]);
        $set1->add(200);
        $set1->add(300);
        $set2->retainAll($set1);
        $this->assertEquals($set1->toArray(), [200, 300]);
        $this->assertEquals($set2->toArray(), [200]);
        $this->assertFalse($set1->equals($set2));
        $this->assertFalse($set2->equals($set1));
        $set1->remove(300);
        $this->assertTrue($set1->equals($set2));
        $this->assertTrue($set2->equals($set1));
        $set1->removeAll($set2);
        $this->assertTrue($set1->isEmpty());
        $this->assertFalse($set2->isEmpty());
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage JesseSchalken\Set::offsetUnset is not supported
     * @expectedExceptionCode  0
     */
    public function testArrayOffsetUnset() {
        $set = new Set;
        unset($set[0]);
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage JesseSchalken\Set::offsetExists is not supported
     * @expectedExceptionCode  0
     */
    public function testArrayOffsetIsset() {
        $set = new Set;
        print isset($set[0]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode  0
     * @expectedExceptionMessage  Cannot use a 'stdClass' as a set
     */
    public function testInvalidSet() {
        $set      = new \stdClass();
        $set->foo = 1;
        new Set($set);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode  0
     * @expectedExceptionMessage  Cannot use a 'integer' as a set
     */
    public function testInvalidSet2() {
        new Set(8);
    }
}
