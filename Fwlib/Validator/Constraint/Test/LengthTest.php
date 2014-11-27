<?php
namespace Fwlib\Validator\Constraint\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Validator\Constraint\Length;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class LengthTest extends PHPunitTestCase
{
    public function testValidate()
    {
        $constraint = new Length();

        $x = 'abcd';

        $this->assertTrue($constraint->validate($x, '3, 5'));
        $this->assertTrue($constraint->validate($x, '3 and 5'));
        $this->assertTrue($constraint->validate($x, '3'));

        $this->assertFalse($constraint->validate($x, '5, 6'));
        $this->assertEquals(
            'The input should be more than 5 characters',
            current($constraint->getMessage())
        );

        $this->assertFalse($constraint->validate($x, '2, 3'));
        $this->assertEquals(
            'The input should be less than 3 characters',
            current($constraint->getMessage())
        );
    }
}
