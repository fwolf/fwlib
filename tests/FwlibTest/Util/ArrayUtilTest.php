<?php
namespace FwlibTest\Util;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
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

        $ar = ['foo' => '', 'foo1' => 42];

        $this->assertEquals('', $arrayUtil->getIdx($ar, 'foo'));
        $this->assertEquals(null, $arrayUtil->getEdx($ar, 'foo'));

        // With default value
        $this->assertEquals('bar', $arrayUtil->getEdx($ar, 'foo', 'bar'));
        $this->assertEquals(42, $arrayUtil->getEdx($ar, 'foo1', 'bar'));
    }


    public function testGetIdx()
    {
        $arrayUtil = $this->buildMock();

        $ar = ['foo' => 'bar'];

        $this->assertEquals('bar', $arrayUtil->getIdx($ar, 'foo'));
        $this->assertEquals(null, $arrayUtil->getIdx($ar, 'foo1'));

        // With default value
        $this->assertEquals('bar', $arrayUtil->getIdx($ar, 'foo1', 'bar'));

        // Compare with array_key_exists()
        $ar = ['foo' => null];
        $this->assertFalse(isset($ar['foo']));
        $this->assertTrue(array_key_exists('foo', $ar));
        $this->assertEquals(null, $arrayUtil->getIdx($ar, 'foo', 42));
    }


    public function testIncreaseByKey()
    {
        $arrayUtil = $this->buildMock();

        $ar = [];
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

        $x = ['a', 'b', 'c'];
        $y = $x;

        // Empty input
        $this->assertEqualArray($y, $arrayUtil->insert($x, 'foo', []));

        // Pos not exists, number indexed
        $arrayUtil->insert($x, 'd', ['d']);
        $this->assertEqualArray(['a', 'b', 'c', 'd'], $x);

        // Pos not exists, assoc indexed
        $x = [
            'a' => 1,
        ];
        $y = [
            'a' => 1,
            0 => 'd',
        ];
        $this->assertEqualArray($y, $arrayUtil->insert($x, 'd', ['d']));

        // Assoc indexed, normal
        $source = [
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ];
        $insert = [
            'ins1'  => 'ins1',
            'ins2'  => 'ins2',
        ];
        // Insert before a key
        $x = $source;
        $y = [
            'a' => 1,
            'ins1'  => 'ins1',
            'ins2'  => 'ins2',
            'b' => 2,
            'c' => 3,
        ];
        $this->assertEqualArray($y, $arrayUtil->insert($x, 'c', $insert, -2));

        // Insert after a key
        $x = $source;
        $y = [
            'a' => 1,
            'ins1'  => 'ins1',
            'ins2'  => 'ins2',
            'b' => 2,
            'c' => 3,
        ];
        $this->assertEqualArray($y, $arrayUtil->insert($x, 'a', $insert, 1));

        // Replace
        $x = $source;
        $y = [
            'ins1'  => 'ins1',
            'ins2'  => 'ins2',
            'b' => 2,
            'c' => 3,
        ];
        $this->assertEqualArray($y, $arrayUtil->insert($x, 'a', $insert, 0));

        // Replace & not exist = append
        $x = $source;
        $y = [
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'ins1'  => 'ins1',
            'ins2'  => 'ins2',
        ];
        $this->assertEqualArray($y, $arrayUtil->insert($x, 'f', $insert, 0));

        // Insert far before
        $x = $source;
        $y = [
            'ins1'  => 'ins1',
            'ins2'  => 'ins2',
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ];
        $this->assertEqualArray($y, $arrayUtil->insert($x, 'a', $insert, -10));

        // Insert far after
        $x = $source;
        $y = [
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'ins1'  => 'ins1',
            'ins2'  => 'ins2',
        ];
        $this->assertEqualArray($y, $arrayUtil->insert($x, 'c', $insert, 10));
    }


    public function testInsertWithReplaceKey()
    {
        $arrayUtil = $this->buildMock();

        $source = [
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ];
        $insert = [
            'c' => 33,
        ];
        // Insert before a key
        $x = $source;
        $y = [
            'a' => 1,
            'c' => 33,
            'b' => 2,
        ];
        $this->assertEqualArray($y, $arrayUtil->insert($x, 'c', $insert, -2));
    }


    public function testPick()
    {
        $arrayUtil = $this->buildMock();

        $sources = [
            'a' => 'A ',
            'b' => 42,
            'c' => null,
            'd' => '0',
        ];


        // Key replacement
        $keys = ['paramA' => 'a', 'paramB' => 'b'];
        $y = [
            'paramA' => 'A ',
            'paramB' => 42,
        ];
        $this->assertEqualArray($y, $arrayUtil->pick($sources, $keys, true));


        // noEmpty
        $keys = ['a', 'b', 'c', 'd'];
        $y = [
            'a' => 'A ',
            'b' => 42,
        ];
        $this->assertEqualArray($y, $arrayUtil->pick($sources, $keys, true));


        // Callback
        $callback = function ($value) {
            return is_null($value) ? 'null' : $value;
        };
        $y = [
            'a' => 'A ',
            'b' => 42,
            'c' => 'null',
        ];
        $this->assertEqualArray(
            $y,
            $arrayUtil->pick($sources, $keys, true, $callback)
        );


        // Use build-in function as callback
        $this->assertEqualArray(
            ['a' => 'A'],
            $arrayUtil->pick($sources, ['a'], false, 'trim')
        );
    }


    public function testSearchByWildcard()
    {
        $arrayUtil = $this->buildMock();

        // Empty check
        $this->assertEquals([], $arrayUtil->searchByWildcard(null, null));

        $x = ['foo' => 'bar'];
        $y = $arrayUtil->searchByWildcard($x, null);
        $this->assertEqualArray($x, $y);
        $y = $arrayUtil->searchByWildcard($x, '|', '|');
        $this->assertEqualArray($x, $y);


        $rule = 'a*, -*b, -??c, +?d*';
        $sourceArray = [
            'a' => 'ab',
            'b' => 'abc',
            'c' => 'adc',
        ];
        $ar = $arrayUtil->searchByWildcard($sourceArray, $rule);
        $this->assertEquals($ar, ['c' => 'adc']);
    }


    public function testSortByLevel2()
    {
        $arrayUtil = $this->buildMock();

        $x = [
            'a' => ['col' => 20],
            'b' => ['col' => 30],
            'c' => ['col' => 10],
        ];
        $y = [
            'c' => ['col' => 10],
            'a' => ['col' => 20],
            'b' => ['col' => 30],
        ];

        $ar = $x;
        $arrayUtil->sortByLevel2($ar, 'col', 'ASC');
        $this->assertEqualArray($ar, $y);

        unset($x['c']['col']);
        $y = [
            'b' => ['col' => 30],
            'c' => [],
            'a' => ['col' => 20],
        ];
        $ar = $x;
        $arrayUtil->sortByLevel2($ar, 'col', false, 25);
        $this->assertEqualArray($ar, $y);
    }
}
