<?php
namespace FwlibTest\Html\ListView;

use Fwlib\Html\ListView\ListDto;
use Fwlib\Html\ListView\Renderer;
use Fwlib\Html\ListView\Request;
use Fwlib\Html\ListView\RequestInterface;
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
        /** @var MockObject|Renderer $renderer */
        $renderer = $this->getMock(
            Renderer::class,
            ['getPager']
        );

        $renderer->expects($this->any())
            ->method('getPager')
            ->willReturnOnConsecutiveCalls(
                '<!-- top pager -->',
                '<!-- bottom pager -->'
            );

        $renderer->setClass('listTable')->setId(1);

        $renderer->setPreContent('<!-- pre content -->');
        $renderer->setPostContent('<!-- post content -->');
        $renderer->setConfig('showTopPager', true);
        $renderer->setConfig('showBottomPager', true);

        $html = "<!-- pre content -->

<div class='listTable' id='listTable-1'>

  <!-- top pager -->

  <!-- head -->

  <!-- body -->

  <!-- bottom pager -->

</div>

<!-- post content -->";
        $this->assertEquals($html, $renderer->getHtml());
    }


    public function testGetPager()
    {
        /** @var MockObject|Renderer $renderer */
        $renderer = $this->getMock(
            Renderer::class,
            ['getSafePage', 'getSafePageSize']
        );
        $renderer->expects($this->any())
            ->method('getSafePage')
            ->willReturn(2);
        $renderer->expects($this->any())
            ->method('getSafePageSize')
            ->willReturn(10);

        $renderer->setClass('listTable')->setId(1);

        /** @var MockObject|RequestInterface $request */
        $request = $this->getMock(Request::class, ['getBaseUrl']);
        $request->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn('http://domain.tld/?foo=1&Bar=2');
        $renderer->setRequest($request);

        $listDto = new ListDto;
        $listDto->setTotalRows(42);
        $renderer->setListDto($listDto);

        $html = "<div class='listTable__pager' id='listTable-1__pager--top'>
  <a href='http://domain.tld/?foo=1&Bar=2&p=1'>首页</a> | " . "
  <a href='http://domain.tld/?foo=1&Bar=2&p=1'>上一页</a> | " . "
  <a href='http://domain.tld/?foo=1&Bar=2&p=3'>下一页</a> | " . "
  <a href='http://domain.tld/?foo=1&Bar=2&p=5'>尾页</a> | " . "
  共42条信息，每页显示10条，当前为第2/5页 | " . "
  转到第
  <form method='get' action='http://domain.tld/'>
    <input type='hidden' name='foo' value='1' />
    <input type='hidden' name='Bar' value='2' />
    <input type='text' name='p' value='2' size='1' />
    页
    <input type='submit' value='转' />
  </form>
</div>";
        $this->assertEquals(
            $html,
            $this->reflectionCall($renderer, 'getPager', ['top'])
        );
    }


    public function testGetPagerJumpFormWithEmptyUrl()
    {
        $renderer = $this->buildMock();

        $html = "转到第
<form method='get' action=''>
  <input type='text' name='p' value='1' size='1' />
  页
  <input type='submit' value='转' />
</form>";
        $this->assertEquals(
            $html,
            $this->reflectionCall(
                $renderer,
                'getPagerJumpForm',
                ['', 1, 5, 'p']
            )
        );
    }


    public function testGetSafePage()
    {
        $renderer = $this->buildMock();

        /** @var MockObject|RequestInterface $request */
        $request = $this->getMock(Request::class, ['getPage']);
        $request->expects($this->any())
            ->method('getPage')
            ->willReturn(3);
        $renderer->setRequest($request);

        // In max page
        $this->assertEquals(
            3,
            $this->reflectionCall($renderer, 'getSafePage', ['3'])
        );

        // Exceed max page
        $this->assertEquals(
            2,
            $this->reflectionCall($renderer, 'getSafePage', ['2'])
        );
    }


    public function testGetSafePageSize()
    {
        $renderer = $this->buildMock();

        /** @var MockObject|RequestInterface $request */
        $request = $this->getMock(Request::class, ['getPageSize']);
        $request->expects($this->any())
            ->method('getPageSize')
            ->willReturnOnConsecutiveCalls(30, -1);
        $renderer->setRequest($request);

        $renderer->setConfig('pageSize', 20);

        // Use request
        $this->assertEquals(
            30,
            $this->reflectionCall($renderer, 'getSafePageSize')
        );

        // Use config
        $this->assertEquals(
            20,
            $this->reflectionCall($renderer, 'getSafePageSize')
        );
    }
}
