<?php
namespace FwlibTest\Algorithm;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Algorithm\Iso7064;

/**
 * Test for Fwlib\Algorithm\Iso7064
 *
 * @package     FwlibTest\Algorithm
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+FwlibTest@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-09-03
 */
class Iso7064Test extends PHPunitTestCase
{
    public function testEncode112()
    {
        $this->assertEquals(
            '0',
            Iso7064::encode('0794', '112', false)
        );

        // Mis\CinCode
        $this->assertEquals(
            '4',
            Iso7064::encode('44052418800101001', '112', false)
        );
        $this->assertEquals(
            'X',
            Iso7064::encode('11010519491231002', '112', false)
        );
    }


    public function testEncode1716()
    {
        $x = 'D98989898909898';
        $this->assertEquals(
            'B',
            Iso7064::encode($x, '1716', false)
        );
        $this->assertEquals(
            $x . 'B',
            Iso7064::encode($x, '1716', true)
        );

        $this->assertEquals(
            'A',
            Iso7064::encode('123A567B8912E01', '1716', false)
        );

        $this->assertEquals(
            '0',
            Iso7064::encode('9', '1716', false)
        );
    }


    public function testEncode3736()
    {
        $this->assertEquals(null, Iso7064::encode(null));

        // Value from https://en.wikipedia.org/wiki/Global_Release_Identifier
        $x = 'A12425GABC1234002';
        $this->assertEquals(
            'M',
            Iso7064::encode($x, '3736', false)
        );
        $this->assertEquals(
            $x . 'M',
            Iso7064::encode($x, '3736', true)
        );

        $this->assertEquals(
            'G',
            Iso7064::encode('G123489654321', '3736', false)
        );

        $this->assertEquals(
            '0',
            Iso7064::encode('J', '3736', false)
        );
    }


    public function testVerify()
    {
        $this->assertTrue(Iso7064::verify(null));

        $x = '07940';
        $this->assertTrue(Iso7064::verify($x, '112'));

        $x = '440524188001010014';
        $this->assertTrue(Iso7064::verify($x, '112'));

        $x = '11010519491231002X';
        $this->assertTrue(Iso7064::verify($x, '112'));

        $x = 'A12425GABC1234002M';
        $this->assertTrue(Iso7064::verify($x, '3736'));

        $x = 'A12425GABC1234008M';
        $this->assertFalse(Iso7064::verify($x, '3736'));
    }
}
