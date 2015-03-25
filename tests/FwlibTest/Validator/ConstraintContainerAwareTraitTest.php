<?php
namespace FwlibTest\Validator;

use Fwlib\Validator\ConstraintContainerAwareTrait;
use Fwlib\Validator\ConstraintContainerInterface;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ConstraintContainerAwareTraitTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | ConstraintContainerAwareTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(ConstraintContainerAwareTrait::class)
            ->setMethods(null)
            ->getMockForTrait();

        return $mock;
    }


    public function test()
    {
        $containerAware = $this->buildMock();

        $this->assertInstanceOf(
            ConstraintContainerInterface::class,
            $this->reflectionCall($containerAware, 'getConstraintContainer')
        );
        $this->assertNull(
            $this->reflectionGet($containerAware, 'constraintContainer')
        );


        $container = $this->getMock(ConstraintContainerInterface::class);
        /** @var ConstraintContainerInterface $container */
        $containerAware->setConstraintContainer($container);
        $this->assertInstanceOf(
            ConstraintContainerInterface::class,
            $this->reflectionCall($containerAware, 'getConstraintContainer')
        );
        $this->assertNotNull(
            $this->reflectionGet($containerAware, 'constraintContainer')
        );
    }
}
