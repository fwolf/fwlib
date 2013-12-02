<?php
namespace Fwlib\Db\Test;

use Fwlib\Db\SyncDbData;
use Fwlib\Test\AbstractDbRelateTest;
use Fwlib\Util\StringUtil;
use Fwlib\Util\UuidBase36;

/**
 * Test for Fwlib\Db\SyncDbData
 *
 * @package     Fwlib\Db\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-02
 */
class SyncDbDataMysqlTest extends AbstractDbRelateTest
{
    public static $flock = true;
    public static $fopen = null;
    public static $unlink = null;

    public static $dbUsing = 'mysql';
    public static $tableUserDest = 'test_user_dest';

    /**
     * Rows of initial test data
     */
    private $totalRows = 16;


    private static function removeDestTable()
    {
        if (self::$dbMysql->isTableExist(self::$tableUserDest)) {
            self::$dbMysql->Execute('DROP TABLE ' . self::$tableUserDest);
        }
    }


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::removeDestTable();
    }


    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        // Delete record table
        self::$flock = true;
        $sdd = new SyncDbData();
        self::$dbMysql->Execute("DROP TABLE {$sdd->tableRecord}");

        self::removeDestTable();
    }


    public function testLock()
    {
        // Normal lock file release
        $sdd = new SyncDbData();
        $y = self::$fopen;
        unset($sdd);
        $this->assertEquals($y, self::$unlink);
    }


    public function testSetDb()
    {
        $sdd = new SyncDbData();
        $sdd->setDb(self::$dbMysql, self::$dbMysql->profile);

        $this->assertTrue(self::$dbMysql->isTableExist($sdd->tableRecord));

        // Create again and check record table exists
        $sdd = new syncDbData();
        $sdd->setDb(self::$dbMysql, self::$dbMysql);
        $this->assertEquals(
            "Record table {$sdd->tableRecord} already exists.",
            array_pop($sdd->logMessage)
        );
    }


    /**
     * Need before testSyncOneway(), which create timestamp column
     *
     * @expectedException Exception
     * @expectedExceptionMessage Table test_user in source db hasn't timestamp column.
     */
    public function testSyncOnewayWithSrceHasNoTimestampColumn()
    {
        $sdd = new SyncDbData;
        $sdd->setDb(self::$dbMysql, self::$dbMysql);
        $config = array(
            self::$tableUser => self::$tableUserDest,
        );
        $sdd->syncOneway($config);
    }


    public function testSyncOneway()
    {
        $tableUser = self::$tableUser;
        $tableUserDest = self::$tableUserDest;
        $tableNotExist = 'test_not_exist';


        // Add timestamp column in srce db
        self::$dbMysql->Execute(
            "ALTER TABLE {$tableUser}
                ADD COLUMN ts TIMESTAMP NOT NULL
                    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ;"
        );
        // Refresh cached meta data
        self::$dbMysql->getMetaColumn($tableUser, true);


        // Create another test user table as sync dest
        self::$dbMysql->Execute(
            "CREATE TABLE {$tableUserDest} LIKE {$tableUser};"
        );


        // Prepare dummy date in srce table
        $data = array();
        for ($i = 0; $i < $this->totalRows; $i ++) {
            $data[] = array(
                'uuid'  => UuidBase36::gen(),
                'title' => "Title - $i",
                'age'   => $i + 20,
                'credit'    => $i * 100,
            );
        }
        self::$dbMysql->write($tableUser, $data, 'I');
        $rowsSrce = self::$dbMysql->getRowCount($tableUser);


        // Prepare SyncDbData instance
        // The table $tableNotExist if test dummy, we will use mock to create
        // convertDataXxx() method for it, and return empty convert result to
        // skip data write to it.
        $config = array(
            $tableUser => array($tableUserDest, $tableNotExist),
        );

        // Mock instance with 2 additional convert method
        $convertForNotExist = 'convertData' .
            StringUtil::toStudlyCaps($tableUser) .
            'To' . StringUtil::toStudlyCaps($tableNotExist);
        $convertForUserDest = 'convertData' .
            StringUtil::toStudlyCaps($tableUser) .
            'To' . StringUtil::toStudlyCaps($tableUserDest);
        $sdd = $this->getMock(
            'Fwlib\Db\SyncDbData',
            array($convertForNotExist)
        );
        $sdd->expects($this->any())
            ->method($convertForNotExist)
            ->will($this->returnValue(null));

        $sdd->setDb(self::$dbMysql, self::$dbMysql);
        $sdd->batchSize = 10;


        // First sync round, limit by batchSize

        $this->assertEquals($sdd->batchSize, $sdd->syncOneway($config));
        $this->assertEquals(
            $sdd->batchSize,
            self::$dbMysql->getRowCount($tableUserDest)
        );

        // Run sync again will sync nothing, because batchSize limit
        $this->assertEquals(0, $sdd->syncOneway($config));


        // Second sync round, full sync, not reach batchSize limit

        $sdd = $this->getMock(
            'Fwlib\Db\SyncDbData',
            array($convertForNotExist, $convertForUserDest)
        );
        $sdd->expects($this->any())
            ->method($convertForNotExist)
            ->will($this->returnValue(null));
        // Change age column through convert data method
        $sdd->expects($this->any())
            ->method($convertForUserDest)
            ->will($this->returnCallback(function ($arSrce) {
                $arSrce['age'] = 42;
                return $arSrce;
            }));

        $sdd->setDb(self::$dbMysql, self::$dbMysql);
        // Mysql timestamp is not unique, so we need raise batchSize to sync
        // all rows. It need not clear record table.
        $sdd->batchSize = 200;
        //self::$dbMysql->Execute('TRUNCATE TABLE ' . self::$tableUserDest);

        $this->assertEquals($this->totalRows, $sdd->syncOneway($config));
        $this->assertEquals(
            $this->totalRows,
            self::$dbMysql->getRowCount($tableUserDest)
        );

        // In dest db, column age in all rows are same value return by
        // callback function we defined, assert it now.
        $rs = self::$dbMysql->execute(
            "SELECT DISTINCT age from $tableUserDest"
        );
        $this->assertEquals(1, $rs->RowCount());
        $this->assertEquals(42, $rs->fields['age']);
    }


    /**
     * Need after testSyncOneway()
     */
    public function testSyncDelete()
    {
        $tableUser = self::$tableUser;
        $tableUserDest = self::$tableUserDest;


        // Prepare SyncDbData instance
        // The 2nd $tableUserDest is test dummy for empty table or table need
        // not to sync. All actual sync is done on 1st $tableUserDest.
        $config = array(
            $tableUser => array($tableUserDest, $tableUserDest),
        );

        // Mock instance with 2 additional convert method
        $compareForUserDest = 'compareData' .
            StringUtil::toStudlyCaps($tableUser) .
            'To' . StringUtil::toStudlyCaps($tableUserDest);
        $sdd = $this->getMock(
            'Fwlib\Db\SyncDbData',
            array($compareForUserDest)
        );
        $db = self::$dbMysql;
        $sdd->expects($this->any())
            ->method($compareForUserDest)
            ->will($this->returnCallback(
                function () use ($db, $tableUser, $tableUserDest) {
                    $countSrce = $db->getRowCount($tableUser);
                    $countDest = $db->getRowCount($tableUserDest);

                    if (1 >= ($countDest - $countSrce)) {
                        return null;

                    } else {
                        $rs = $db->SelectLimit(
                            "SELECT uuid FROM $tableUserDest",
                            $countDest - $countSrce
                        );
                        return $rs->GetArray();
                    }
                }
            ));

        $sdd->setDb(self::$dbMysql, self::$dbMysql);


        // If previous reach batchSize, will skip sync
        $sdd->batchSize = 0;
        $this->assertEquals(0, $sdd->syncDelete($config));


        $sdd->batchSize = 10;

        // First sync round, db rows count diff but still no rows need to be delete
        self::$dbMysql->Execute("DELETE FROM $tableUser LIMIT 1");
        $this->assertEquals(0, $sdd->syncDelete($config));


        // Second sync round, only 2 row need to delete
        self::$dbMysql->Execute("DELETE FROM $tableUser LIMIT 1");
        $this->assertEquals(2, $sdd->syncDelete($config));


        // Third sync round, all rest need sync but limit by batchSize
        // Remove 2nd $tableUserDest in $config, because do sync on it will
        // exceed batchSize.
        $config = array(
            $tableUser => array($tableUserDest),
            'not_exist' => 'not_exist',
        );

        self::$dbMysql->Execute("TRUNCATE TABLE $tableUser");
        $this->assertEquals($sdd->batchSize - 2, $sdd->syncDelete($config));

        $this->assertEquals(
            $this->totalRows - $sdd->batchSize,
            self::$dbMysql->getRowCount($tableUserDest)
        );

        // Sync for not_exist is skipped
        $this->assertEquals(
            "Reach batchSize limit {$sdd->batchSize}.",
            $sdd->logMessage[count($sdd->logMessage) - 2]
        );
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Compare method needed:
     */
    public function testSyncDeleteWithoutCompareMethod()
    {
        $tableUser = self::$tableUser;
        $tableUserDest = self::$tableUserDest;

        $config = array(
            $tableUser => array($tableUserDest),
        );

        $sdd = new SyncDbData;
        $sdd->setDb(self::$dbMysql, self::$dbMysql);

        $sdd->syncDelete($config);
    }


    /**
     * Put last avoid influence other tests
     *
     * @expectedException Exception
     * @expectedExceptionMessage Aborted: Lockfile check failed.
     */
    public function testLockException()
    {
        // Duplicate instance will throw exception
        self::$flock = false;
        $sdd = new SyncDbData();
    }
}


// Overwrite build-in function for test

namespace Fwlib\Db;

function fclose($fileHandle)
{
}

function flock($fileHandle, $mode)
{
    return \Fwlib\Db\Test\SyncDbDataMysqlTest::$flock;
}

function fopen($path)
{
    \Fwlib\Db\Test\SyncDbDataMysqlTest::$fopen = $path;
    return $path;
}

function unlink($path)
{
    \Fwlib\Db\Test\SyncDbDataMysqlTest::$unlink = $path;
}
