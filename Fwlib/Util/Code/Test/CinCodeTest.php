<?php
namespace Fwlib\Util\Code\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\Code\CinCode;

/**
 * Test for Fwlib\Util\Code\CinCode
 *
 * @package     Fwlib\Util\Code\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-27
 */
class CinCodeTest extends PHPunitTestCase
{
    public function testGen()
    {
        $x = CinCode::gen();
        $this->assertEquals(true, preg_match('/[0-9]{17}[0-9X]/', $x));
    }


    public function testTo()
    {
        $x = '110105491231002';
        $y = '11010519491231002X';
        $this->assertEquals($y, CinCode::to18($x));
        $this->assertEquals($x, CinCode::to15($y));

        $x = '440524800101001';
        $y = '440524188001010014';
        $this->assertEquals($x, CinCode::to15($y));

        // Invalid input string length
        $x = 'foo';
        $this->assertEquals('foo', CinCode::to15($x));
        $this->assertEquals('foo', CinCode::to18($x));
    }


    public function testValidate()
    {
        // Data on @link
        $this->assertEquals(true, CinCode::validate('11010519491231002X'));
        $this->assertEquals(true, CinCode::validate('440524188001010014'));

        // Fake data
        $this->assertTrue(CinCode::validate('421029199004091521'));
        $this->assertTrue(CinCode::validate('820701197812060930'));

        $this->assertEquals(false, CinCode::validate('110105194912310024'));
        $this->assertEquals(false, CinCode::validate('44052418800101001X'));

        $this->assertFalse(CinCode::validate('440524800101001'));
    }
}
