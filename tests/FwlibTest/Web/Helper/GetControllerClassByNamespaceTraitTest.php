<?php
namespace FwlibTest\Web\Helper;

use Fwlib\Web\Helper\GetControllerClassByNamespaceTrait;
use FwlibTest\Aide\FunctionMockFactoryAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class GetControllerClassByNamespaceTraitTest extends PHPUnitTestCase
{
    use FunctionMockFactoryAwareTrait;


    /**
     * @return MockObject | GetControllerClassByNamespaceTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(
            GetControllerClassByNamespaceTrait::class
        )
            ->getMockForTrait();

        /** @noinspection PhpUndefinedFieldInspection */
        $mock->controllerNamespace = 'Ns\\';

        return $mock;
    }


    public function testGetControllerClass()
    {
        $factory = $this->getFunctionMockFactory(
            GetControllerClassByNamespaceTrait::class
        );
        $classExistsMock = $factory->get(null, 'class_exists', true);

        $controller = $this->buildMock();

        $classExistsMock->setResult(true);
        $this->assertEquals(
            'Ns\FooController',
            $this->reflectionCall($controller, 'getControllerClass', ['foo'])
        );

        $classExistsMock->setResult(false);
        $this->assertEmpty(
            $this->reflectionCall($controller, 'getControllerClass', ['foo'])
        );
    }
}
