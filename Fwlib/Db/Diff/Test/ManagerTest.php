<?php
namespace Fwlib\Db\Diff\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Db\Diff\Executor;
use Fwlib\Db\Diff\Manager;
use Fwlib\Db\Diff\RowSet;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2012-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2012-12-10
 */
class ManagerTest extends PHPUnitTestCase
{
    /**
     * A dummy table name for backword compatible, the old test uses db
     */
    protected static $tableUser = 'test_user';


    protected function buildMock()
    {
        $executor = new Executor(null);

        $manager = new Manager();
        $manager->setExecutor($executor);

        return $manager;
    }


    /**
     * Test disabled because we removed Adodb dependence, and use solid uuid
     *
     * @expectedException Exception
     * @expectedExceptionMessage has no primary key
     */
    public function tesAddRowWithTableHaveNoPrimaryKey()
    {
        $manager = $this->buildMock();

        $dataNew = array(
            'title' => 'User Title',
        );

        $manager->addRow('table_not_exist', null, $dataNew);
    }


    public function testAddRows()
    {
        $manager = $this->buildMock();

        $manager->addRows(self::$tableUser, null, null);

        $this->assertEquals(0, $manager->getRowSet()->getRowCount());

        $manager->addRows(
            self::$tableUser,
            array(1 => null, 2 => null),
            array(1 => null, 2 => null)
        );

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


    public function testRollbackWithEmptyRowSet()
    {
        $manager = $this->buildMock();
        $manager->rollback();

        // No db connection bind to Executor, so no error reported is pass
        $this->assertTrue(true);
    }
}
