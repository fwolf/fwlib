<?php
namespace Fwlib\Util\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\Validator;

/**
 * Test for Fwlib\Util\Validator
 *
 * @package     Fwlib\Util\Test
 * @copyright   Copyright 2010-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2010-12-13
 */
class ValidatorTest extends PHPunitTestCase
{
    public function testEmail()
    {
        $x = '12345678';
        $this->assertFalse(Validator::email($x));

        $x = str_repeat('a', 65) . '@mail.com';
        $this->assertFalse(Validator::email($x));

        $x = 'dummy@' . str_repeat('a', 256) . '.com';
        $this->assertFalse(Validator::email($x));

        $x = '.dummy@mail.com';
        $this->assertFalse(Validator::email($x));

        $x = 'dummy.@mail.com';
        $this->assertFalse(Validator::email($x));

        $x = 'd..ummy@mail.com';
        $this->assertFalse(Validator::email($x));

        $x = 'dummy.Aa+-0@m_ail.com';
        $this->assertFalse(Validator::email($x));

        $x = 'dummy@mail..com';
        $this->assertFalse(Validator::email($x));

        $x = '[dummy]@mail.com';
        $this->assertFalse(Validator::email($x));
    }


    public function testIpv4()
    {
        $x = '208.67.222.222';
        $this->assertTrue(Validator::ipv4($x));

        $x = '208.67.222222';
        $this->assertFalse(Validator::ipv4($x));
    }
}
