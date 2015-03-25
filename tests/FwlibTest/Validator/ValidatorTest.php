<?php
namespace FwlibTest\Validator;

use Fwlib\Validator\ConstraintContainer;
use Fwlib\Validator\Validator;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ValidatorTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | Validator
     */
    protected function buildMock()
    {
        $mock = $this->getMock(Validator::class, null);

        return $mock;
    }


    public function testValidate()
    {
        $validator = $this->buildMock();

        $validator->setConstraintContainer(ConstraintContainer::getInstance());

        $rule = [
            'notEmpty',
            'length: 3',
        ];

        $this->assertTrue($validator->validate('foobar', $rule));

        $this->assertFalse($validator->validate('', $rule));
        // Each constraint return a message, total 2.
        $this->assertEquals(2, count($validator->getMessages()));


        // $rule can also be string
        $this->assertTrue($validator->validate('foobar', 'notEmpty'));
    }


    /**
     * @expectedException \Fwlib\Base\Exception\ServiceInstanceCreationFailException
     */
    public function testValidateWithNotRegisteredConstraint()
    {
        $validator = $this->buildMock();

        $rule = [
            'notRegistered',
        ];

        $validator->validate('dummy', $rule);
    }
}
