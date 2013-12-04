<?php
namespace Fwlib\Validator\Constraint\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Validator\Constraint\Email;

/**
 * Test for Fwlib\Validator\Constraint\Email
 *
 * @package     Fwlib\Validator\Constraint\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-04
 */
class EmailTest extends PHPunitTestCase
{
    public static $checkdnsrr = false;


    public function testValidate()
    {
        $constraint = new Email();

        $this->assertTrue($constraint->validate('dummy@mail.com'));

        $x = '12345678';
        $this->assertFalse($constraint->validate($x));

        $x = str_repeat('a', 65) . '@mail.com';
        $this->assertFalse($constraint->validate($x));

        $x = 'dummy@' . str_repeat('a', 256) . '.com';
        $this->assertFalse($constraint->validate($x));

        $x = '.dummy@mail.com';
        $this->assertFalse($constraint->validate($x));

        $x = 'dummy.@mail.com';
        $this->assertFalse($constraint->validate($x));

        $x = 'd..ummy@mail.com';
        $this->assertFalse($constraint->validate($x));

        $x = 'dummy.Aa+-0@m-ail.com';
        $this->assertTrue($constraint->validate($x));

        $x = 'dummy.Aa+-0@m_ail.com';
        $this->assertFalse($constraint->validate($x));

        $x = 'dummy@mail..com';
        $this->assertFalse($constraint->validate($x));

        $x = '[dummy]@mail.com';
        $this->assertFalse($constraint->validate($x));


        $constraint->dnsCheck = true;
        $x = 'dummy@mail.com';
        self::$checkdnsrr = false;
        $this->assertFalse($constraint->validate($x));

        self::$checkdnsrr = true;
        $this->assertTrue($constraint->validate($x));
    }
}


namespace Fwlib\Validator\Constraint;

function checkdnsrr()
{
    return \Fwlib\Validator\Constraint\Test\EmailTest::$checkdnsrr;
}
