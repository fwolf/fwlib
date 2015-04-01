<?php
namespace FwlibTest\Html\ListView;

use Fwlib\Html\ListView\Renderer;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class RendererTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | Renderer
     */
    protected function buildMock()
    {
        $mock = $this->getMock(
            Renderer::class,
            null
        );

        /** @var Renderer $mock */
        $mock->setClass('listTable')
            ->setId(1);

        return $mock;
    }


    public function testGetHtml()
    {
        $renderer = $this->buildMock();

        $renderer->setPreContent('<!-- pre content -->');
        $renderer->setPostContent('<!-- post content -->');
        $renderer->setConfig('showTopPager', true);
        $renderer->setConfig('showBottomPager', true);

        $html = "
<!-- pre content -->

<div class='listTable' id='listTable-1'>

  <!-- top pager -->

  <!-- head -->

  <!-- body -->

  <!-- bottom pager -->

</div>

<!-- post content -->
";
        $this->assertEquals($html, $renderer->getHtml());
    }
}
