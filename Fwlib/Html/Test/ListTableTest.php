<?php
namespace Fwlib\Html\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Bridge\Smarty;
use Fwlib\Html\ListTable;

/**
 * Test for Fwlib\Html\ListTable
 *
 * @package     Fwlib\Html\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-11-26
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
        $data = array(
            array(
                'uuid'  => '1',
                'title' => 'tom',
                'age'   => 20,
            ),
            array(
                'uuid'  => '2',
                'title' => 'jack',
                'age'   => 30,
            ),
            array(
                'uuid'  => '3',
                'title' => 'smith',
                'age'   => 40,
            ),
        );
        $title = array(
            'title' => 'Name',
            'age'   => 'Current Age',
            'credit'    => 'Money',
        );


        $this->lt->setConfig('fitMode', ListTable::FIT_TO_TITLE);
        $this->lt->setData($data, $title);
        $x = array(
            array(
                'title' => 'tom',
                'age'   => 20,
                'credit'    => '&nbsp;',
            ),
            array(
                'title' => 'jack',
                'age'   => 30,
                'credit'    => '&nbsp;',
            ),
            array(
                'title' => 'smith',
                'age'   => 40,
                'credit'    => '&nbsp;',
            ),
        );
        $this->assertEqualArray($x, $this->reflectionGet($this->lt, 'listData'));


        $this->lt->setConfig('fitMode', ListTable::FIT_TO_DATA);
        $this->lt->setData($data, $title);
        $x = array(
            'title' => 'Name',
            'age'   => 'Current Age',
            'uuid'  => 'uuid',  // Add later, so on last position
        );
        $this->assertEqualArray($x, $this->reflectionGet($this->lt, 'listTitle'));


        $this->lt->setConfig('fitMode', ListTable::FIT_INSECTION);
        $this->lt->setData($data, $title);
        $x = array(
            array(
                'title' => 'tom',
                'age'   => 20,
            ),
            array(
                'title' => 'jack',
                'age'   => 30,
            ),
            array(
                'title' => 'smith',
                'age'   => 40,
            ),
        );
        $y = array(
            'title' => 'Name',
            'age'   => 'Current Age',
        );
        $this->assertEqualArray($x, $this->reflectionGet($this->lt, 'listData'));
        $this->assertEqualArray($y, $this->reflectionGet($this->lt, 'listTitle'));


        $this->lt->setConfig('fitMode', ListTable::FIT_UNION);
        $this->lt->setData($data, $title);
        $x = array(
            array(
                'uuid'  => '1',
                'title' => 'tom',
                'age'   => 20,
                'credit'    => '&nbsp;',
            ),
            array(
                'uuid'  => '2',
                'title' => 'jack',
                'age'   => 30,
                'credit'    => '&nbsp;',
            ),
            array(
                'uuid'  => '3',
                'title' => 'smith',
                'age'   => 40,
                'credit'    => '&nbsp;',
            ),
        );
        $y = array(
            'title' => 'Name',
            'age'   => 'Current Age',
            'credit'    => 'Money',
            'uuid'  => 'uuid',
        );
        $this->assertEqualArray($x, $this->reflectionGet($this->lt, 'listData'));
        $this->assertEqualArray($y, $this->reflectionGet($this->lt, 'listTitle'));
    }
}
