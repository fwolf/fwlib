<?php
namespace Fwlib\Db\Diff\Test;

use Fwlib\Db\Diff\Manager;
use Fwlib\Db\Diff\RowSet;
use Fwlib\Test\AbstractDbRelateTest;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2012-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2012-12-10
 */
class ManagerTest extends AbstractDbRelateTest
{
    protected static $dbUsing = 'default';

    protected $uuid1;
    protected $uuid2;
    protected $uuid3;

    public static $getErrorCode;
    public static $getErrorMessage;

    public function __construct()
    {
        $this->uuid1 = $this->generateUuid();
        $this->uuid2 = $this->generateUuid();
        $this->uuid3 = $this->generateUuid();
    }


    protected function buildMock()
    {
        $manager = new Manager(self::$db);

        return $manager;
    }


    protected function buildMockWithFakeDb()
    {
        $db = $this->getMockBuilder('Fwlib\Bridge\Adodb')
            ->disableOriginalConstructor()
            ->getMock(
                'Fwlib\Bridge\Adodb',
                array(
                    'BeginTrans', 'CommitTrans', 'RollbackTrans',
                    'getErrorCode', 'getErrorMessage',
                    'execute'
                )
            );

        $db->expects($this->any())
            ->method('getErrorCode')
            ->will($this->returnCallback(function () {
                return ManagerTest::$getErrorCode;
            }));

        $db->expects($this->any())
            ->method('getErrorMessage')
            ->will($this->returnCallback(function () {
                return ManagerTest::$getErrorMessage;
            }));

        $manager = new Manager();
        $manager->setDb($db);

        return $manager;
    }


    protected function generateUuid()
    {
        return UtilContainer::getInstance()->get('UuidBase36')->generate();
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage has no primary key
     */
    public function testAddRowWithTableHaveNoPrimaryKey()
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


    /**
     * @expectedException Exception
     * @expectedExceptionMessage already committed
     */
    public function testCommitAgain()
    {
        $manager = $this->buildMock();

        $json = '{
            "executeStatus": 1,
            "rows": [{
                "table": "' . self::$tableUser . '",
                "primaryKey": "uuid",
                "old": null,
                "new": {
                    "uuid": "' . $this->uuid1 . '"
                }
            }]
        }';
        $manager->setRowSet(new RowSet($json));

        $manager->commit();
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Db execute fail
     */
    public function testCommitWithDbFail()
    {
        $manager = $this->buildMockWithFakeDb();

        self::$getErrorCode = -1;
        self::$getErrorMessage = 'Db execute fail';

        $json = '{
            "rowCount": 0,
            "executeStatus": 0,
            "rows": [{
                "table": "' . self::$tableUser . '",
                "primaryKey": "uuid",
                "old": null,
                "new": {
                    "uuid": "' . $this->uuid1 . '"
                }
            }]
        }';
        $manager->setRowSet(new RowSet($json));

        $manager->commit();
    }


    public function testCommitWithEmptyRowSet()
    {
        $queryCount = self::$db->getQueryCount();

        $manager = $this->buildMock();
        $manager->commit();

        $this->assertEquals($queryCount, self::$db->getQueryCount());
    }


