<?php
namespace Fwlib\Db\Diff\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Db\Diff\Row;
use Fwlib\Db\Diff\RowSet;

/**
 * @copyright   Copyright 2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class RowSetTest extends PHPunitTestCase
{
    public function testAddRow()
    {
        $row = new Row('table', 'uuid', null, array('column' => 'value'));

        $rowSet = new RowSet();
        $rowSet->addRow($row);

        $this->assertEquals(1, $rowSet->getRowCount());

        $rows = $rowSet->getRows();

        $this->assertEquals(
            json_encode($row),
            json_encode($rows[0])
        );
    }


    public function testExecuteStatus()
    {
        $rowSet = new RowSet;

        $this->assertFalse($rowSet->isExecuted());
        $this->assertFalse($rowSet->isCommitted());
        $this->assertFalse($rowSet->isRollbacked());

        $rowSet->setCommitted();

        $this->assertTrue($rowSet->isExecuted());
        $this->assertTrue($rowSet->isCommitted());
        $this->assertFalse($rowSet->isRollbacked());

        $rowSet->setRollbacked();

        $this->assertTrue($rowSet->isExecuted());
        $this->assertFalse($rowSet->isCommitted());
        $this->assertTrue($rowSet->isRollbacked());
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid json
     */
    public function testFromJsonWithoutExecuteStatus()
    {
        $json =
            '{"rowCount":1,"rows":[{"table":"table",' .
            '"old":null,"new":{"column":"value"}}]}';

        $rowSet = new RowSet($json);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid json
     */
    public function testFromJsonWithRowInfoError()
    {
        $json =
            '{"rowCount":1,"executeStatus":0,"rows":[{"table":"table",' .
            '"old":null,"new":{"column":"value"}}]}';

        $rowSet = new RowSet($json);
    }


    public function testToAndFromJson()
    {
        $json =
            '{"rowCount":1,"executeStatus":0,"rows":[{"table":"table",' .
            '"primaryKey":"uuid","old":null,"new":{"column":"value"}}]}';

        $rowSet = new RowSet();
        $rowSet->addRow(
            new Row('table', 'uuid', null, array('column' => 'value'))
        );

        $this->assertEquals($json, $rowSet->toJson());

        $rowSet = new RowSet($json);

        $this->assertEquals($json, $rowSet->toJson());
    }
}
