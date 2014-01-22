<?php
namespace Fwlib\Validator\Constraint\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Validator\Constraint\Regex;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-11
 */
class RegexTest extends PHPunitTestCase
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
        $this->assertFalse($constraint->validate(array()));
    }
}
