<?php
namespace FwlibTest\Validator\Constraint;

use Fwlib\Config\StringOptions;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Validator\Constraint\Length;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class LengthTest extends PHPUnitTestCase
{
    public function testValidate()
    {
        $constraint = new Length();

        $str = 'test';

        $constraint->setOptionsInstance(new StringOptions('min=3, max=5'));
        $this->assertTrue($constraint->validate($str));
        $constraint->setOptionsInstance(new StringOptions('min=3'));
        $this->assertTrue($constraint->validate($str));

        $constraint->setOptionsInstance(new StringOptions('min=5, max=6'));
        $this->assertFalse($constraint->validate($str));
        $this->assertEquals(
            'The input should be more than 5 characters',
            current($constraint->getMessages())
        );

        $constraint->setOptionsInstance(new StringOptions('min=2, max=3'));
        $this->assertFalse($constraint->validate($str));
        $this->assertEquals(
            'The input should be less than 3 characters',
            current($constraint->getMessages())
        );
    }
}
