<?php
namespace FwlibTest\Html;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Bridge\Smarty;
use Fwlib\Html\ListTable;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ListTableTest extends PHPunitTestCase
{
    private $lt = null;


    public function __construct()
    {
        $tpl = new Smarty;

        $this->lt = new ListTable($tpl);
    }


    public function testFitDataWithTitle()
    {
        $this->lt->setConfig('fitEmpty', '&nbsp;');
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


        $this->lt->setConfig('fitMode', ListTable::FIT_TO_TITLE);
        $this->lt->setData($data, $title);
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
        $this->assertEqualArray($x, $this->reflectionGet($this->lt, 'listData'));


        $this->lt->setConfig('fitMode', ListTable::FIT_TO_DATA);
        $this->lt->setData($data, $title);
        $x = [
            'title' => 'Name',
            'age'   => 'Current Age',
            'uuid'  => 'uuid',  // Add later, so on last position
        ];
        $this->assertEqualArray($x, $this->reflectionGet($this->lt, 'listTitle'));


        $this->lt->setConfig('fitMode', ListTable::FIT_INTERSECTION);
        $this->lt->setData($data, $title);
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
        $this->assertEqualArray($x, $this->reflectionGet($this->lt, 'listData'));
        $this->assertEqualArray($y, $this->reflectionGet($this->lt, 'listTitle'));


        $this->lt->setConfig('fitMode', ListTable::FIT_UNION);
        $this->lt->setData($data, $title);
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
        $this->assertEqualArray($x, $this->reflectionGet($this->lt, 'listData'));
        $this->assertEqualArray($y, $this->reflectionGet($this->lt, 'listTitle'));
    }
}
