<?php
namespace FwlibTest\Validator\Constraint;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Validator\Constraint\NotEmpty;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class NotEmptyTest extends PHPUnitTestCase
{
    public function testValidate()
    {
        $constraint = new NotEmpty();

        $this->assertTrue($constraint->validate(42));

        $this->assertFalse($constraint->validate(0));
        $this->assertFalse($constraint->validate(''));
        $this->assertFalse($constraint->validate(null));
        $this->assertFalse($constraint->validate([]));

        // Assert fail message key, which can't do in AbstractConstraint
        $failMessages = [
            NotEmpty::class . '#default' =>
                'The input should not be empty or zero',
        ];
        $this->assertEqualArray($failMessages, $constraint->getMessages());
    }
}
