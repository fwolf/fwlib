<?php
namespace FwlibTest\Web\Helper;

use Fwlib\Web\Helper\GetViewClassByNamespaceTrait;
use FwlibTest\Aide\FunctionMockAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class GetViewClassByNamespaceTraitTest extends PHPUnitTestCase
{
    use FunctionMockAwareTrait;


    /**
     * @return MockObject | GetViewClassByNamespaceTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(
            GetViewClassByNamespaceTrait::class
        )
            ->getMockForTrait();

        /** @noinspection PhpUndefinedFieldInspection */
        {
            $mock->viewNamespace = 'Ns\\';
            $mock->module = 'mod';
            $mock->defaultView = 'Hello';
        }

        return $mock;
    }


    public function testGetViewClass()
    {
        $factory = $this->getFunctionMockFactory(
            GetViewClassByNamespaceTrait::class
        );
        $classExistsMock = $factory->get(null, 'class_exists', true);

        $view = $this->buildMock();

        $classExistsMock->setResult(true);
        $this->assertEquals(
            'Ns\Mod\Foo',
            $this->reflectionCall($view, 'getViewClass', ['foo'])
        );

        $classExistsMock->setResult(false);
        $this->assertEquals(
            'Hello',
            $this->reflectionCall($view, 'getViewClass', ['foo'])
        );

        $this->assertEquals(
            'Hello',
            $this->reflectionCall($view, 'getViewClass', [''])
        );
    }
}
