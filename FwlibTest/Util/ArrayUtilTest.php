<?php
namespace FwlibTest\Util;

use Fwlib\Util\ArrayUtil;

/**
 * Test for Fwlib\Util\ArrayUtil
 *
 * @package     FwlibTest\Util
 * @copyright   Copyright 2009-2013 Fwolf
 * @author      Fwolf <fwolf.aide+FwlibTest@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2010-01-25
 */
class ArrayUtilTest extends \PHPunit_Framework_TestCase
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
}
