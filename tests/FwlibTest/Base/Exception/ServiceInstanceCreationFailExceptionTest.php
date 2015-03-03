<?php
namespace FwlibTest\Base\Exception;

use Fwlib\Base\Exception\ServiceInstanceCreationFailException;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ServiceInstanceCreationFailExceptionTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | ServiceInstanceCreationFailException
     */
    protected function buildMock()
    {
        $mock = $this->getMock(
            ServiceInstanceCreationFailException::class,
            null
        );

        return $mock;
    }


    public function testSetService()
    {
        $exception = $this->buildMock();

        $this->assertInstanceOf(
            'Exception',
            $exception->setServiceName('fooBar')
        );

        $this->assertRegExp('/ fooBar$/', $exception->getMessage());
    }
}
