<?php
namespace FwlibTest\Validator\Constraint;

use Fwlib\Validator\Constraint\Email;
use Fwlib\Util\UtilContainer;
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

        $x = 'a..dummy@mail.com';
        $this->assertFalse($constraint->validate($x));

        $x = 'dummy.Aa+-0@m-ail.com';
        $this->assertTrue($constraint->validate($x));

        $x = 'dummy.Aa+-0@m_ail.com';
        $this->assertFalse($constraint->validate($x));

        $x = 'dummy@mail..com';
        $this->assertFalse($constraint->validate($x));

        $x = '[dummy]@mail.com';
        $this->assertFalse($constraint->validate($x));


        $factory = $this->getFunctionMockFactory(Email::class);
        $checkdnsrrMock = $factory->get(null, 'checkdnsrr', true);

        $constraint->dnsCheck = true;
        $x = 'dummy@mail.com';
        $checkdnsrrMock->setResult(false);
        $this->assertFalse($constraint->validate($x));

        // For coverage
        $constraint->setUtilContainer(UtilContainer::getInstance());

        $checkdnsrrMock->setResult(true);
        $this->assertTrue($constraint->validate($x));


        $checkdnsrrMock->disableAll();
    }
}
