<?php
namespace Fwlib\Validator\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Validator\Validator;

/**
 * Test for Fwlib\Validator\Validator
 *
 * @package     Fwlib\Validator\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-04
 */
class ValidatorTest extends PHPunitTestCase
{
    private $validator;


    public function __construct()
    {
        $this->validator = new Validator();
    }


    public function testRegisterConstraint()
    {
        $this->validator->registerConstraint('newConstraint', 'ClassName');

        $this->assertTrue(
            array_key_exists(
                'newConstraint',
                $this->reflectionGet($this->validator, 'constraintMap')
            )
        );
    }


    public function testValidate()
    {
        $rule = array(
            'notEmpty',
            'length: 3',
        );

        $this->assertTrue($this->validator->validate('foobar', $rule));

        $this->assertFalse($this->validator->validate('', $rule));
        // Each constraint return a message, total 2.
        $this->assertEquals(2, count($this->validator->getMessage()));


        // $rule can also be string
        $this->assertTrue($this->validator->validate('foobar', 'notEmpty'));
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage not registed
     */
    public function testValidateWithNotRegistedConstraint()
    {
        $rule = array(
            'notRegisted',
        );

        $this->validator->validate('dummy', $rule);
    }
}
