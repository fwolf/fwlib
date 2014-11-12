<?php
namespace Fwlib\Db\Test;

use Fwlib\Db\DbDiff;
use Fwlib\Test\AbstractDbRelateTest;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2012-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2012-12-10
 */
class DbDiffTest extends AbstractDbRelateTest
{
    protected static $dbUsing = 'default';
    protected $utilContainer;
    protected $uuidUtil;

    protected $uuid1;
    protected $uuid2;
    protected $uuid3;

    public static $getErrorCode;
    public static $getErrorMessage;

    public function __construct()
    {
        $this->utilContainer = UtilContainer::getInstance();
        $this->uuidUtil = $this->utilContainer->get('UuidBase36');

        $this->uuid1 = $this->generateUuid();
        $this->uuid2 = $this->generateUuid();
        $this->uuid3 = $this->generateUuid();
    }


    protected function buildMock()
    {
        $dbDiff = new DbDiff(self::$db);
        $dbDiff->setUtilContainer($this->utilContainer);

        return $dbDiff;
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
                return DbDiffTest::$getErrorCode;
            }));

        $db->expects($this->any())
            ->method('getErrorMessage')
            ->will($this->returnCallback(function () {
                return DbDiffTest::$getErrorMessage;
            }));

        $dbDiff = new DbDiff($db);
        $dbDiff->setUtilContainer($this->utilContainer);

        return $dbDiff;
    }


    protected function generateUuid()
    {
        return $this->uuidUtil->generate();
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage can't commit again
     */
    public function testCommitAgain()
    {
        $dbDiff = $this->buildMock();

        $json = '{
            "rowCount": 0,
            "executeStatus": 100,
            "diff": {
                "' . self::$tableUser .  '": [
                    {
                        "mode": "Whatever",
                        "pk": [],
                        "column": []
                    }
                ]
            }
        }';

        $dbDiff->import($json);
        $dbDiff->commit();
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Db execute fail
     */
    public function testCommitWithDbFail()
    {
        $dbDiff = $this->buildMockWithFakeDb();

        $dataNew = array(
            self::$tableUser => array(
                'uuid'  => $this->uuid1,
            ),
        );

        self::$getErrorCode = -1;
        self::$getErrorMessage = 'Db execute fail';

        $json = '{
            "rowCount": 0,
            "executeStatus": 0,
            "diff": {
                "' . self::$tableUser .  '": [
                    {
                        "mode": "INSERT",
                        "pk": {
                            "uuid": {
                                "new": "' . $this->uuid1 . '",
                                "old": null
                            }
                        },
                        "column": []
                    }
                ]
            }
        }';
        $dbDiff->import($json);

        $dbDiff->commit();
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid mode
     */
    public function testCommitWithInvalidMode()
    {
        $dbDiff = $this->buildMock();

        $json = '{
            "rowCount": 0,
            "executeStatus": 0,
            "diff": {
                "' . self::$tableUser .  '": [
                    {
                        "mode": "InvalidMode",
                        "pk": [],
                        "column": []
                    }
                ]
            }
        }';

        $dbDiff->import($json);
        $dbDiff->commit();
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage No diff data
     */
    public function testCommitWithoutDiffData()
    {
        $dbDiff = $this->buildMock();
        $dbDiff->commit();
    }


    public function testCompareWithDeleteMode()
    {
        $dbDiff = $this->buildMock();
        $dataNew = array(
            self::$tableUser => array(
                'uuid'  => null,
            ),
        );
        $dataOld = array(
            self::$tableUser => array(
                'uuid'  => $this->uuid1,
                'title' => 'User Title The Third',
                'age'   => 4200,
                'credit'    => '42',
                'joindate'  => '2012-01-02',
            ),
        );

        $dbDiff->compare($dataNew, $dataOld);
        $diff = $dbDiff->getDiff();
        $diff = $diff[self::$tableUser][0];

        $this->assertEquals('DELETE', $diff['mode']);
        $this->assertEquals(4, count($diff['column']));
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage all null
     */
    public function testCompareWithNullPkInBothDataNewAndOld()
    {
        $dbDiff = $this->buildMock();
        $dataNew = array(
            self::$tableUser => array(
                'uuid'  => null,
            ),
        );

        $dbDiff->compare($dataNew);
    }


    public function testCompareWithUpdateModeWithSameDataNewAndOld()
    {
        $dbDiff = $this->buildMock();
        $dataNew = array(
            self::$tableUser => array(
                'uuid'  => $this->uuid1,
                'title' => 'User Title The Third',
                'age'   => 4200,
                'credit'    => '42',
                'joindate'  => '2012-01-02',
            ),
        );

        $dbDiff->compare($dataNew, $dataNew);

        $this->assertEmpty($dbDiff->getDiff());
    }


    public function testExecute()
    {
        $dbDiff = $this->buildMock();

        // Normal insert
        $dataNew1 = array(
            self::$tableUser => array(
                'uuid'  => $this->uuid1,
                'title' => 'User Title',
                'age'   => 42,
                'credit'    => '0.42',
                'joindate'  => '2014-01-02',
            ),
        );

        $dbDiff->execute($dataNew1);
        $this->assertEquals(1, $dbDiff->getRowCount());

        $diff = $dbDiff->getDiff();
        $this->assertEquals('INSERT', $diff[self::$tableUser][0]['mode']);
        $this->assertEquals(1, count($diff[self::$tableUser][0]['pk']));
        $this->assertEquals(4, count($diff[self::$tableUser][0]['column']));

        $this->assertTrue($dbDiff->isCommitted());
        $this->assertTrue($dbDiff->isExecuted());


        // Insert with PK column only
        $dataNew2 = array(
            self::$tableUser => array(
                'uuid'  => $this->uuid2,
            ),
        );

        $dbDiff->execute($dataNew2);
        $this->assertEquals(1, $dbDiff->getRowCount());
        $diff = $dbDiff->getDiff();
        $this->assertEquals(0, count($diff[self::$tableUser][0]['column']));

        $this->assertEquals(2, self::$db->getRowCount(self::$tableUser));


        // Update row with $uuid1, and delete row with $uuid2
        $dataNewChanged = array(
            self::$tableUser => array(
                // Modify from $dataNew1
                array(
                    'uuid'  => $this->uuid1,
                    'title' => 'User Title Changed',
                    'age'   => 420,
                    'credit'    => '4.2',
                    'joindate'  => '2013-01-02',
                ),
                array(
                    'uuid'  => null,
                )
            ),
        );
        $dataOld = array(
            self::$tableUser => array(
                $dataNew1[self::$tableUser],
                $dataNew2[self::$tableUser],
            ),
        );
        $dbDiff->execute($dataNewChanged, $dataOld);

        $this->assertEquals(2, $dbDiff->getRowCount());
        $this->assertEquals(
            420,
            self::$db->getByKey(self::$tableUser, $this->uuid1, 'age', 'uuid')
        );
        $this->assertEquals(1, self::$db->getRowCount(self::$tableUser));


        // Rollback last update and delete
        $dbDiff->rollback();

        $this->assertTrue($dbDiff->isRollbacked());
        $this->assertEquals(
            42,
            self::$db->getByKey(self::$tableUser, $this->uuid1, 'age', 'uuid')
        );
        $this->assertEquals(2, self::$db->getRowCount(self::$tableUser));


        // Then commit again
        $dbDiff->commit();

        $this->assertTrue($dbDiff->isCommitted());
        $this->assertEquals(
            420,
            self::$db->getByKey(self::$tableUser, $this->uuid1, 'age', 'uuid')
        );
        $this->assertEquals(1, self::$db->getRowCount(self::$tableUser));
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage can't execute again
     */
    public function testExecuteAgain()
    {
        $dbDiff = $this->buildMock();

        $json = '{
            "rowCount": 0,
            "executeStatus": 100,
            "diff": {
                "' . self::$tableUser .  '": [
                    {
                        "mode": "Whatever",
                        "pk": [],
                        "column": []
                    }
                ]
            }
        }';

        $dbDiff->import($json);
        $dbDiff->execute();
    }


    public function testExecuteInsertThenRollback()
    {
        $dbDiff = $this->buildMock();

        $dataNew = array(
            self::$tableUser => array(
                'uuid'  => $this->uuid3,
            ),
        );

        $condition = "WHERE uuid = '{$this->uuid3}'";


        // Insert
        $dbDiff->execute($dataNew);
        $this->assertEquals(
            1,
            self::$db->getRowCount(self::$tableUser, $condition)
        );


        // Export to json and import back
        $dbDiff->import($dbDiff->export());


        // Rollback
        $dbDiff->rollback();
        $this->assertEquals(
            0,
            self::$db->getRowCount(self::$tableUser, $condition)
        );
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage empty
     */
    public function testExecuteWithEmptyDataNew()
    {
        $dbDiff = $this->buildMock();
        $dbDiff->execute(array(), null);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage PK not all assigned
     */
    public function testExecuteWithNotEnoughPkInDataNew()
    {
        $dbDiff = $this->buildMock();

        // No PK column uuid
        $dataNew = array(
            self::$tableUser => array(
                'title' => 'User Title',
            ),
        );

        $dbDiff->execute($dataNew);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage must have PK
     */
    public function testExecuteWithTableHaveNoPk()
    {
        $dbDiff = $this->buildMock();

        // No PK column uuid
        $dataNew = array(
            'table_not_exist' => array(
                'title' => 'User Title',
            ),
        );

        $dbDiff->execute($dataNew);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid json
     */
    public function testImportInvalidJson()
    {
        $dbDiff = $this->buildMock();

        $dbDiff->import('{}');
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage can't rollback again
     */
    public function testRollbackAgain()
    {
        $dbDiff = $this->buildMock();

        $json = '{
            "rowCount": 0,
            "executeStatus": -100,
            "diff": {
                "' . self::$tableUser .  '": [
                    {
                        "mode": "Whatever",
                        "pk": [],
                        "column": []
                    }
                ]
            }
        }';

        $dbDiff->import($json);
        $dbDiff->rollback();
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Db execute fail
     */
    public function testRollbackWithDbFail()
    {
        $dbDiff = $this->buildMockWithFakeDb();

        $dataNew = array(
            self::$tableUser => array(
                'uuid'  => $this->uuid1,
            ),
        );

        self::$getErrorCode = -1;
        self::$getErrorMessage = 'Db execute fail';

        $json = '{
            "rowCount": 0,
            "executeStatus": 100,
            "diff": {
                "' . self::$tableUser .  '": [
                    {
                        "mode": "INSERT",
                        "pk": {
                            "uuid": {
                                "new": "' . $this->uuid1 . '",
                                "old": null
                            }
                        },
                        "column": []
                    }
                ]
            }
        }';
        $dbDiff->import($json);

        $dbDiff->rollback();
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid mode
     */
    public function testRollbackWithInvalidMode()
    {
        $dbDiff = $this->buildMock();

        $json = '{
            "rowCount": 0,
            "executeStatus": 0,
            "diff": {
                "' . self::$tableUser .  '": [
                    {
                        "mode": "InvalidMode",
                        "pk": [],
                        "column": []
                    }
                ]
            }
        }';

        $dbDiff->import($json);
        $dbDiff->rollback();
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage No diff data
     */
    public function testRollbackWithoutDiffData()
    {
        $dbDiff = $this->buildMock();
        $dbDiff->rollback();
    }
}
