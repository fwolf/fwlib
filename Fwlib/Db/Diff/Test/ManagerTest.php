<?php
namespace Fwlib\Db\Diff\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Db\Diff\Executor;
use Fwlib\Db\Diff\Manager;
use Fwlib\Db\Diff\Row;
use Fwlib\Db\Diff\RowSet;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2012-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 */
class ManagerTest extends PHPUnitTestCase
{
    /**
     * A dummy table name for backword compatible, the old test uses db
     */
    protected static $tableUser = 'test_user';


    protected function buildMock($rowSet = null)
    {
        $executor = new Executor(null);

        $manager = new Manager($rowSet);
        $manager->setExecutor($executor);

        return $manager;
    }


    /**
     * Test disabled because we removed Adodb dependence, and use solid uuid
     *
     * @expectedException Exception
     * @expectedExceptionMessage has no primary key
     */
    public function disabledTestAddRowWithTableHaveNoPrimaryKey()
    {
        $manager = $this->buildMock();

        $dataNew = array(
            'title' => 'User Title',
        );

        $manager->addRow('table_not_exist', null, $dataNew);
    }


    public function testAddRow()
    {
        $manager = $this->buildMock();

        $old = array(
            'uuid'  => 'uuid',
            'column' => 1,
        );
        $new = array(
            'uuid'  => 'uuid',
            'column' => 2,
        );

        // Insert, update, delete
        $manager->addRow('table', null, $old);
        $manager->addRow('table', $old, $new);
        $manager->addRow('table', $new, null);

        $this->assertEquals(3, $manager->getRowSet()->getRowCount());
    }


    public function testAddRows()
    {
        $manager = $this->buildMock();

        $manager->addRows(self::$tableUser, null, null);

        $this->assertEquals(0, $manager->getRowSet()->getRowCount());


        // Insert, update, delete
        $old = array(
            'uuid'  => 'uuid',
            'column' => 1,
        );
        $new = array(
            'uuid'  => 'uuid',
            'column' => 2,
        );
        $oldRows = array(null, $old, $new);
        $newRows = array($old, $new, null);

        $manager->addRows('table', $oldRows, $newRows);

        $this->assertEquals(3, $manager->getRowSet()->getRowCount());


        // Add 2 rows
        $newRows = array($old, $new);

        $manager->renew()->addRows('table', null, $newRows);

        $this->assertEquals(2, $manager->getRowSet()->getRowCount());
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage already executed
     */
    public function testAddRowWithExecutedRowSet()
    {
        $manager = $this->buildMock();

        $manager->getRowSet()->setCommitted();

        $manager->addRow('table', null, null);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Primary key uuid is not included
     */
    public function testAddRowWithoutPrimaryKey()
    {
        $manager = $this->buildMock();

        $new = array(
            'title' => 'Title',
        );

        $manager->addRow('table', null, $new);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage No RowSet set
     */
    public function testAddRowWithoutRowSet()
    {
        $manager = $this->buildMock();

        $manager->setRowSet(null);

        $manager->addRow('table', null, null);
    }


    public function testAddRowWithSameOldAndNew()
    {
        $manager = $this->buildMock();

        $old = array(
            'uuid'  => 'uuid',
            'column' => 1,
        );

        $manager->addRow('table', $old, $old);

        $this->assertEquals(0, $manager->getRowSet()->getRowCount());

        $manager->addRow('table', null, null);

        $this->assertEquals(0, $manager->getRowSet()->getRowCount());
    }


    public function testCommitWithEmptyRowSet()
    {
        $manager = $this->buildMock();
        $manager->commit();

        // No db connection bind to Executor, so no error reported is pass
        $this->assertTrue(true);
    }


    public function testExecuteWithEmptyDataNew()
    {
        $manager = $this->buildMock();
        $manager->execute();

        // No db connection bind to Executor, so no error reported is pass
        $this->assertTrue(true);
    }


    public function testGetExecutor()
    {
        $manager = new Manager;

        $this->assertNull($this->reflectionGet($manager, 'executor'));

        $this->assertInstanceOf(
            'Fwlib\Db\Diff\ExecutorInterface',
            $this->reflectionCall($manager, 'getExecutor')
        );
    }


    public function testRollbackWithEmptyRowSet()
    {
        $manager = $this->buildMock();
        $manager->rollback();

        // No db connection bind to Executor, so no error reported is pass
        $this->assertTrue(true);
    }


    public function testSetRowSet()
    {
        // Throuth constructor
        $row = new Row('table', 'uuid', null, array('uuid' => 'value'));
        $rowSet = new RowSet();
        $rowSet->addRow($row);

        $manager = $this->buildMock($rowSet);

        $this->assertEquals(1, $manager->getRowSet()->getRowCount());


        // Then use setter method and json
        $manager = $this->buildMock($rowSet->toJson());

        $this->assertEquals(1, $manager->getRowSet()->getRowCount());
    }
}
