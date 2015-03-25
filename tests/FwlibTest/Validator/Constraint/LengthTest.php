<?php
namespace FwlibTest\Validator\Constraint;

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

        $this->assertTrue($constraint->validate($str, '3, 5'));
        $this->assertTrue($constraint->validate($str, '3 and 5'));
        $this->assertTrue($constraint->validate($str, '3'));

        $this->assertFalse($constraint->validate($str, '5, 6'));
        $this->assertEquals(
            'The input should be more than 5 characters',
            current($constraint->getMessages())
        );

        $this->assertFalse($constraint->validate($str, '2, 3'));
        $this->assertEquals(
            'The input should be less than 3 characters',
            current($constraint->getMessages())
        );
    }
}
