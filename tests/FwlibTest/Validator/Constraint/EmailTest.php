<?php
namespace FwlibTest\Validator\Constraint;

use Fwlib\Validator\Constraint\Email;
use FwlibTest\Aide\FunctionMockFactoryAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class EmailTest extends PHPUnitTestCase
{
    use FunctionMockFactoryAwareTrait;


    public function testValidate()
    {
        $constraint = new Email();

        $this->assertTrue($constraint->validate('dummy@mail.com'));

        $foo = '12345678';
        $this->assertFalse($constraint->validate($foo));

        $foo = str_repeat('a', 65) . '@mail.com';
        $this->assertFalse($constraint->validate($foo));

        $foo = 'dummy@' . str_repeat('a', 256) . '.com';
        $this->assertFalse($constraint->validate($foo));

        $foo = '.dummy@mail.com';
        $this->assertFalse($constraint->validate($foo));

        $foo = 'dummy.@mail.com';
        $this->assertFalse($constraint->validate($foo));

        $foo = 'a..dummy@mail.com';
        $this->assertFalse($constraint->validate($foo));

        $foo = 'dummy.Aa+-0@m-ail.com';
        $this->assertTrue($constraint->validate($foo));

        $foo = 'dummy.Aa+-0@m_ail.com';
        $this->assertFalse($constraint->validate($foo));

        $foo = 'dummy@mail..com';
        $this->assertFalse($constraint->validate($foo));

        $foo = '[dummy]@mail.com';
        $this->assertFalse($constraint->validate($foo));


        $factory = $this->getFunctionMockFactory(Email::class);
        $checkdnsrrMock = $factory->get(null, 'checkdnsrr', true);

        $constraint->dnsCheck = true;
        $foo = 'dummy@mail.com';
        $checkdnsrrMock->setResult(false);
        $this->assertFalse($constraint->validate($foo));

        $checkdnsrrMock->setResult(true);
        $this->assertTrue($constraint->validate($foo));


        $checkdnsrrMock->disableAll();
    }
}
