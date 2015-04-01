<?php
namespace FwlibTest\Html\ListView;

use Fwlib\Html\ListView\Request;
use Fwlib\Html\ListView\RequestAwareTrait;
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
            ->setMethods(null)
            ->getMockForTrait();

        return $mock;
    }


    public function test()
    {
        $requestAware = $this->buildMock();

        $request = new Request;
        $requestAware->setRequest($request);
        $this->assertInstanceOf(
            Request::class,
            $this->reflectionCall($requestAware, 'getRequest')
        );
    }
}
