<?php
namespace FwlibTest\Html\ListView;

use Fwlib\Html\ListView\AbstractRetriever;
use Fwlib\Html\ListView\FitMode;
use Fwlib\Html\ListView\Fitter;
use Fwlib\Html\ListView\ListDto;
use Fwlib\Html\ListView\ListView;
use Fwlib\Html\ListView\Renderer;
use Fwlib\Html\ListView\RendererInterface;
use Fwlib\Html\ListView\RequestInterface;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ListViewTest extends PHPUnitTestCase
{
    /**
     * @param   string[]    $methods
     * @return  MockObject|ListView
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMock(ListView::class, $methods);

        return $mock;
    }


    public function testAccessors()
    {
        $listView = $this->buildMock();

        $this->assertInstanceOf(
            RendererInterface::class,
            $this->reflectionCall($listView, 'getRenderer')
        );

        $this->assertInstanceOf(
            RequestInterface::class,
            $this->reflectionCall($listView, 'getRequest')
        );

        /** @var MockObject|Fitter $fitter */
        $fitter = $this->getMock(Fitter::class, []);
        $listView->setFitter($fitter);

        $listView->setRowCount(42);
    }


    public function testDecorateRows()
    {
        $listView = $this->buildMock();

        $listDto = new ListDto;
        $this->reflectionCall($listView, 'decorateRows', [$listDto]);
        $this->assertEmpty($listDto->getBody());

        // No decorator
        $listDto->setBody([['a', 'b']])
            ->setRowCount(1);
        $this->reflectionCall($listView, 'decorateRows', [$listDto]);
        $this->assertEqualArray([['a', 'b']], $listDto->getBody());

        $listView->setRowDecorator(function($row) {
            foreach ($row as &$val) {
                $val = strtoupper($val);
            }
            unset($val);

            return $row;
        });
        $this->reflectionCall($listView, 'decorateRows', [$listDto]);
        $this->assertEqualArray([['A', 'B']], $listDto->getBody());
    }


    public function testFitHeadAndBody()
    {
        $listView = $this->buildMock();

        $listView->setConfig('fitMode', FitMode::TO_TITLE);
        $listView->setConfig('fitEmptyFiller', '-');

        $listView->setHead(['F' => 'Foo', 'B' => 'Bar']);
        $listView->setBody([['F' => 'foo']]);

        $listDto = $this->reflectionCall($listView, 'getListDto');
        $this->reflectionCall($listView, 'fitHeadAndBody', [$listDto]);

        $this->assertEqualArray(
            [['F' => 'foo', 'B' => '-']],
            $listDto->getBody()
        );
    }


    public function testGetFilledListDto()
    {
        $listView = $this->buildMock();

        /** @var MockObject|AbstractRetriever $retriever */
        $retriever = $this->getMock(
            AbstractRetriever::class,
            ['getListBody', 'getRowCount']
        );
        $retriever->expects($this->once())
            ->method('getRowCount')
            ->willReturn(42);
        $listView->setRetriever($retriever);

        $listDto = $this->reflectionCall($listView, 'getFilledListDto');
        $this->assertEquals(42, $listDto->getRowCount());
    }


    public function testGetHtml()
    {
        $listView = $this->buildMock(
            ['fitHeadAndBody', 'decorateRows', 'render']
        );
        $listView->expects($this->once())
            ->method('fitHeadAndBody');
        $listView->expects($this->once())
            ->method('decorateRows');
        $listView->expects($this->once())
            ->method('render');

        $listView->getHtml();
    }


    public function testRender()
    {
        /** @var MockObject|Renderer $renderer */
        $renderer = $this->getMock(
            Renderer::class,
            ['setConfigInstance', 'setListDto', 'getHtml']
        );
        $renderer->expects($this->once())
            ->method('setConfigInstance')
            ->willReturnSelf();
        $renderer->expects($this->once())
            ->method('setListDto')
            ->willReturnSelf();
        $renderer->expects($this->once())
            ->method('getHtml');

        $listView = $this->buildMock();
        $listView->setRenderer($renderer);

        $this->reflectionCall($listView, 'render', [new ListDto()]);
    }


    public function testSetBody()
    {
        $listView = $this->buildMock();

        $listView->setBody([['key' => 'foo'], ['key' => 'bar']], true);
        /** @var ListDto $listDto */
        $listDto = $this->reflectionCall($listView, 'getListDto');
        $this->assertEquals(2, $listDto->getRowCount());

        $listView->reset();
        $listDto = $this->reflectionCall($listView, 'getListDto');
        $this->assertEquals(
            ListView::ROW_COUNT_NOT_SET,
            $listDto->getRowCount()
        );
    }
}
