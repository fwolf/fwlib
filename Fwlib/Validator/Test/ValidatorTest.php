<?php
namespace Fwlib\Validator\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Validator\ConstraintContainer;
use Fwlib\Validator\Validator;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-04
 */
class ValidatorTest extends PHPunitTestCase
{
    protected $constraintContainer;
    protected $validator;


    public function __construct()
    {
        $this->constraintContainer = ConstraintContainer::getInstance();
    }


    public function testValidate()
    {
        $this->validator = new Validator($this->constraintContainer);

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
     * @expectedExceptionMessage Invalid service
     */
    public function testValidateWithNotRegistedConstraint()
    {
        $this->validator = new Validator();
        $this->validator->setConstraintContainer($this->constraintContainer);

        $rule = array(
            'notRegisted',
        );

        $this->validator->validate('dummy', $rule);
    }
}
