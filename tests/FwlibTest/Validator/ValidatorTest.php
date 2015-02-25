<?php
namespace FwlibTest\Validator;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Validator\ConstraintContainer;
use Fwlib\Validator\Validator;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
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

        $rule = [
            'notEmpty',
            'length: 3',
        ];

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

        $rule = [
            'notRegisted',
        ];

        $this->validator->validate('dummy', $rule);
    }
}
