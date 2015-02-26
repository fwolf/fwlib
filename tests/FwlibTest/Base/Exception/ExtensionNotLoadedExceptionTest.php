<?php
namespace FwlibTest\Base\Exception;

use Fwlib\Base\Exception\ExtensionNotLoadedException;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ExtensionNotLoadedExceptionTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | ExtensionNotLoadedException
     */
    protected function buildMock()
    {
        $mock = $this->getMock(
            ExtensionNotLoadedException::class,
            null
        );

        return $mock;
    }


    public function testSetExtension()
    {
        $exception = $this->buildMock();

        $this->assertInstanceOf(
            'Exception',
            $exception->setExtension('mcrypt')
        );

        $this->assertRegExp('/ mcrypt /', $exception->getMessage());
    }
}
