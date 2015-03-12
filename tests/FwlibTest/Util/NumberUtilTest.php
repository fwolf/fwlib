<?php
namespace FwlibTest\Util;

use Fwlib\Util\NumberUtil;
use Fwlib\Util\UtilContainer;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class NumberUtilTest extends PHPUnitTestCase
{
    /**
     * @return NumberUtil
     */
    protected function buildMock()
    {
        return UtilContainer::getInstance()->getNumber();
    }


    public function testBaseConvert()
    {
        $numberUtil = $this->buildMock();

        // Use build-in base_convert()
        $this->assertEquals('z', $numberUtil->baseConvert(35, 10, 36));


        $this->assertEquals('0', $numberUtil->baseConvert(0, 10, 62));
        $this->assertEquals('0', $numberUtil->baseConvert(0, 62, 10));

        $this->assertEquals('f', $numberUtil->baseConvert(15, 10, 62));
        $this->assertEquals('15', $numberUtil->baseConvert('f', 62, 10));


        /** @noinspection SpellCheckingInspection */
        {
            $x = 'abcdef00001234567890';
            $y = '3o47re02jzqisvio';
            $z = '43tpyVgFkHFsZO';
        }

        $this->assertEquals('0', $numberUtil->baseConvertBcmath(null, 10, 62));
        if (extension_loaded('bcmath')) {
            $this->assertEquals('Z', $numberUtil->baseConvertBcmath(61, 10, 62));
            $this->assertEquals('61', $numberUtil->baseConvertBcmath('Z', 62, 10));

            $this->assertEquals($y, $numberUtil->baseConvertBcmath($x, 16, 36));
            $this->assertEquals($x, $numberUtil->baseConvertBcmath($y, 36, 16));
            $this->assertEquals($x, $numberUtil->baseConvertBcmath($z, 62, 16));
        }

        $this->assertEquals('0', $numberUtil->baseConvertGmp(null, 10, 62));
        if (extension_loaded('gmp')) {
            $this->assertEquals('Z', $numberUtil->baseConvertGmp(61, 10, 62));
            $this->assertEquals('61', $numberUtil->baseConvertGmp('Z', 62, 10));

            $this->assertEquals($y, $numberUtil->baseConvertGmp($x, 16, 36));
            $this->assertEquals($x, $numberUtil->baseConvertGmp($y, 36, 16));
            $this->assertEquals($x, $numberUtil->baseConvertGmp($z, 62, 16));
        }

        $this->assertEquals('0', $numberUtil->baseConvertGmpSimple(null, 10, 62));
        if (extension_loaded('gmp') && version_compare(PHP_VERSION, '5.3.2', '>=')) {
            $this->assertEquals('Z', $numberUtil->baseConvertGmpSimple(61, 10, 62));
            $this->assertEquals('61', $numberUtil->baseConvertGmpSimple('Z', 62, 10));

            $this->assertEquals($y, $numberUtil->baseConvertGmpSimple($x, 16, 36));
            $this->assertEquals($x, $numberUtil->baseConvertGmpSimple($y, 36, 16));
            $this->assertEquals($x, $numberUtil->baseConvertGmpSimple($z, 62, 16));
        }
    }


    /**
     * @expectedException  \InvalidArgumentException
     */
    public function testBaseConvertInvalidArgument()
    {
        $numberUtil = $this->buildMock();

        $numberUtil->baseConvert(0, 1, 100);
    }


    public function testToHumanSize()
    {
        $numberUtil = $this->buildMock();

        $this->assertEquals('100B', $numberUtil->toHumanSize(100));
        $this->assertEquals('1K', $numberUtil->toHumanSize(1001, 1, 1000));
        $this->assertEquals('1.001K', $numberUtil->toHumanSize(1001, 3, 1000));
        $this->assertEquals(
            '52G',
            $numberUtil->toHumanSize(52000000000, 0, 1000)
        );
        // With round
        $this->assertEquals(
            '48.43G',
            $numberUtil->toHumanSize(52000000000, 2, 1024)
        );
        $this->assertEquals(
            '46.185P',
            $numberUtil->toHumanSize(52000000000000000, 3, 1024)
        );
        $this->assertEquals(
            '52000P',
            $numberUtil->toHumanSize(52000000000000000000, 0, 1000)
        );
    }
}
