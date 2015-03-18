<?php
namespace FwlibTest\Web;

use Fwlib\Web\RequestAwareTrait;
use Fwlib\Web\RequestInterface;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class RequestAwareTraitTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | RequestAwareTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(RequestAwareTrait::class)
            ->getMockForTrait();

        return $mock;
    }


    public function testGetRequest()
    {
        $requestAware = $this->buildMock();

        $this->assertInstanceOf(
            RequestInterface::class,
            $this->reflectionCall($requestAware, 'getRequest')
        );
    }
}
