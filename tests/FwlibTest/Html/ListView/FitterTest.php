<?php
namespace FwlibTest\Html\ListView;

use Fwlib\Html\ListView\FitMode;
use Fwlib\Html\ListView\Fitter;
use Fwlib\Html\ListView\ListDto;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class FitterTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | Fitter
     */
    protected function buildMock()
    {
        $mock = $this->getMock(Fitter::class, null);

        /** @var Fitter $mock */
        $mock->setEmptyFiller('&amp;');

        return $mock;
    }


    /**
     * @return  ListDto
     */
    protected function getListDto()
    {
        $listDto = new ListDto();

        $listDto->setBody([
            [
                'id'    => '1',
                'title' => 'tom',
                'age'   => 20,
            ],
            [
                'id'    => '2',
                'title' => 'jack',
                'age'   => 30,
            ],
            [
                'id'    => '3',
                'title' => 'smith',
                'age'   => 40,
            ],
        ]);

        $listDto->setHead([
            'title'  => 'Name',
            'age'    => 'Current Age',
            'credit' => 'Money',
        ]);

        return $listDto;
    }


    public function testFitIntersection()
    {
        $fitter = $this->buildMock();
        $listDto = $this->getListDto();

        $fitter->setMode(FitMode::INTERSECTION)
            ->fit($listDto);

        // Key 'id' is removed.
        $body = [
            [
                'title' => 'tom',
                'age'   => 20,
            ],
            [
                'title' => 'jack',
                'age'   => 30,
            ],
            [
                'title' => 'smith',
                'age'   => 40,
            ],
        ];
        // Key 'credit' is removed.
        $head = [
            'title' => 'Name',
            'age'   => 'Current Age',
        ];
        $this->assertEqualArray($body, $listDto->getBody());
        $this->assertEqualArray($head, $listDto->getHead());
    }


    public function testFitToData()
    {
        $fitter = $this->buildMock();
        $listDto = $this->getListDto();

        $listDto = $fitter->setMode(FitMode::TO_DATA)
            ->fit($listDto);

        // Key 'id' is added, 'credit' is removed.
        $head = [
            'title' => 'Name',
            'age'   => 'Current Age',
            'id'    => 'Id',  // At last position because it added later
        ];
        $this->assertEqualArray($head, $listDto->getHead());
    }


    public function testFitToTitle()
    {
        $fitter = $this->buildMock();
        $listDto = $this->getListDto();

        $listDto = $fitter->setMode(FitMode::TO_TITLE)
            ->fit($listDto);

        // Key 'id' is removed, 'credit' is added.
        $body = [
            [
                'title'  => 'tom',
                'age'    => 20,
                'credit' => '&amp;',
            ],
            [
                'title'  => 'jack',
                'age'    => 30,
                'credit' => '&amp;',
            ],
            [
                'title'  => 'smith',
                'age'    => 40,
                'credit' => '&amp;',
            ],
        ];
        $this->assertEqualArray($body, $listDto->getBody());
    }


    public function testFitUnion()
    {
        $fitter = $this->buildMock();
        $listDto = $this->getListDto();

        $fitter->setMode(FitMode::UNION)
            ->fit($listDto);

        // Key 'credit' is added.
        $body = [
            [
                'id'     => '1',
                'title'  => 'tom',
                'age'    => 20,
                'credit' => '&amp;',
            ],
            [
                'id'     => '2',
                'title'  => 'jack',
                'age'    => 30,
                'credit' => '&amp;',
            ],
            [
                'id'     => '3',
                'title'  => 'smith',
                'age'    => 40,
                'credit' => '&amp;',
            ],
        ];
        // Key 'id' is added.
        $head = [
            'title'  => 'Name',
            'age'    => 'Current Age',
            'credit' => 'Money',
            'id'     => 'Id',
        ];
        $this->assertEqualArray($body, $listDto->getBody());
        $this->assertEqualArray($head, $listDto->getHead());
    }


    public function testFitWithEmptyOrSame()
    {
        $fitter = $this->buildMock();

        $listDto = (new ListDto())->setBody([])->setHead([]);

        $fitter->fit($listDto);
        $this->assertEqualArray([], $listDto->getBody());
        $this->assertEqualArray([], $listDto->getHead());


        $body = [
            [
                'title' => 'tom',
                'age'   => 20,
            ],
            [
                'title' => 'jack',
                'age'   => 30,
            ],
            [
                'title' => 'smith',
                'age'   => 40,
            ],
        ];
        // Key 'credit' is removed.
        $head = [
            'title' => 'Name',
            'age'   => 'Current Age',
        ];
        $listDto = (new ListDto())->setBody($body)->setHead($head);

        $fitter->fit($listDto);
        $this->assertEqualArray($body, $listDto->getBody());
        $this->assertEqualArray($head, $listDto->getHead());
    }


    /**
     * @expectedException \Fwlib\Html\ListView\Exception\InvalidFitModeException
     */
    public function testFitWithInvalidMode()
    {
        $fitter = $this->buildMock();
        $listDto = $this->getListDto();

        // Invalid mode
        $fitter->setMode(9999)
            ->fit($listDto);
    }
}
