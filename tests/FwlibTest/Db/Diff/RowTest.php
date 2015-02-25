<?php
namespace FwlibTest\Db\Diff;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Db\Diff\Row;

/**
 * @copyright   Copyright 2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class RowTest extends PHPunitTestCase
{
    public function testAccessors()
    {
        $old = [
            'uuid'    => 'uuid value',
            'column'  => 1,
            'column2' => 'will be removed',
        ];
        $new = [
            'uuid'    => 'uuid value',
            'column'  => 2,
            'column2' => 'will be removed',
        ];
        $row = new Row('table', 'uuid', $old, $new);

        $this->assertEquals('table', $row->getTable());
        $this->assertEquals('uuid', $row->getPrimaryKey());

        unset($old['column2']);
        unset($new['column2']);
        $this->assertEqualArray($old, $row->getOld());
        $this->assertEqualArray($new, $row->getNew());

        $this->assertEquals(1, $row->getOld('column'));
        $this->assertEquals(2, $row->getNew('column'));

        $this->assertEqualArray(
            ['column' => 1],
            $row->getOldWithoutPrimaryKey()
        );
        $this->assertEqualArray(
            ['column' => 2],
            $row->getNewWithoutPrimaryKey()
        );
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Diff mode determine failed
     */
    public function testInvalidMode()
    {
        $row = new Row(
            'table',
            'pk',
            null,
            null
        );
    }


    public function testMode()
    {
        $row = new Row(
            'table',
            'pk',
            null,
            ['column' => 1]
        );

        $this->assertEquals('INSERT', $row->getMode());

        $row = new Row(
            'table',
            'pk',
            ['column' => 1],
            null
        );

        $this->assertEquals('DELETE', $row->getMode());

        $row = new Row(
            'table',
            'pk',
            ['column' => 1],
            ['column' => 2]
        );

        $this->assertEquals('UPDATE', $row->getMode());
    }
}
