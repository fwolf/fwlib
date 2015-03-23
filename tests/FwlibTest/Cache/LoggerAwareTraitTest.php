<?php
namespace FwlibTest\Cache;

use Fwlib\Cache\Logger;
use Fwlib\Cache\LoggerAwareTrait;
use Fwlib\Cache\LoggerInterface;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class LoggerAwareTraitTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | LoggerAwareTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(LoggerAwareTrait::class)
            ->setMethods(null)
            ->getMockForTrait();

        return $mock;
    }


    public function testSetAndGet()
    {
        /** @var MockObject|LoggerInterface $logger */
        $logger = $this->getMock(Logger::class, ['log']);
        $logger->expects($this->once())
            ->method('log');

        $loggerAware = $this->buildMock();

        // Before logger instance set
        $this->reflectionCall($loggerAware, 'log', ['get', 'dummy', 'true']);

        // After logger instance set
        $loggerAware->setLogger($logger);
        $this->reflectionCall($loggerAware, 'log', ['get', 'dummy', 'true']);

        $this->assertInstanceOf(
            LoggerInterface::class,
            $loggerAware->getLogger()
        );
    }
}
