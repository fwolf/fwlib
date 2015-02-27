<?php
namespace FwlibTest\Util\Algorithm;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Util\Algorithm\Iso7064;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Iso7064Test extends PHPUnitTestCase
{
    protected $iso7064;


    public function __construct()
    {
        $this->iso7064 = new Iso7064;
    }


    public function testEncode112()
    {
        $this->assertEquals(
            '0',
            $this->iso7064->encode('0794', '112', false)
        );

        // Mis\CinCode
        $this->assertEquals(
            '4',
            $this->iso7064->encode('44052418800101001', '112', false)
        );
        $this->assertEquals(
            'X',
            $this->iso7064->encode('11010519491231002', '112', false)
        );
    }


    public function testEncode1716()
    {
        $x = 'D98989898909898';
        $this->assertEquals(
            'B',
            $this->iso7064->encode($x, '1716', false)
        );
        $this->assertEquals(
            $x . 'B',
            $this->iso7064->encode($x, '1716', true)
        );

        $this->assertEquals(
            'A',
            $this->iso7064->encode('123A567B8912E01', '1716', false)
        );

        $this->assertEquals(
            '0',
            $this->iso7064->encode('9', '1716', false)
        );
    }


    public function testEncode3736()
    {
        $this->assertEquals(null, $this->iso7064->encode(null));

        // Value from https://en.wikipedia.org/wiki/Global_Release_Identifier
        $x = 'A12425GABC1234002';
        $this->assertEquals(
            'M',
            $this->iso7064->encode($x, '3736', false)
        );
        $this->assertEquals(
            $x . 'M',
            $this->iso7064->encode($x, '3736', true)
        );

        $this->assertEquals(
            'G',
            $this->iso7064->encode('G123489654321', '3736', false)
        );

        $this->assertEquals(
            '0',
            $this->iso7064->encode('J', '3736', false)
        );
    }


    public function testVerify()
    {
        $this->assertTrue($this->iso7064->verify(null));

        $x = '07940';
        $this->assertTrue($this->iso7064->verify($x, '112'));

        $x = '440524188001010014';
        $this->assertTrue($this->iso7064->verify($x, '112'));

        $x = '11010519491231002X';
        $this->assertTrue($this->iso7064->verify($x, '112'));

        $x = 'A12425GABC1234002M';
        $this->assertTrue($this->iso7064->verify($x, '3736'));

        $x = 'A12425GABC1234008M';
        $this->assertFalse($this->iso7064->verify($x, '3736'));
    }
}
