<?php
namespace FwlibTest\Validator;

use Fwlib\Validator\ConstraintContainer;
use Fwlib\Validator\Validator;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ValidatorTest extends PHPUnitTestCase
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
     * @expectedException \Fwlib\Base\Exception\ServiceInstanceCreationFailException
     */
    public function testValidateWithNotRegisteredConstraint()
    {
        $this->validator = new Validator();
        $this->validator->setConstraintContainer($this->constraintContainer);

        $rule = [
            'notRegistered',
        ];

        $this->validator->validate('dummy', $rule);
    }
}
