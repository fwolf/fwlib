<?php
namespace FwlibTest\Validator\Constraint;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Validator\Constraint\Regex;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class RegexTest extends PHPUnitTestCase
{
    public function testValidate()
    {
        $constraint = new Regex();

        $this->assertTrue($constraint->validate(42, '/\d{2}/'));
        $this->assertTrue($constraint->validate(0, '/\d?/'));

        $this->assertFalse($constraint->validate('', '/\w+/'));
        $this->assertFalse($constraint->validate('   ', '/!^\s/'));
        // Invalid type
        $this->assertFalse($constraint->validate(null));
        $this->assertFalse($constraint->validate([]));
    }
}
