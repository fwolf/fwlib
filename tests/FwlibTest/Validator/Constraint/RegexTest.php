<?php
namespace FwlibTest\Validator\Constraint;

use Fwlib\Config\StringOptions;
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

        $this->assertTrue(
            $constraint->validate(42, new StringOptions('regex=/\d{2}/'))
        );
        $this->assertTrue(
            $constraint->validate(0, new StringOptions('regex=/\d?/'))
        );

        $this->assertFalse(
            $constraint->validate('', new StringOptions('regex=/\w+/'))
        );
        $this->assertFalse(
            $constraint->validate('   ', new StringOptions('regex=/!^\s/'))
        );
        // Invalid type
        $this->assertFalse($constraint->validate(null));
        $this->assertFalse($constraint->validate([]));
    }
}
