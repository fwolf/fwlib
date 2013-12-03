<?php
namespace Fwlib\Validator\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Validator\AbstractConstraint;

/**
 * Test for Fwlib\Validator\AbstractConstraint
 *
 * @package     Fwlib\Validator\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-03
 */
class AbstractConstraintTest extends PHPunitTestCase
{
    public function testSetMessage()
    {
        $constraint = $this->getMockForAbstractClass(
            'Fwlib\Validator\AbstractConstraint'
        );


        // Call setMessage() method
        $this->reflectionCall($constraint, 'setMessage', array('default'));
        $x = array(
            'Fwlib::Validator::AbstractConstraint::default' =>
            'Validate fail message'
        );
        $this->assertEqualArray($x, $constraint->getMessage());


        // Call setMessage() method again will affect nothing
        $this->reflectionCall($constraint, 'setMessage', array('default'));
        $this->assertEqualArray($x, $constraint->getMessage());
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
            'Fwlib\Validator\AbstractConstraint'
        );

        $this->reflectionCall($constraint, 'setMessage', array('notExist'));
    }
}
