<?php
namespace FwlibTest\Web;

use Fwlib\Web\AbstractView;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractViewTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | AbstractView
     */
    protected function buildMock()
    {
        $mock = $this->getMock(AbstractView::class, null);

        return $mock;
    }


    public function testConstructor()
    {
        $view = $this->buildMock();

        $this->assertNotEmpty($this->reflectionCall($view, 'getOutputHeader'));
        $this->assertNotEmpty($this->reflectionCall($view, 'getOutputFooter'));
    }
}
