<?php
namespace FwlibTest\Util;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\DatetimeUtil;

/**
 * Test for Fwlib\Util\DatetimeUtil
 *
 * @package     FwlibTest\Util
 * @copyright   Copyright 2009-2013 Fwolf
 * @author      Fwolf <fwolf.aide+FwlibTest@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2012-12-06
 */
class DatetimeUtilTest extends PHPunitTestCase
{
    public function testConvertSecToStr()
    {
        $this->assertEquals(DatetimeUtil::convertSecToStr(12), '12s');
        $this->assertEquals(DatetimeUtil::convertSecToStr(120), '2i');
        $this->assertEquals(DatetimeUtil::convertSecToStr('a'), '');

        $i = 65831316;
        $this->assertEquals(
            DatetimeUtil::convertStrToSec(
                DatetimeUtil::convertSecToStr($i, false)
            ),
            $i
        );

        $i = 65831316985649;
        $this->assertEquals(
            DatetimeUtil::convertStrToSec(
                DatetimeUtil::convertSecToStr($i, false)
            ),
            $i
        );

        return DatetimeUtil::convertSecToStr(62);
    }


    /**
     * Method being depended must test before this.
     * Or test of this method will be skipped.
     *
     * @depends testConvertSecToStr
     */
    public function testConvertStrToSec($str)
    {
        // Test result from testConvertSecToStr
        $this->assertEquals(DatetimeUtil::convertStrToSec($str), '62');

        $s = '2years 31days 22hours 28minutes 36seconds';
        $this->assertEquals(
            DatetimeUtil::convertSecToStr(
                DatetimeUtil::convertStrToSec($s),
                false
            ),
            $s
        );

        $s = '20874centuries 97years 134days 4hours 27minutes 29seconds';
        $this->assertEquals(
            DatetimeUtil::convertSecToStr(
                DatetimeUtil::convertStrToSec($s),
                false
            ),
            $s
        );

        $this->assertEquals(DatetimeUtil::convertStrToSec(''), 0);
        $this->assertEquals(DatetimeUtil::convertStrToSec(100), 100);
        $this->assertEquals(DatetimeUtil::convertStrToSec('100'), 100);

        $this->assertEquals(DatetimeUtil::convertStrToSec('3s'), 3);
        $this->assertEquals(DatetimeUtil::convertStrToSec('2i 3s'), 123);
        $this->assertEquals(DatetimeUtil::convertStrToSec('2I- 3s'), 117);
        $this->assertEquals(DatetimeUtil::convertStrToSec('3I - 1i 3s'), 123);
        $this->assertEquals(
            DatetimeUtil::convertStrToSec('2H- 118i -3s'),
            117
        );
        $this->assertEquals(DatetimeUtil::convertStrToSec('-118i2H-3s'), 117);

        $this->assertEquals(
            DatetimeUtil::convertStrToSec(
                '2centuries - 199Year-364DAY+ 4month
                -17w+2d-3d-24h1h-1hour+1h-58i2min-2minutes3s'
            ),
            123
        );
        $this->assertEquals(
            DatetimeUtil::convertStrToSec(
                '3s-2i2i-58i1h-1h1h-24h-3d2d
                -17w4m-364d-199y2c'
            ),
            123
        );

        return DateTimeUtil::convertStrToSec('1m2s');
    }


    public function testConvertTimeFromSybase()
    {
        $t1 = date('Y-m-d H:i:s');
        $t2 = $t1 . ':789';
        $this->assertEquals(
            DatetimeUtil::convertTimeFromSybase($t2),
            strtotime($t1)
        );
    }
}
