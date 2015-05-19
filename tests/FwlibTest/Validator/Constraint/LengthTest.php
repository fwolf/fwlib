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

        $this->assertTrue(
            $constraint->validate($str, new StringOptions('min=3, max=5'))
        );
        $this->assertTrue(
            $constraint->validate($str, new StringOptions('min=3'))
        );

        $this->assertFalse(
            $constraint->validate($str, new StringOptions('min=5, max=6'))
        );
        $this->assertEquals(
            'The input should be more than 5 characters',
            current($constraint->getMessages())
        );

        $this->assertFalse(
            $constraint->validate($str, new StringOptions('min=2, max=3'))
        );
        $this->assertEquals(
            'The input should be less than 3 characters',
            current($constraint->getMessages())
        );
    }
}
