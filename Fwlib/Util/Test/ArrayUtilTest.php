<?php
namespace Fwlib\Util\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\ArrayUtil;

/**
 * Test for Fwlib\Util\ArrayUtil
 *
 * @package     Fwlib\Util\Test
 * @copyright   Copyright 2009-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2010-01-25
 */
class ArrayUtilTest extends PHPunitTestCase
{
    public function testGetEdx()
    {
        $ar = array('foo' => '', 'foo1' => 42);

        $this->assertEquals('', ArrayUtil::getIdx($ar, 'foo'));
        $this->assertEquals(null, ArrayUtil::getEdx($ar, 'foo'));

        // With default value
        $this->assertEquals('bar', ArrayUtil::getEdx($ar, 'foo', 'bar'));
        $this->assertEquals(42, ArrayUtil::getEdx($ar, 'foo1', 'bar'));
    }


    public function testGetIdx()
    {
        $ar = array('foo' => 'bar');

        $this->assertEquals('bar', ArrayUtil::getIdx($ar, 'foo'));
        $this->assertEquals(null, ArrayUtil::getIdx($ar, 'foo1'));

        // With default value
        $this->assertEquals('bar', ArrayUtil::getIdx($ar, 'foo1', 'bar'));
    }


    public function testIncreaseByKey()
    {
        $ar = array();
        ArrayUtil::increaseByKey($ar, 'a', 3);
        ArrayUtil::increaseByKey($ar, 'a', 4);
        $this->assertEquals($ar['a'], 7);
        ArrayUtil::increaseByKey($ar, 'a');
        $this->assertEquals($ar['a'], 8);

        ArrayUtil::increaseByKey($ar, 'b', 3);
        ArrayUtil::increaseByKey($ar, 'b', '4');
        $this->assertEquals($ar['b'], '34');

        ArrayUtil::increaseByKey($ar, 42, 2);
        $this->assertEquals($ar[42], 2);
        ArrayUtil::increaseByKey($ar, 42, 2);
        $this->assertEquals($ar[42], 4);
    }


    public function testInsert()
    {
        $ar_srce = array('a', 'b', 'c');
        $x = $ar_srce;

        // Empty input
        $this->assertEqualArray(
            $ar_srce,
            ArrayUtil::insert($x, 'foo', array())
        );

        // Pos not exists, number indexed
        $x = ArrayUtil::insert($x, 'd', array('d'));
        $this->assertEqualArray($x, array('a', 'b', 'c', 'd'));

        // Pos not exists, assoc indexed
        $ar_srce = array(
            'a' => 1,
            'b' => 2,
            'c' => 3,
        );
        $x = $ar_srce;
        $y = array(
            'a' => 1,
            'b' => 2,
            'c' => 3,
            0 => 'd',
        );
        $x = ArrayUtil::insert($x, 'd', array('d'));
        $this->assertEqualArray($x, $y);

        // Assoc indexed, normal
        $ar_srce = array(
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'd' => 4,
            'e' => 5,
        );
        $ar_ins = array(
            'ins1'  => 'ins1',
            'ins2'  => 'ins2',
        );
        // Insert before a key
        $x = $ar_srce;
        $y = array(
            'a' => 1,
            'ins1'  => 'ins1',
            'ins2'  => 'ins2',
            'b' => 2,
            'c' => 3,
            'd' => 4,
            'e' => 5,
        );
        ArrayUtil::insert($x, 'c', $ar_ins, -2);
        $this->assertEqualArray($x, $y);

        // Insert after a key
        $x = $ar_srce;
        $y = array(
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'd' => 4,
            'ins1'  => 'ins1',
            'ins2'  => 'ins2',
            'e' => 5,
        );
        ArrayUtil::insert($x, 'c', $ar_ins, 2);
        $this->assertEqualArray($x, $y);

        // Replace
        $x = $ar_srce;
        $y = array(
            'ins1'  => 'ins1',
            'ins2'  => 'ins2',
            'b' => 2,
            'c' => 3,
            'd' => 4,
            'e' => 5,
        );
        ArrayUtil::insert($x, 'a', $ar_ins, 0);
        $this->assertEqualArray($x, $y);

        // Replace & not exist = append
        $x = $ar_srce;
        $y = array(
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'd' => 4,
            'e' => 5,
            'ins1'  => 'ins1',
            'ins2'  => 'ins2',
        );
        ArrayUtil::insert($x, 'f', $ar_ins, 0);
        $this->assertEqualArray($x, $y);

        // Insert far before
        $x = $ar_srce;
        $y = array(
            'ins1'  => 'ins1',
            'ins2'  => 'ins2',
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'd' => 4,
            'e' => 5,
        );
        ArrayUtil::insert($x, 'a', $ar_ins, -10);
        $this->assertEqualArray($x, $y);

        // Insert far after
        $x = $ar_srce;
        $y = array(
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'd' => 4,
            'e' => 5,
            'ins1'  => 'ins1',
            'ins2'  => 'ins2',
        );
        ArrayUtil::insert($x, 'e', $ar_ins, 10);
        $this->assertEqualArray($x, $y);
    }


