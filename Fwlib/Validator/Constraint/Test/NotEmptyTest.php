<?php
namespace Fwlib\Validator\Constraint\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Validator\Constraint\NotEmpty;

/**
 * Test for Fwlib\Validator\Constraint\NotEmpty
 *
 * @package     Fwlib\Validator\Constraint\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-03
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
    }
}
