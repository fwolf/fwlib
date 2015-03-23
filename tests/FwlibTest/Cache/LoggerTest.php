<?php
namespace FwlibTest\Cache;

use Fwlib\Cache\Logger;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class LoggerTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | Logger
     */
    protected function buildMock()
    {
        $mock = $this->getMock(
            Logger::class,
            null
        );

        return $mock;
    }


    public function testLog()
    {
        $logger = $this->buildMock();

        $logger->log('get', 'cache key', true);
        $this->assertEquals(1, count($logger->getLogs()));
    }
}
