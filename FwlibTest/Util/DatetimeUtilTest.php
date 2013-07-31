<?php
namespace FwlibTest\Util;

use Fwlib\Util\DatetimeUtil;

/**
 * Test for Fwlib\Util\Datetimeutil
 *
 * @package     FwlibTest\Util
 * @copyright   Copyright 2009-2013, Fwolf
 * @author      Fwolf <fwolf.aide+FwlibTest@gmail.com>
 * @since       2012-12-06
 */
class DatetimeUtilTest extends \PHPunit_Framework_TestCase
{
    public function testCvtSecToStr()
    {
        $this->assertEquals(DatetimeUtil::cvtSecToStr(12), '12s');
        $this->assertEquals(DatetimeUtil::cvtSecToStr(120), '2i');

        $i = 65831316;
        $this->assertEquals(
            DatetimeUtil::cvtStrToSec(DatetimeUtil::cvtSecToStr($i, false)),
            $i
        );

        $i = 65831316985649;
        $this->assertEquals(
            DatetimeUtil::cvtStrToSec(DatetimeUtil::cvtSecToStr($i, false)),
            $i
        );

        return DatetimeUtil::cvtSecToStr(62);
    }


    /**
     * Method being depended must test before this.
     * Or test of this method will be skipped.
     *
     * @depends testCvtSecToStr
     */
    public function testCvtStrToSec($str)
    {
        // Test result from testCvtSecToStr
        $this->assertEquals(DatetimeUtil::cvtStrToSec($str), '62');

        $s = '2years 31days 22hours 28minutes 36seconds';
        $this->assertEquals(
            DatetimeUtil::cvtSecToStr(DatetimeUtil::cvtStrToSec($s), false),
            $s
        );

        $s = '20874centuries 97years 134days 4hours 27minutes 29seconds';
        $this->assertEquals(
            DatetimeUtil::cvtSecToStr(DatetimeUtil::cvtStrToSec($s), false),
            $s
        );

        $this->assertEquals(DatetimeUtil::cvtStrToSec(''), 0);
        $this->assertEquals(DatetimeUtil::cvtStrToSec(100), 100);
        $this->assertEquals(DatetimeUtil::cvtStrToSec('100'), 100);

        $this->assertEquals(DatetimeUtil::cvtStrToSec('3s'), 3);
        $this->assertEquals(DatetimeUtil::cvtStrToSec('2i 3s'), 123);
        $this->assertEquals(DatetimeUtil::cvtStrToSec('2I- 3s'), 117);
        $this->assertEquals(DatetimeUtil::cvtStrToSec('3I - 1i 3s'), 123);
        $this->assertEquals(DatetimeUtil::cvtStrToSec('2H- 118i -3s'), 117);
        $this->assertEquals(DatetimeUtil::cvtStrToSec('-118i2H-3s'), 117);

        $this->assertEquals(
            DatetimeUtil::cvtStrToSec(
                '2centuries - 199Year-364DAY+ 4month
                -17w+2d-3d-24h1h-1hour+1h-58i2min-2minutes3s'
            ),
            123
        );
        $this->assertEquals(
            DatetimeUtil::cvtStrToSec(
                '3s-2i2i-58i1h-1h1h-24h-3d2d
                -17w4m-364d-199y2c'
            ),
            123
        );

        return DateTimeUtil::cvtStrToSec('1m2s');
    }
}
