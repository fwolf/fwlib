<?php
namespace FwlibTest\Util;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\ArrayUtil;

/**
 * @copyright   Copyright 2009-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ArrayUtilTest extends PHPunitTestCase
{
    /**
     * @return  ArrayUtil
     */
    public function buildMock()
    {
        return new ArrayUtil;
    }


    public function testGetEdx()
    {
        $arrayUtil = $this->buildMock();

        $ar = array('foo' => '', 'foo1' => 42);

        $this->assertEquals('', $arrayUtil->getIdx($ar, 'foo'));
        $this->assertEquals(null, $arrayUtil->getEdx($ar, 'foo'));

        // With default value
        $this->assertEquals('bar', $arrayUtil->getEdx($ar, 'foo', 'bar'));
        $this->assertEquals(42, $arrayUtil->getEdx($ar, 'foo1', 'bar'));
    }


    public function testGetIdx()
    {
        $arrayUtil = $this->buildMock();

        $ar = array('foo' => 'bar');

        $this->assertEquals('bar', $arrayUtil->getIdx($ar, 'foo'));
        $this->assertEquals(null, $arrayUtil->getIdx($ar, 'foo1'));

        // With default value
        $this->assertEquals('bar', $arrayUtil->getIdx($ar, 'foo1', 'bar'));

        // Compare with array_key_exists()
        $ar = array('foo' => null);
        $this->assertFalse(isset($ar['foo']));
        $this->assertTrue(array_key_exists('foo', $ar));
        $this->assertEquals(null, $arrayUtil->getIdx($ar, 'foo', 42));
    }


    public function testIncreaseByKey()
    {
        $arrayUtil = $this->buildMock();

        $ar = array();
        $arrayUtil->increaseByKey($ar, 'a', 3);
        $arrayUtil->increaseByKey($ar, 'a', 4);
        $this->assertEquals($ar['a'], 7);
        $arrayUtil->increaseByKey($ar, 'a');
        $this->assertEquals($ar['a'], 8);

        $arrayUtil->increaseByKey($ar, 'b', 3);
        $arrayUtil->increaseByKey($ar, 'b', '4');
        $this->assertEquals($ar['b'], '34');

        $arrayUtil->increaseByKey($ar, 42, 2);
        $this->assertEquals($ar[42], 2);
        $arrayUtil->increaseByKey($ar, 42, 2);
        $this->assertEquals($ar[42], 4);
    }


    public function testInsert()
    {
        $arrayUtil = $this->buildMock();

        $x = array('a', 'b', 'c');
        $y = $x;

        // Empty input
        $this->assertEqualArray($y, $arrayUtil->insert($x, 'foo', array()));

        // Pos not exists, number indexed
        $arrayUtil->insert($x, 'd', array('d'));
        $this->assertEqualArray(array('a', 'b', 'c', 'd'), $x);

        // Pos not exists, assoc indexed
        $x = array(
            'a' => 1,
        );
        $y = array(
            'a' => 1,
            0 => 'd',
        );
        $this->assertEqualArray($y, $arrayUtil->insert($x, 'd', array('d')));

        // Assoc indexed, normal
        $source = array(
            'a' => 1,
            'b' => 2,
            'c' => 3,
        );
        $insert = array(
            'ins1'  => 'ins1',
            'ins2'  => 'ins2',
        );
        // Insert before a key
        $x = $source;
        $y = array(
            'a' => 1,
            'ins1'  => 'ins1',
            'ins2'  => 'ins2',
            'b' => 2,
            'c' => 3,
        );
        $this->assertEqualArray($y, $arrayUtil->insert($x, 'c', $insert, -2));

        // Insert after a key
        $x = $source;
        $y = array(
            'a' => 1,
            'ins1'  => 'ins1',
            'ins2'  => 'ins2',
            'b' => 2,
            'c' => 3,
        );
        $this->assertEqualArray($y, $arrayUtil->insert($x, 'a', $insert, 1));

        // Replace
        $x = $source;
        $y = array(
            'ins1'  => 'ins1',
            'ins2'  => 'ins2',
            'b' => 2,
            'c' => 3,
        );
        $this->assertEqualArray($y, $arrayUtil->insert($x, 'a', $insert, 0));

        // Replace & not exist = append
        $x = $source;
        $y = array(
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'ins1'  => 'ins1',
            'ins2'  => 'ins2',
        );
        $this->assertEqualArray($y, $arrayUtil->insert($x, 'f', $insert, 0));

        // Insert far before
        $x = $source;
        $y = array(
            'ins1'  => 'ins1',
            'ins2'  => 'ins2',
            'a' => 1,
            'b' => 2,
            'c' => 3,
        );
        $this->assertEqualArray($y, $arrayUtil->insert($x, 'a', $insert, -10));

        // Insert far after
        $x = $source;
        $y = array(
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'ins1'  => 'ins1',
            'ins2'  => 'ins2',
        );
        $this->assertEqualArray($y, $arrayUtil->insert($x, 'c', $insert, 10));
    }


    public function testPick()
    {
        $arrayUtil = $this->buildMock();

        $sources = array(
            'a' => 'A ',
            'b' => 42,
            'c' => null,
            'd' => '0',
        );


        // noEmpty
        $keys = array('a', 'b', 'c', 'd');
        $y = array(
            'a' => 'A ',
            'b' => 42,
        );
        $this->assertEqualArray($y, $arrayUtil->pick($sources, $keys, true));


        // Callback
        $callback = function ($value) {
            return is_null($value) ? 'null' : $value;
        };
        $y = array(
            'a' => 'A ',
            'b' => 42,
            'c' => 'null',
        );
        $this->assertEqualArray(
            $y,
            $arrayUtil->pick($sources, $keys, true, $callback)
        );


        // Use build-in function as callback
        $this->assertEqualArray(
            array('a' => 'A'),
            $arrayUtil->pick($sources, array('a'), false, 'trim')
        );
    }


    public function testSearchByWildcard()
    {
        $arrayUtil = $this->buildMock();

        // Empty check
        $this->assertEquals(array(), $arrayUtil->searchByWildcard(null, null));

        $x = array('foo' => 'bar');
        $y = $arrayUtil->searchByWildcard($x, null);
        $this->assertEqualArray($x, $y);
        $y = $arrayUtil->searchByWildcard($x, '|', '|');
        $this->assertEqualArray($x, $y);


        $rule = 'a*, -*b, -??c, +?d*';
        $sourceArray = array(
            'a' => 'ab',
            'b' => 'abc',
            'c' => 'adc',
        );
        $ar = $arrayUtil->searchByWildcard($sourceArray, $rule);
        $this->assertEquals($ar, array('c' => 'adc'));
    }


    public function testSortByLevel2()
    {
        $arrayUtil = $this->buildMock();

        $x = array(
            'a' => array('col' => 20),
            'b' => array('col' => 30),
            'c' => array('col' => 10),
        );
        $y = array(
            'c' => array('col' => 10),
            'a' => array('col' => 20),
            'b' => array('col' => 30),
        );

        $ar = $x;
        $arrayUtil->sortByLevel2($ar, 'col', 'ASC');
        $this->assertEqualArray($ar, $y);

        unset($x['c']['col']);
        $y = array(
            'b' => array('col' => 30),
            'c' => array(),
            'a' => array('col' => 20),
        );
        $ar = $x;
        $arrayUtil->sortByLevel2($ar, 'col', false, 25);
        $this->assertEqualArray($ar, $y);
    }
}
