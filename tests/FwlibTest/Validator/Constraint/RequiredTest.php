<?php
namespace FwlibTest\Validator\Constraint;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Validator\Constraint\Required;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class RequiredTest extends PHPunitTestCase
{
    public function testValidate()
    {
        $constraint = new Required();

        $this->assertTrue($constraint->validate(42));
        $this->assertTrue($constraint->validate(0));
        $this->assertTrue($constraint->validate(array()));

        $this->assertFalse($constraint->validate(''));
        $this->assertFalse($constraint->validate('   '));
        $this->assertFalse($constraint->validate(null));
    }
}
