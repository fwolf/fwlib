<?php
namespace FwlibTest\Html;

use Fwlib\Bridge\Smarty;
use Fwlib\Html\ListTable;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ListTableTest extends PHPUnitTestCase
{
    /**
     * @return ListTable
     */
    protected function buildMock()
    {
        $smarty = new Smarty;

        $listTable = new ListTable($smarty);

        return $listTable;
    }


    public function testFitDataWithTitle()
    {
        $listTable = $this->buildMock();

        $listTable->setConfig('fitEmpty', '&nbsp;');
        $data = [
            [
                'uuid'  => '1',
                'title' => 'tom',
                'age'   => 20,
            ],
            [
                'uuid'  => '2',
                'title' => 'jack',
                'age'   => 30,
            ],
            [
                'uuid'  => '3',
                'title' => 'smith',
                'age'   => 40,
            ],
        ];
        $title = [
            'title' => 'Name',
            'age'   => 'Current Age',
            'credit'    => 'Money',
        ];


        $listTable->setConfig('fitMode', ListTable::FIT_TO_TITLE);
        $listTable->setData($data, $title);
        $x = [
            [
                'title' => 'tom',
                'age'   => 20,
                'credit'    => '&nbsp;',
            ],
            [
                'title' => 'jack',
                'age'   => 30,
                'credit'    => '&nbsp;',
            ],
            [
                'title' => 'smith',
                'age'   => 40,
                'credit'    => '&nbsp;',
            ],
        ];
        $this->assertEqualArray($x, $this->reflectionGet($listTable, 'listData'));


        $listTable->setConfig('fitMode', ListTable::FIT_TO_DATA);
        $listTable->setData($data, $title);
        $x = [
            'title' => 'Name',
            'age'   => 'Current Age',
            'uuid'  => 'uuid',  // Add later, so on last position
        ];
        $this->assertEqualArray($x, $this->reflectionGet($listTable, 'listTitle'));


        $listTable->setConfig('fitMode', ListTable::FIT_INTERSECTION);
        $listTable->setData($data, $title);
        $x = [
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
        $y = [
            'title' => 'Name',
            'age'   => 'Current Age',
        ];
        $this->assertEqualArray($x, $this->reflectionGet($listTable, 'listData'));
        $this->assertEqualArray($y, $this->reflectionGet($listTable, 'listTitle'));


        $listTable->setConfig('fitMode', ListTable::FIT_UNION);
        $listTable->setData($data, $title);
        $x = [
            [
                'uuid'  => '1',
                'title' => 'tom',
                'age'   => 20,
                'credit'    => '&nbsp;',
            ],
            [
                'uuid'  => '2',
                'title' => 'jack',
                'age'   => 30,
                'credit'    => '&nbsp;',
            ],
            [
                'uuid'  => '3',
                'title' => 'smith',
                'age'   => 40,
                'credit'    => '&nbsp;',
            ],
        ];
        $y = [
            'title' => 'Name',
            'age'   => 'Current Age',
            'credit'    => 'Money',
            'uuid'  => 'uuid',
        ];
        $this->assertEqualArray($x, $this->reflectionGet($listTable, 'listData'));
        $this->assertEqualArray($y, $this->reflectionGet($listTable, 'listTitle'));
    }
}
