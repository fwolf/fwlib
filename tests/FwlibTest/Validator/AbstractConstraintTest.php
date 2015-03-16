<?php
namespace FwlibTest\Validator;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Validator\AbstractConstraint;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractConstraintTest extends PHPUnitTestCase
{
    public function testSetMessage()
    {
        $constraint = $this->getMockForAbstractClass(
            AbstractConstraint::class
        );


        // Call setMessage() method
        $this->reflectionCall($constraint, 'setMessage', ['default']);
        // Can't use class name of mocked object, so only check message value
        $x = 'Validate fail message';
        $this->assertEqualArray($x, current($constraint->getMessage()));


        // Call setMessage() method again will affect nothing
        $this->reflectionCall($constraint, 'setMessage', ['default']);
        $this->assertEqualArray($x, current($constraint->getMessage()));
        $this->assertEquals(1, count($constraint->getMessage()));


        // Call validate() will clear messages
        $constraint->validate(42);
        $this->assertEmpty($constraint->getMessage());

        // %value% is set
        $ar = $this->reflectionGet($constraint, 'messageVariable');
        $this->assertEquals(42, $ar['value']);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage not defined
     */
    public function testSetMessageWithInvalidMessageKey()
    {
        $constraint = $this->getMockForAbstractClass(
            AbstractConstraint::class
        );

        $this->reflectionCall($constraint, 'setMessage', ['notExist']);
    }
}
