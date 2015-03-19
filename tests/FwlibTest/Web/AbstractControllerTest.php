<?php
namespace FwlibTest\Web;

use Fwlib\Web\AbstractController;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractControllerTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | AbstractController
     */
    protected function buildMock()
    {
        $mock = $this->getMock(AbstractController::class, null);

        return $mock;
    }


    /**
     * Test if AbstractController has syntax or inherit/use error
     */
    public function testConstructor()
    {
        $controller = $this->buildMock();

        $this->assertTrue(true || $controller);
    }
}
