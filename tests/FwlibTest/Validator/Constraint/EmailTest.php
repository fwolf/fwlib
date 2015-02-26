<?php
namespace FwlibTest\Validator\Constraint;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Validator\Constraint\Email;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
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

        // For coverage
        $constraint->setUtilContainer(UtilContainer::getInstance());

        self::$checkdnsrr = true;
        $this->assertTrue($constraint->validate($x));
    }
}


namespace Fwlib\Validator\Constraint;

function checkdnsrr()
{
    return \FwlibTest\Validator\Constraint\EmailTest::$checkdnsrr;
}