    public function testExecute()
    {
        $manager = $this->buildMock();

        // Normal insert
        $dataNew1 = array(
            'uuid'  => $this->uuid1,
            'title' => 'User Title',
            'age'   => 42,
            'credit'    => '0.42',
            'joindate'  => '2014-01-02',
        );
        $manager->addRow(self::$tableUser, null, $dataNew1);
        $manager->execute();

        $rowSet = $manager->getRowSet();
        $rows = $rowSet->getRows();
        $this->assertEquals(1, count($rows));
        $this->assertEquals('INSERT', $rows[0]->getMode());
        $this->assertEquals('uuid', $rows[0]->getPrimaryKey());
        $this->assertEquals(0, count($rows[0]->getOld()));
        $this->assertEquals(5, count($rows[0]->getNew()));
        $this->assertTrue($rowSet->isCommitted());


        // Insert with PK column only
        $dataNew2 = array(
            'uuid'  => $this->uuid2,
        );
        $manager->renew()->addRow(self::$tableUser, null, $dataNew2);
        $manager->execute();

        $rowSet = $manager->getRowSet();
        $this->assertEquals(1, $rowSet->getRowCount());
        $this->assertEquals(2, self::$db->getRowCount(self::$tableUser));


        // Update row with $uuid1, and delete row with $uuid2
        $dataNew1Changed = array(
            'uuid'  => $this->uuid1,
            'title' => 'User Title Changed',
            'age'   => 420,
            'credit'    => '4.2',
            'joindate'  => '2013-01-02',
        );
        $manager->renew()
            ->addRow(self::$tableUser, $dataNew1, $dataNew1Changed)
            ->addRow(self::$tableUser, $dataNew2, null)
            ->execute();

        $rowSet = $manager->getRowSet();
        $this->assertEquals(2, $rowSet->getRowCount());
        $this->assertEquals(
            420,
            self::$db->getByKey(self::$tableUser, $this->uuid1, 'age', 'uuid')
        );
        $this->assertEquals(1, self::$db->getRowCount(self::$tableUser));


        // Rollback last update and delete
        $manager->rollback();

        $this->assertTrue($rowSet->isRollbacked());
        $this->assertEquals(
            42,
            self::$db->getByKey(self::$tableUser, $this->uuid1, 'age', 'uuid')
        );
        $this->assertEquals(2, self::$db->getRowCount(self::$tableUser));


        // Then commit again
        $manager->commit();

        $this->assertTrue($rowSet->isCommitted());
        $this->assertEquals(
            420,
            self::$db->getByKey(self::$tableUser, $this->uuid1, 'age', 'uuid')
        );
        $this->assertEquals(1, self::$db->getRowCount(self::$tableUser));
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage RowSet is already executed
     */
    public function testExecuteAgain()
    {
        $manager = $this->buildMock();

        $json = '{
            "executeStatus": 1,
            "rows": [{
                "table": "' . self::$tableUser . '",
                "primaryKey": "uuid",
                "old": null,
                "new": {
                    "uuid": "' . $this->uuid1 . '"
                }
            }]
        }';
        $manager->setRowSet(new RowSet($json));

        $manager->execute();
    }


    public function testExecuteInsertThenRollback()
    {
        $manager = $this->buildMock();

        $dataNew = array(
            'uuid'  => $this->uuid3,
        );
        $condition = "WHERE uuid = '{$this->uuid3}'";


        // Insert
        $manager->addRow(self::$tableUser, null, $dataNew)->execute();
        $this->assertEquals(
            1,
            self::$db->getRowCount(self::$tableUser, $condition)
        );


        // Export to json and import back
        $json = $manager->getRowSet()->toJson();
        $manager->setRowSet(new RowSet($json));


        // Rollback
        $manager->rollback();
        $this->assertEquals(
            0,
            self::$db->getRowCount(self::$tableUser, $condition)
        );
    }


    public function testExecuteWithEmptyDataNew()
    {
        $queryCount = self::$db->getQueryCount();

        $manager = $this->buildMock();
        $manager->execute();

        $this->assertEquals($queryCount, self::$db->getQueryCount());
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage already rollbacked
     */
    public function testRollbackAgain()
    {
        $manager = $this->buildMock();

        $json = '{
            "executeStatus": -1,
            "rows": [{
                "table": "' . self::$tableUser . '",
                "primaryKey": "uuid",
                "old": null,
                "new": {
                    "uuid": "' . $this->uuid1 . '"
                }
            }]
        }';
        $manager->setRowSet(new RowSet($json));

        $manager->rollback();
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Db execute fail
     */
    public function testRollbackWithDbFail()
    {
        $manager = $this->buildMockWithFakeDb();

        $dataNew = array(
            'uuid'  => $this->uuid1,
        );

        self::$getErrorCode = -1;
        self::$getErrorMessage = 'Db execute fail';

        $json = '{
            "rowCount": 0,
            "executeStatus": 1,
            "rows": [{
                "table": "' . self::$tableUser . '",
                "primaryKey": "uuid",
                "old": null,
                "new": {
                    "uuid": "' . $this->uuid1 . '"
                }
            }]
        }';
        $manager->setRowSet($json);

        $manager->rollback();
    }


    public function testRollbackWithoutDiffData()
    {
        $queryCount = self::$db->getQueryCount();

        $manager = $this->buildMock();
        $manager->rollback();

        $this->assertEquals($queryCount, self::$db->getQueryCount());
    }
}
