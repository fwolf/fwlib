<?php
namespace FwlibTest\Html\ListView;

use Fwlib\Html\ListView\ListDto;
use Fwlib\Html\ListView\ListDtoAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ListDtoAwareTraitTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | ListDtoAwareTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(ListDtoAwareTrait::class)
            ->setMethods(null)
            ->getMockForTrait();

        return $mock;
    }


    public function test()
    {
        $listDtoAware = $this->buildMock();

        $listDto = new ListDto;
        $listDtoAware->setListDto($listDto);
        $this->assertInstanceOf(
            ListDto::class,
            $this->reflectionCall($listDtoAware, 'getListDto')
        );
    }
}
