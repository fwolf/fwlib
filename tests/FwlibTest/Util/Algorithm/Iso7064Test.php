<?php
namespace FwlibTest\Util\Algorithm;

use Fwlib\Util\UtilContainer;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Util\Algorithm\Iso7064;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Iso7064Test extends PHPUnitTestCase
{
    /**
     * @return Iso7064
     */
    protected function buildMock()
    {
        return UtilContainer::getInstance()->getIso7064();
    }


    public function testEncode112()
    {
        $iso7064 = $this->buildMock();

        $this->assertEquals(
            '0',
            $iso7064->encode('0794', '112', false)
        );

        // Mis\CinCode
        $this->assertEquals(
            '4',
            $iso7064->encode('44052418800101001', '112', false)
        );
        $this->assertEquals(
            'X',
            $iso7064->encode('11010519491231002', '112', false)
        );
    }


    public function testEncode1716()
    {
        $iso7064 = $this->buildMock();

        $x = 'D98989898909898';
        $this->assertEquals(
            'B',
            $iso7064->encode($x, '1716', false)
        );
        $this->assertEquals(
            $x . 'B',
            $iso7064->encode($x, '1716', true)
        );

        $this->assertEquals(
            'A',
            $iso7064->encode('123A567B8912E01', '1716', false)
        );

        $this->assertEquals(
            '0',
            $iso7064->encode('9', '1716', false)
        );
    }


    public function testEncode3736()
    {
        $iso7064 = $this->buildMock();

        $this->assertEquals(null, $iso7064->encode(null));

        // Value from https://en.wikipedia.org/wiki/Global_Release_Identifier
        /** @noinspection SpellCheckingInspection */
        $x = 'A12425GABC1234002';
        $this->assertEquals(
            'M',
            $iso7064->encode($x, '3736', false)
        );
        $this->assertEquals(
            $x . 'M',
            $iso7064->encode($x, '3736', true)
        );

        $this->assertEquals(
            'G',
            $iso7064->encode('G123489654321', '3736', false)
        );

        $this->assertEquals(
            '0',
            $iso7064->encode('J', '3736', false)
        );
    }


    public function testVerify()
    {
        $iso7064 = $this->buildMock();

        $this->assertTrue($iso7064->verify(null));

        $x = '07940';
        $this->assertTrue($iso7064->verify($x, '112'));

        $x = '440524188001010014';
        $this->assertTrue($iso7064->verify($x, '112'));

        $x = '11010519491231002X';
        $this->assertTrue($iso7064->verify($x, '112'));

        /** @noinspection SpellCheckingInspection */
        {
            $x1 = 'A12425GABC1234002M';
            $x2 = 'A12425GABC1234008M';
        }
        $this->assertTrue($iso7064->verify($x1, '3736'));

        $this->assertFalse($iso7064->verify($x2, '3736'));
    }
}
