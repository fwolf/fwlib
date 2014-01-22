<?php
namespace Fwlib\Util\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\NumberUtil;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-29
 */
class NumberUtilTest extends PHPunitTestCase
{
    protected $numberUtil;


    public function __construct()
    {
        $this->numberUtil = new NumberUtil;
    }


    public function testBaseConvert()
    {
        // Use build-in base_convert()
        $this->assertEquals('z', $this->numberUtil->baseConvert(35, 10, 36));


        $this->assertEquals('0', $this->numberUtil->baseConvert(0, 10, 62));
        $this->assertEquals('0', $this->numberUtil->baseConvert(0, 62, 10));

        $this->assertEquals('f', $this->numberUtil->baseConvert(15, 10, 62));
        $this->assertEquals('15', $this->numberUtil->baseConvert('f', 62, 10));


        $x = 'abcdef00001234567890';
        $y = '3o47re02jzqisvio';
        $z = '43tpyVgFkHFsZO';

        $this->assertEquals('0', $this->numberUtil->baseConvertBcmath(null, 10, 62));
        if (extension_loaded('bcmath')) {
            $this->assertEquals('Z', $this->numberUtil->baseConvertBcmath(61, 10, 62));
            $this->assertEquals('61', $this->numberUtil->baseConvertBcmath('Z', 62, 10));

            $this->assertEquals($y, $this->numberUtil->baseConvertBcmath($x, 16, 36));
            $this->assertEquals($x, $this->numberUtil->baseConvertBcmath($y, 36, 16));
            $this->assertEquals($x, $this->numberUtil->baseConvertBcmath($z, 62, 16));
        }

        $this->assertEquals('0', $this->numberUtil->baseConvertGmp(null, 10, 62));
        if (extension_loaded('gmp')) {
            $this->assertEquals('Z', $this->numberUtil->baseConvertGmp(61, 10, 62));
            $this->assertEquals('61', $this->numberUtil->baseConvertGmp('Z', 62, 10));

            $this->assertEquals($y, $this->numberUtil->baseConvertGmp($x, 16, 36));
            $this->assertEquals($x, $this->numberUtil->baseConvertGmp($y, 36, 16));
            $this->assertEquals($x, $this->numberUtil->baseConvertGmp($z, 62, 16));
        }

        $this->assertEquals('0', $this->numberUtil->baseConvertGmpSimple(null, 10, 62));
        if (extension_loaded('gmp') && version_compare(PHP_VERSION, '5.3.2', '>=')) {
            $this->assertEquals('Z', $this->numberUtil->baseConvertGmpSimple(61, 10, 62));
            $this->assertEquals('61', $this->numberUtil->baseConvertGmpSimple('Z', 62, 10));

            $this->assertEquals($y, $this->numberUtil->baseConvertGmpSimple($x, 16, 36));
            $this->assertEquals($x, $this->numberUtil->baseConvertGmpSimple($y, 36, 16));
            $this->assertEquals($x, $this->numberUtil->baseConvertGmpSimple($z, 62, 16));
        }
    }


    /**
     * @expectedException  InvalidArgumentException
     */
    public function testBaseConvertInvalidArgument()
    {
        $this->numberUtil->baseConvert(0, 1, 100);
    }


    public function testToHumanSize()
    {
        $this->assertEquals('100B', $this->numberUtil->toHumanSize(100));
        $this->assertEquals('1K', $this->numberUtil->toHumanSize(1001, 1, 1000));
        $this->assertEquals('1.001K', $this->numberUtil->toHumanSize(1001, 3, 1000));
        $this->assertEquals(
            '52G',
            $this->numberUtil->toHumanSize(52000000000, 0, 1000)
        );
        // With round
        $this->assertEquals(
            '48.43G',
            $this->numberUtil->toHumanSize(52000000000, 2, 1024)
        );
        $this->assertEquals(
            '46.185P',
            $this->numberUtil->toHumanSize(52000000000000000, 3, 1024)
        );
        $this->assertEquals(
            '52000P',
            $this->numberUtil->toHumanSize(52000000000000000000, 0, 1000)
        );
    }
}
