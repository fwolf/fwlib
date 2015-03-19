<?php
namespace FwlibTest\Web;

use Fwlib\Web\ResponseAwareTrait;
use Fwlib\Web\ResponseInterface;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ResponseAwareTraitTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | ResponseAwareTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(ResponseAwareTrait::class)
            ->getMockForTrait();

        return $mock;
    }


    public function testGetResponse()
    {
        $requestAware = $this->buildMock();

        $this->assertInstanceOf(
            ResponseInterface::class,
            $this->reflectionCall($requestAware, 'getResponse')
        );
    }
}
