<?php
namespace FwlibTest\Validator;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Validator\AbstractConstraint;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractConstraintTest extends PHPUnitTestCase
{
    /**
     * @return  MockObject|AbstractConstraint
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(AbstractConstraint::class)
            ->getMockForAbstractClass();

        return $mock;
    }


    public function testSetMessage()
    {
        $constraint = $this->buildMock();

        $defaultMessage = 'Validate failed';

        // Call setMessage() method
        $this->reflectionCall($constraint, 'setMessage', ['default']);
        // Can't use class name of mocked object, so only check message value
        $messages = $constraint->getMessages();
        $this->assertEquals($defaultMessage, current($messages));
        $this->assertStringEndsWith('#default', key($messages));


        // Call setMessage() method again will affect nothing
        $this->reflectionCall($constraint, 'setMessage', ['default']);
        $this->assertEquals(
            $defaultMessage,
            current($constraint->getMessages())
        );
        $this->assertEquals(1, count($constraint->getMessages()));


        // Call validate() will clear messages
        $constraint->validate(42);
        $this->assertEmpty($constraint->getMessages());

        // %value% is set
        $variables = $this->reflectionGet($constraint, 'messageVariables');
        $this->assertEquals(42, $variables['value']);
    }


    /**
     * @expectedException \Fwlib\Validator\Exception\MessageTemplateNotDefinedException
     * @expectedExceptionMessage not defined
     */
    public function testSetMessageWithInvalidMessageKey()
    {
        $constraint = $this->buildMock();

        $this->reflectionCall($constraint, 'setMessage', ['notExist']);
    }
}
