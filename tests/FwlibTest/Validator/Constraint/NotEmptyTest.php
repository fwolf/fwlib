<?php
namespace FwlibTest\Validator\Constraint;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Validator\Constraint\NotEmpty;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class NotEmptyTest extends PHPunitTestCase
{
    public function testValidate()
    {
        $constraint = new NotEmpty();

        $this->assertTrue($constraint->validate(42));

        $this->assertFalse($constraint->validate(0));
        $this->assertFalse($constraint->validate(''));
        $this->assertFalse($constraint->validate(null));
        $this->assertFalse($constraint->validate(array()));

        // Assert fail message key, which can't do in AbstractConstraint
        $x = array(
            'Fwlib::Validator::Constraint::NotEmpty::default' =>
                'The input should not be empty or zero',
        );
        $this->assertEqualArray($x, $constraint->getMessage());
    }
}