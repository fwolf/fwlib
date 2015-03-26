<?php
namespace FwlibTest\Util\Common;

use Fwlib\Util\Common\FilterInputTrait;
use Fwlib\Util\Common\HttpUtil;
use FwlibTest\Aide\FunctionMockAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class FilterInputTraitTest extends PHPUnitTestCase
{
    use FunctionMockAwareTrait;


    /**
     * @return MockObject | \Fwlib\Util\Common\FilterInputTrait
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

        $factory = $this->getFunctionMockFactory(HttpUtil::class);
        $filterInputMock = $factory->get(null, 'filter_input', true);


        $filterInputMock->setResult('bar');
        $y = $filterInputTrait->filterInput(INPUT_GET, 'dummy', 'foo');
        $this->assertEquals('bar', $y);

        $filterInputMock->setResult(null);
        $y = $filterInputTrait->filterInput(INPUT_GET, 'dummy', 'foo');
        $this->assertEquals('foo', $y);


        $filterInputMock->disableAll();
    }


    /**
     * Actual input read test deleted due has no value in Travis-CI
     */
    public function testFilterInputArray()
    {
        $filterInputTrait = $this->buildMock();

        $factory = $this->getFunctionMockFactory(HttpUtil::class);
        $filterInputArrayMock =
            $factory->get(null, 'filter_input_array', true);


        $filterInputArrayMock->setResult(['foo']);
        $ar = $filterInputTrait->filterInputArray(INPUT_ENV, FILTER_DEFAULT);
        $this->assertEqualArray(['foo'], $ar);


        $filterInputArrayMock->disableAll();
    }
}
