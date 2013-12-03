<?php
namespace Fwlib\Validator\Constraint\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Validator\Constraint\Length;

/**
 * Test for Fwlib\Validator\Constraint\Length
 *
 * @package     Fwlib\Validator\Constraint\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-03
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
            'The input is less than 5 characters',
            current($constraint->getMessage())
        );

        $this->assertFalse($constraint->validate($x, '2, 3'));
        $this->assertEquals(
            'The input is more than 3 characters',
            current($constraint->getMessage())
        );
    }
}
