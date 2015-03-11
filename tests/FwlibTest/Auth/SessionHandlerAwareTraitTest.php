<?php
namespace FwlibTest\Auth;

use Fwlib\Auth\SessionHandlerAwareTrait;
use Fwlib\Auth\SessionHandlerInterface;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class SessionHandlerAwareTraitTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | SessionHandlerAwareTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(
            SessionHandlerAwareTrait::class
        )
            ->setMethods(null)
            ->getMockForTrait();

        return $mock;
    }


    /**
     * @return MockObject | SessionHandlerInterface
     */
    protected function buildSessionHandlerMock()
    {
        $mock = $this->getMock(
            SessionHandlerInterface::class,
            []
        );

        return $mock;
    }


    public function testSetGet()
    {
        $handlerAware = $this->buildMock();
        $handler = $this->buildSessionHandlerMock();

        $handlerAware->setSessionHandler($handler);
        $this->assertInstanceOf(
            SessionHandlerInterface::class,
            $this->reflectionCall($handlerAware, 'getSessionHandler')
        );
    }
}
