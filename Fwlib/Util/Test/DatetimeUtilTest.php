<?php
namespace Fwlib\Util\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\DatetimeUtil;

/**
 * @copyright   Copyright 2009-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class DatetimeUtilTest extends PHPunitTestCase
{
    protected $datetimeUtil;

    public function __construct()
    {
        $this->datetimeUtil = new DatetimeUtil;
    }


    public function testConvertSecondToString()
    {
        $this->assertEquals(
            '12s',
            $this->datetimeUtil->convertSecondToString(12)
        );
        $this->assertEquals(
            '2i',
            $this->datetimeUtil->convertSecondToString(120)
        );
        $this->assertEquals(
            '',
            $this->datetimeUtil->convertSecondToString('a')
        );

        $i = 65831316;
        $this->assertEquals(
            $this->datetimeUtil->convertStringToSecond(
                $this->datetimeUtil->convertSecondToString($i, false)
            ),
            $i
        );

        $i = 65831316985649;
        $this->assertEquals(
            $this->datetimeUtil->convertStringToSecond(
                $this->datetimeUtil->convertSecondToString($i, false)
            ),
            $i
        );

        return $this->datetimeUtil->convertSecondToString(62);
    }


    /**
     * Method being depended must test before this.
     * Or test of this method will be skipped.
     *
     * @depends testConvertSecondToString
     */
    public function testConvertStringToSecond($str)
    {
        // Test result from testConvertSecondToString
        $this->assertEquals($this->datetimeUtil->convertStringToSecond($str), '62');

        $s = '2years 31days 22hours 28minutes 36seconds';
        $this->assertEquals(
            $this->datetimeUtil->convertSecondToString(
                $this->datetimeUtil->convertStringToSecond($s),
                false
            ),
            $s
        );

        $s = '20874centuries 97years 134days 4hours 27minutes 29seconds';
        $this->assertEquals(
            $this->datetimeUtil->convertSecondToString(
                $this->datetimeUtil->convertStringToSecond($s),
                false
            ),
            $s
        );

        $this->assertEquals($this->datetimeUtil->convertStringToSecond(''), 0);
        $this->assertEquals($this->datetimeUtil->convertStringToSecond('foobar'), 0);
        $this->assertEquals($this->datetimeUtil->convertStringToSecond(100), 100);
        $this->assertEquals($this->datetimeUtil->convertStringToSecond('100'), 100);

        $this->assertEquals($this->datetimeUtil->convertStringToSecond('3s'), 3);
        $this->assertEquals($this->datetimeUtil->convertStringToSecond('2i 3s'), 123);
        $this->assertEquals($this->datetimeUtil->convertStringToSecond('2I- 3s'), 117);
        $this->assertEquals($this->datetimeUtil->convertStringToSecond('3I - 1i 3s'), 123);
        $this->assertEquals(
            $this->datetimeUtil->convertStringToSecond('2H- 118i -3s'),
            117
        );
        $this->assertEquals($this->datetimeUtil->convertStringToSecond('-118i2H-3s'), 117);

        $this->assertEquals(
            $this->datetimeUtil->convertStringToSecond(
                '2centuries - 199Year-364DAY+ 4month
                -17w+2d-3d-24h1h-1hour+1h-58i2min-2minutes3s'
            ),
            123
        );
        $this->assertEquals(
            $this->datetimeUtil->convertStringToSecond(
                '3s-2i2i-58i1h-1h1h-24h-3d2d
                -17w4m-364d-199y2c'
            ),
            123
        );

        return $this->datetimeUtil->convertStringToSecond('1m2s');
    }


    public function testConvertTimeFromSybase()
    {
        $t1 = date('Y-m-d H:i:s');
        $t2 = $t1 . ':789';
        $this->assertEquals(
            $this->datetimeUtil->convertTimeFromSybase($t2),
            strtotime($t1)
        );
    }


    /**
     * date('Y-m-d H:i:s', 9999999999) = "2286-11-21 01:46:39"
     * So the result will always be 19 digit.
     */
    public function testGetMicroTime()
    {
        $microTime = $this->datetimeUtil->getMicroTime();

        $this->assertEquals(19, strlen($microTime));
    }
}