    public function testSearchByWildcard()
    {
        // Empty check
        $this->assertEquals(array(), ArrayUtil::searchByWildcard(null, null));

        $x = array('foo' => 'bar');
        $y = ArrayUtil::searchByWildcard($x, null);
        $this->assertEqualArray($x, $y);
        $y = ArrayUtil::searchByWildcard($x, '|', '|');
        $this->assertEqualArray($x, $y);


        $rule = 'a*, -*b, -??c, +?d*';
        $arSrce = array(
            'a' => 'ab',
            'b' => 'abc',
            'c' => 'adc',
        );
        $ar = ArrayUtil::searchByWildcard($arSrce, $rule);
        $this->assertEquals($ar, array('c' => 'adc'));
    }


    public function testSortByLevel2()
    {
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
        ArrayUtil::sortByLevel2($ar, 'col', 'ASC');
        $this->assertEqualArray($ar, $y);

        unset($x['c']['col']);
        $y = array(
            'b' => array('col' => 30),
            'c' => array(),
            'a' => array('col' => 20),
        );
        $ar = $x;
        ArrayUtil::sortByLevel2($ar, 'col', false, 25);
        $this->assertEqualArray($ar, $y);


        return;
        // Benchmark compare with array_multisort
        // @link http://php.net/manual/en/function.array-multisort.php

        $x = array(
            'a' => array('volume' => 67, 'edition' => 2),
            'b' => array('volume' => 86, 'edition' => 1),
            'c' => array('volume' => 85, 'edition' => 6),
            'd' => array('volume' => 98, 'edition' => 2),
            'e' => array('volume' => 86, 'edition' => 6),
            'f' => array('volume' => 67, 'edition' => 7),
        );
        $y = array(
            'd' => array('volume' => 98, 'edition' => 2),
            'b' => array('volume' => 86, 'edition' => 1),
            'e' => array('volume' => 86, 'edition' => 6),
            'c' => array('volume' => 85, 'edition' => 6),
            'a' => array('volume' => 67, 'edition' => 2),
            'f' => array('volume' => 67, 'edition' => 7),
        );


        $t1 = microtime(true);
        $j = 100;
        echo "\n";

        for ($i = 0; $i < $j; $i ++) {
            $ar = $x;
            ArrayUtil::sortByLevel2($ar, 'volume', 'DESC');
        }
        $t2 = microtime(true);
        echo 'sortByLevel2()    cost ' . ($t2 - $t1) . ' seconds.' . "\n";

        for ($i = 0; $i < $j; $i ++) {
            $ar = $x;
            $volume = array();
            foreach ($ar as $k => $v) {
                $volume[$k] = $v['volume'];
            }
            array_multisort($volume, SORT_DESC, $ar);
        }
        $this->assertEqualArray($ar, $y);
        $t3 = microtime(true);
        echo 'array_multisort() cost ' . ($t3 - $t2) . ' seconds.' . "\n";
    }
}