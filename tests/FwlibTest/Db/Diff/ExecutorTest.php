<?php
namespace FwlibTest\Db\Diff;

use Fwlib\Bridge\Adodb;
use Fwlib\Db\Diff\Executor;
use Fwlib\Db\Diff\Manager;
use Fwlib\Db\Diff\Row;
use Fwlib\Db\Diff\RowSet;
use Fwlib\Test\AbstractDbRelateTest;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2012-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ExecutorTest extends AbstractDbRelateTest
{
    protected static $dbUsing = 'default';

    protected static $uuid1;
    protected static $uuid2;
    protected static $uuid3;

    public static $getErrorCode;
    public static $getErrorMessage;


    protected function buildMock()
    {
        $executor = new Executor(self::$db);

        return $executor;
    }


    protected function buildMockManager()
    {
        $manager = new Manager();

        return $manager;
    }


    protected function buildMockWithFakeDb()
    {
        $db = $this->getMockBuilder(Adodb::class)
            ->disableOriginalConstructor()
            ->getMock(
                Adodb::class,
                [
                    'BeginTrans', 'CommitTrans', 'RollbackTrans',
                    'getErrorCode', 'getErrorMessage',
                    'execute'
                ]
            );

        $db->expects($this->any())
            ->method('getErrorCode')
            ->will($this->returnCallback(function () {
                return ExecutorTest::$getErrorCode;
            }));

        $db->expects($this->any())
            ->method('getErrorMessage')
            ->will($this->returnCallback(function () {
                return ExecutorTest::$getErrorMessage;
            }));

        $executor = $this->buildMock();
        $executor->setDb($db);

        return $executor;
    }


    protected function buildMockRowSetWithInvalidMode()
    {
        $row = new Row('table', 'uuid', null, ['uuid' => 'uuid value']);
        $this->reflectionSet($row, 'mode', 'invalid mode');

        $rowSet = new RowSet();
        $rowSet->addRow($row);

        return $rowSet;
    }


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $uuidGenerator = UtilContainer::getInstance()->getUuidBase36();

        self::$uuid1 = $uuidGenerator->generate();
        self::$uuid2 = $uuidGenerator->generate();
        self::$uuid3 = $uuidGenerator->generate();
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage already committed
     */
    public function testCommitAgain()
    {
        $executor = $this->buildMock();

        $json = '{
            "executeStatus": 1,
            "rows": [{
                "table": "' . self::$tableUser . '",
                "primaryKey": "uuid",
                "old": null,
                "new": {
                    "uuid": "' . self::$uuid1 . '"
                }
            }]
        }';
        $executor->commit(new RowSet($json));
    }


    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Db execute fail
     */
    public function testCommitWithDbFail()
    {
        $executor = $this->buildMockWithFakeDb();

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
                    "uuid": "' . self::$uuid1 . '"
                }
            }]
        }';
        $executor->commit(new RowSet($json));
    }


    public function testCommitWithEmptyRowSet()
    {
        $queryCount = self::$db->getQueryCount();

        $executor = $this->buildMock();
        $executor->commit(new RowSet);

        $this->assertEquals($queryCount, self::$db->getQueryCount());
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid mode
     */
    public function testCommitWithInvalidMode()
    {
        $executor = $this->buildMock();
        $executor->commit($this->buildMockRowSetWithInvalidMode());
    }


    public function testExecute()
    {
        $executor = $this->buildMock();
        $manager = $this->buildMockManager();

        // Normal insert
        $dataNew1 = [
            'uuid'  => self::$uuid1,
            'title' => 'User Title',
            'age'   => 42,
            'credit'    => '0.42',
            'joindate'  => '2014-01-02',
        ];
        $manager->addRow(self::$tableUser, null, $dataNew1);
        $executor->execute($manager->getRowSet());

        $rowSet = $manager->getRowSet();
        $rows = $rowSet->getRows();
        $this->assertEquals(1, count($rows));
        $this->assertEquals('INSERT', $rows[0]->getMode());
        $this->assertEquals('uuid', $rows[0]->getPrimaryKey());
        $this->assertEquals(0, count($rows[0]->getOld()));
        $this->assertEquals(5, count($rows[0]->getNew()));
        $this->assertTrue($rowSet->isCommitted());


        // Insert with PK column only
        $dataNew2 = [
            'uuid'  => self::$uuid2,
        ];
        $manager->renew()->addRow(self::$tableUser, null, $dataNew2);
        $executor->execute($manager->getRowSet());

        $rowSet = $manager->getRowSet();
        $this->assertEquals(1, $rowSet->getRowCount());
        $this->assertEquals(2, self::$db->getRowCount(self::$tableUser));


        // Update row with $uuid1, and delete row with $uuid2
        $dataNew1Changed = [
            'uuid'  => self::$uuid1,
            'title' => 'User Title Changed',
            'age'   => 420,
            'credit'    => '4.2',
            'joindate'  => '2013-01-02',
        ];
        $manager->renew()
            ->addRow(self::$tableUser, $dataNew1, $dataNew1Changed)
            ->addRow(self::$tableUser, $dataNew2, null);
        $executor->execute($manager->getRowSet());

        $rowSet = $manager->getRowSet();
        $this->assertEquals(2, $rowSet->getRowCount());
        $this->assertEquals(
            420,
            self::$db->getByKey(self::$tableUser, self::$uuid1, 'age', 'uuid')
        );
        $this->assertEquals(1, self::$db->getRowCount(self::$tableUser));


        // Rollback last update and delete
        $executor->rollback($rowSet);

        $this->assertTrue($rowSet->isRollbacked());
        $this->assertEquals(
            42,
            self::$db->getByKey(self::$tableUser, self::$uuid1, 'age', 'uuid')
        );
        $this->assertEquals(2, self::$db->getRowCount(self::$tableUser));


        // Then commit again
        $executor->commit($rowSet);

        $this->assertTrue($rowSet->isCommitted());
        $this->assertEquals(
            420,
            self::$db->getByKey(self::$tableUser, self::$uuid1, 'age', 'uuid')
        );
        $this->assertEquals(1, self::$db->getRowCount(self::$tableUser));
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage RowSet is already executed
     */
    public function testExecuteAgain()
    {
        $executor = $this->buildMock();

        $json = '{
            "executeStatus": 1,
            "rows": [{
                "table": "' . self::$tableUser . '",
                "primaryKey": "uuid",
                "old": null,
                "new": {
                    "uuid": "' . self::$uuid1 . '"
                }
            }]
        }';
        $executor->execute(new RowSet($json));
    }


    public function testExecuteInsertThenRollback()
    {
        $executor = $this->buildMock();
        $manager = $this->buildMockManager();

        $dataNew = [
            'uuid'  => self::$uuid3,
        ];
        $condition = "WHERE uuid = '" . self::$uuid3 . "'";


        // Insert
        $manager->addRow(self::$tableUser, null, $dataNew);
        $executor->execute($manager->getRowSet());
        $this->assertEquals(
            1,
            self::$db->getRowCount(self::$tableUser, $condition)
        );


        // Export to json and import back in rollback
        $json = $manager->getRowSet()->toJson();


        // Rollback
        $executor->rollback(new RowSet($json));
        $this->assertEquals(
            0,
            self::$db->getRowCount(self::$tableUser, $condition)
        );
    }


    public function testExecuteWithEmptyDataNew()
    {
        $queryCount = self::$db->getQueryCount();

        $executor = $this->buildMock();
        $executor->execute(new RowSet);

        $this->assertEquals($queryCount, self::$db->getQueryCount());
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage already rollbacked
     */
    public function testRollbackAgain()
    {
        $executor = $this->buildMock();

        $json = '{
            "executeStatus": -1,
            "rows": [{
                "table": "' . self::$tableUser . '",
                "primaryKey": "uuid",
                "old": null,
                "new": {
                    "uuid": "' . self::$uuid1 . '"
                }
            }]
        }';
        $executor->rollback(new RowSet($json));
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Db execute fail
     */
    public function testRollbackWithDbFail()
    {
        $executor = $this->buildMockWithFakeDb();

        $dataNew = [
            'uuid'  => self::$uuid1,
        ];

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
                    "uuid": "' . self::$uuid1 . '"
                }
            }]
        }';
        $executor->rollback(new RowSet($json));
    }


    public function testRollbackWithEmptyRowSet()
    {
        $queryCount = self::$db->getQueryCount();

        $executor = $this->buildMock();
        $executor->rollback(new RowSet);

        $this->assertEquals($queryCount, self::$db->getQueryCount());
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid mode
     */
    public function testRollbackWithInvalidMode()
    {
        $executor = $this->buildMock();
        $executor->rollback($this->buildMockRowSetWithInvalidMode());
    }
}
