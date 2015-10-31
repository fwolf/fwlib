<?php
namespace FwlibTest\Validator;

use Fwlib\Validator\Constraint\Email;
use Fwlib\Validator\Constraint\Ipv4;
use Fwlib\Validator\Constraint\Length;
use Fwlib\Validator\Constraint\NotEmpty;
use Fwlib\Validator\Constraint\Regex;
use Fwlib\Validator\Constraint\Required;
use Fwlib\Validator\Constraint\Url;
use Fwlib\Validator\ConstraintContainer;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ConstraintContainerTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | ConstraintContainer
     */
    protected function buildMock()
    {
        return ConstraintContainer::getInstance();
    }


    public function testBuildClassMap()
    {
        $constraintContainer = $this->buildMock();

        $this->assertNotEmpty(
            $this->reflectionCall(
                $constraintContainer,
                'buildClassMap'
            )
        );
    }


    public function testGetMethods()
    {
        $constraintContainer = $this->buildMock();

        $this->assertInstanceOf(
            Email::class,
            $constraintContainer->getEmail()
        );
        $this->assertInstanceOf(
            Ipv4::class,
            $constraintContainer->getIpv4()
        );
        $this->assertInstanceOf(
            Length::class,
            $constraintContainer->getLength()
        );
        $this->assertInstanceOf(
            NotEmpty::class,
            $constraintContainer->getNotEmpty()
        );
        $this->assertInstanceOf(
            Required::class,
            $constraintContainer->getRequired()
        );
        $this->assertInstanceOf(
            Regex::class,
            $constraintContainer->getRegex()
        );
        $this->assertInstanceOf(
            Url::class,
            $constraintContainer->getUrl()
        );
    }
}
