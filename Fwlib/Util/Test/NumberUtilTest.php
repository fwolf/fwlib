<?php
namespace Fwlib\Util\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\NumberUtil;

/**
 * Test for Fwlib\Util\NumberUtil
 *
 * @package     Fwlib\Util\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-29
 */
class NumberUtilTest extends PHPunitTestCase
{
    public function testBaseConvert()
    {
        // Use build-in base_convert()
        $this->assertEquals('z', NumberUtil::baseConvert(35, 10, 36));


        $this->assertEquals('0', NumberUtil::baseConvert(0, 10, 62));
        $this->assertEquals('0', NumberUtil::baseConvert(0, 62, 10));

        $this->assertEquals('f', NumberUtil::baseConvert(15, 10, 62));
        $this->assertEquals('15', NumberUtil::baseConvert('f', 62, 10));


        $x = 'abcdef00001234567890';
        $y = '3o47re02jzqisvio';
        $z = '43tpyVgFkHFsZO';

        $ref = new \ReflectionMethod('Fwlib\Util\NumberUtil', 'baseConvertBcmath');
        $ref->setAccessible(true);
        $this->assertEquals('0', $ref->invokeArgs(null, array(null, 10, 62)));
        if (extension_loaded('bcmath')) {
            $this->assertEquals('Z', $ref->invokeArgs(null, array(61, 10, 62)));
            $this->assertEquals('61', $ref->invokeArgs(null, array('Z', 62, 10)));

            $this->assertEquals($y, $ref->invokeArgs(null, array($x, 16, 36)));
            $this->assertEquals($x, $ref->invokeArgs(null, array($y, 36, 16)));
            $this->assertEquals($x, $ref->invokeArgs(null, array($z, 62, 16)));
        }

        $ref = new \ReflectionMethod('Fwlib\Util\NumberUtil', 'baseConvertGmp');
        $ref->setAccessible(true);
        $this->assertEquals('0', $ref->invokeArgs(null, array(null, 10, 62)));
        if (extension_loaded('gmp')) {
            $this->assertEquals('Z', $ref->invokeArgs(null, array(61, 10, 62)));
            $this->assertEquals('61', $ref->invokeArgs(null, array('Z', 62, 10)));

            $this->assertEquals($y, $ref->invokeArgs(null, array($x, 16, 36)));
            $this->assertEquals($x, $ref->invokeArgs(null, array($y, 36, 16)));
            $this->assertEquals($x, $ref->invokeArgs(null, array($z, 62, 16)));
        }

        $ref = new \ReflectionMethod('Fwlib\Util\NumberUtil', 'baseConvertGmpSimple');
        $ref->setAccessible(true);
        $this->assertEquals('0', $ref->invokeArgs(null, array(null, 10, 62)));
        if (extension_loaded('gmp') && version_compare(PHP_VERSION, '5.3.2', '>=')) {
            $this->assertEquals('Z', $ref->invokeArgs(null, array(61, 10, 62)));
            $this->assertEquals('61', $ref->invokeArgs(null, array('Z', 62, 10)));

            $this->assertEquals($y, $ref->invokeArgs(null, array($x, 16, 36)));
            $this->assertEquals($x, $ref->invokeArgs(null, array($y, 36, 16)));
            $this->assertEquals($x, $ref->invokeArgs(null, array($z, 62, 16)));
        }
    }


    /**
     * @expectedException  InvalidArgumentException
     */
    public function testBaseConvertInvalidArgument()
    {
        NumberUtil::baseConvert(0, 1, 100);
    }


    public function testToHumanSize()
    {
        $this->assertEquals('100B', NumberUtil::toHumanSize(100));
        $this->assertEquals('1K', NumberUtil::toHumanSize(1001, 1, 1000));
        $this->assertEquals('1.001K', NumberUtil::toHumanSize(1001, 3, 1000));
        $this->assertEquals(
            '52G',
            NumberUtil::toHumanSize(52000000000, 0, 1000)
        );
        // With round
        $this->assertEquals(
            '48.43G',
            NumberUtil::toHumanSize(52000000000, 2, 1024)
        );
        $this->assertEquals(
            '46.185P',
            NumberUtil::toHumanSize(52000000000000000, 3, 1024)
        );
        $this->assertEquals(
            '52000P',
            NumberUtil::toHumanSize(52000000000000000000, 0, 1000)
        );
    }
}
