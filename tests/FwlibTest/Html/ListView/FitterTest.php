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

        $listDto->setData([
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

        $listDto->setTitle([
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
        $data = [
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
        $title = [
            'title' => 'Name',
            'age'   => 'Current Age',
        ];
        $this->assertEqualArray($data, $listDto->getData());
        $this->assertEqualArray($title, $listDto->getTitle());
    }


    public function testFitToData()
    {
        $fitter = $this->buildMock();
        $listDto = $this->getListDto();

        $listDto = $fitter->setMode(FitMode::TO_DATA)
            ->fit($listDto);

        // Key 'id' is added, 'credit' is removed.
        $title = [
            'title' => 'Name',
            'age'   => 'Current Age',
            'id'    => 'id',  // At last position because it added later
        ];
        $this->assertEqualArray($title, $listDto->getTitle());
    }


    public function testFitToTitle()
    {
        $fitter = $this->buildMock();
        $listDto = $this->getListDto();

        $listDto = $fitter->setMode(FitMode::TO_TITLE)
            ->fit($listDto);

        // Key 'id' is removed, 'credit' is added.
        $data = [
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
        $this->assertEqualArray($data, $listDto->getData());
    }


    public function testFitUnion()
    {
        $fitter = $this->buildMock();
        $listDto = $this->getListDto();

        $fitter->setMode(FitMode::UNION)
            ->fit($listDto);

        // Key 'credit' is added.
        $data = [
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
        $title = [
            'title'  => 'Name',
            'age'    => 'Current Age',
            'credit' => 'Money',
            'id'     => 'id',
        ];
        $this->assertEqualArray($data, $listDto->getData());
        $this->assertEqualArray($title, $listDto->getTitle());
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
