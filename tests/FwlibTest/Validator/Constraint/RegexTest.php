<?php
namespace FwlibTest\Validator\Constraint;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Validator\Constraint\Regex;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class RegexTest extends PHPUnitTestCase
{
    public function testValidate()
    {
        $constraint = new Regex();

        $constraint->setField('/\d{2}/');
        $this->assertTrue($constraint->validate(42));
        $constraint->setField('/\d?/');
        $this->assertTrue($constraint->validate(0));

        $constraint->setField('/\w+/');
        $this->assertFalse($constraint->validate(''));
        $constraint->setField('/!^\s/');
        $this->assertFalse($constraint->validate('   '));

        // Invalid type
        $this->assertFalse($constraint->validate(null));
        $this->assertFalse($constraint->validate([]));

        $constraint->setField('');
        $this->assertFalse($constraint->validate('dummy'));
    }
}
