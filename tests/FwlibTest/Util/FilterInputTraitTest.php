<?php
namespace FwlibTest\Util;

use Fwlib\Util\FilterInputTrait;
use Fwlib\Util\HttpUtil;
use FwlibTest\Aide\FunctionMockFactoryAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class FilterInputTraitTest extends PHPUnitTestCase
{
    use FunctionMockFactoryAwareTrait;


    /**
     * @return MockObject | FilterInputTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(
            FilterInputTrait::class,
            null
        )
            ->getMockForTrait();

        return $mock;
    }


    public function testFilterInput()
    {
        $filterInputTrait = $this->buildMock();

        $factory = $this->getFunctionMockFactory()
            ->setNamespace(HttpUtil::class);
        $filterInputMock = $factory->get(null, 'filter_input', true);


        $filterInputMock->setResult('bar');
        $y = $filterInputTrait->filterInput(INPUT_GET, 'dummy', 'foo');
        $this->assertEquals('bar', $y);

        $filterInputMock->setResult(null);
        $y = $filterInputTrait->filterInput(INPUT_GET, 'dummy', 'foo');
        $this->assertEquals('foo', $y);


        $filterInputMock->disableAll();
    }


    public function testFilterInputArray()
    {
        $filterInputTrait = $this->buildMock();

        $factory = $this->getFunctionMockFactory()
            ->setNamespace(HttpUtil::class);
        $filterInputArrayMock =
            $factory->get(null, 'filter_input_array', true);


        $filterInputArrayMock->setResult(['foo']);
        $ar = $filterInputTrait->filterInputArray(INPUT_GET, FILTER_DEFAULT);
        $this->assertEqualArray(['foo'], $ar);


        $filterInputArrayMock->disableAll();


        // Read raw input
        $env = $filterInputTrait->filterInputArray(INPUT_ENV, FILTER_DEFAULT);
        $this->assertArrayHasKey('PWD', $env);
    }
}
