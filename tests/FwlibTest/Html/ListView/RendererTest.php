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


    public function testAddOrderByLink()
    {
        $renderer = $this->buildMock();

        /** @var MockObject|Request $request */
        $request = $this->getMock(Request::class, ['getBaseUrl']);
        $request->expects($this->any())
            ->method('getBaseUrl')
            ->willReturn('http://domain.tld/');
        $request->setOrderByParameter('ob')
            ->setOrderByDirectionParameter('od');
        $renderer->setRequest($request);

        $this->assertEquals(
            "<a href='http://domain.tld/?ob=foo&od=DESC'>head(asc)</a>",
            $this->reflectionCall(
                $renderer,
                'addOrderByLink',
                ['foo', 'head(asc)', 'ASC']
            )
        );
    }


    public function testAddOrderByText()
    {
        $renderer = $this->buildMock();
        $renderer->setConfig('orderByTextAsc', '[Ascending]');

        $this->assertEquals(
            'head[Ascending]',
            $this->reflectionCall($renderer, 'addOrderByText', ['head', 'ASC'])
        );
    }


    public function testGetHtml()
    {
        /** @var MockObject|Renderer $renderer */
        $renderer = $this->getMock(
            Renderer::class,
            ['getListTable', 'getPager']
        );

        $renderer->expects($this->any())
            ->method('getListTable')
            ->willReturn('<!-- table -->');

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

  <!-- table -->

  <!-- bottom pager -->

</div>

<!-- post content -->";
        $this->assertEquals($html, $renderer->getHtml());
    }


    public function testGetListBody()
    {
        $renderer = $this->buildMock();

        $renderer->setConfig('trAppend', [1 => "dir='ltr'"]);
        $renderer->setConfig('tdAppend', ['foo' => 'nowrap']);

        $listDto = (new ListDto())->setBody([
            [
                'dummy' => 'Dummy1',
                'foo'   => 'Foo1',
                'bar'   => 'Bar1',
            ],
            [
                'dummy' => 'Dummy2',
                'foo'   => 'Foo2',
                'bar'   => 'Bar2',
            ],
            [
                'dummy' => 'Dummy3',
                'foo'   => 'Foo3',
                'bar'   => 'Bar3',
            ],
        ]);
        $renderer->setListDto($listDto);

        $html = "<tbody>
  <tr class='listTable__body__tr'>
    <td class='listTable__td__dummy' id='listTable-1__td__dummy--0'>
      Dummy1
    </td>
    <td class='listTable__td__foo' id='listTable-1__td__foo--0' nowrap>
      Foo1
    </td>
    <td class='listTable__td__bar' id='listTable-1__td__bar--0'>
      Bar1
    </td>
  </tr>
  <tr class='listTable__body__tr' dir='ltr'>
    <td class='listTable__td__dummy' id='listTable-1__td__dummy--1'>
      Dummy2
    </td>
    <td class='listTable__td__foo' id='listTable-1__td__foo--1' nowrap>
      Foo2
    </td>
    <td class='listTable__td__bar' id='listTable-1__td__bar--1'>
      Bar2
    </td>
  </tr>
  <tr class='listTable__body__tr'>
    <td class='listTable__td__dummy' id='listTable-1__td__dummy--2'>
      Dummy3
    </td>
    <td class='listTable__td__foo' id='listTable-1__td__foo--2' nowrap>
      Foo3
    </td>
    <td class='listTable__td__bar' id='listTable-1__td__bar--2'>
      Bar3
    </td>
  </tr>
</tbody>";
        $this->assertEquals(
            $html,
            $this->reflectionCall($renderer, 'getListBody')
        );
    }


    public function testGetListHead()
    {
        $renderer = $this->buildMock();
        $renderer->setConfig('thAppend', ['foo' => 'nowrap']);

        $request = $this->getMock(Request::class, []);
        $renderer->setRequest($request);

        $listDto = (new ListDto())->setHead([
            'dummy' => 'Dummy',
            'foo'   => 'Foo',
            'bar'   => 'Bar',
        ]);
        $renderer->setListDto($listDto);

        $html = "<thead>
  <tr class='listTable__head__tr'>
    <th id='listTable-1__th__dummy'>Dummy</th>
    <th id='listTable-1__th__foo' nowrap>Foo</th>
    <th id='listTable-1__th__bar'>Bar</th>
  </tr>
</thead>";
        $this->assertEquals(
            $html,
            $this->reflectionCall($renderer, 'getListHead')
        );
    }


    public function testGetListHeadText()
    {
        $renderer = $this->buildMock();
        $renderer->setConfig('orderByTextAsc', '[Asc]');

        /** @var MockObject|Request $request */
        $request = $this->getMock(Request::class, ['getBaseUrl', 'getOrderBy']);

        $request->expects($this->any())
            ->method('getBaseUrl')
            ->willReturn('http://domain.tld/');

        $request->expects($this->any())
            ->method('getOrderBy')
            ->willReturnOnConsecutiveCalls(null, ['foo' => 'asc']);

        $request->setOrderByParameter('ob')
            ->setOrderByDirectionParameter('od');
        $renderer->setRequest($request);


        // Empty order by
        $this->assertEquals(
            "head",
            $this->reflectionCall($renderer, 'getListHeadText', ['foo', 'head'])
        );

        $this->assertEquals(
            "<a href='http://domain.tld/?ob=foo&od=DESC'>head[Asc]</a>",
            $this->reflectionCall($renderer, 'getListHeadText', ['foo', 'head'])
        );
    }


    public function testGetListTable()
    {
        /** @var MockObject|Renderer $renderer */
        $renderer = $this->getMock(
            Renderer::class,
            ['getListHead', 'getListBody']
        );

        $renderer->expects($this->any())
            ->method('getListHead')
            ->willReturn("<thead>\n</thead>");

        $renderer->expects($this->any())
            ->method('getListBody')
            ->willReturn("<tbody>\n</tbody>");

        $renderer->setClass('listTable')->setId(1);

        $html = "<table class='listTable__table' id='listTable-1__table'>
  <thead>
  </thead>

  <tbody>
  </tbody>
</table>";
        $this->assertEquals(
            $html,
            $this->reflectionCall($renderer, 'getListTable')
        );
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
        $listDto->setRowCount(42);
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
}
