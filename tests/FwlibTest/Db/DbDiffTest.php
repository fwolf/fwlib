<?php
namespace FwlibTest\Db;

use Fwlib\Bridge\Adodb;
use Fwlib\Db\DbDiff;
use Fwlib\Test\AbstractDbRelateTest;
use Fwlib\Util\UtilContainer;
use Fwlib\Util\UtilContainerAwareTrait;

/**
 * @copyright   Copyright 2012-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class DbDiffTest extends AbstractDbRelateTest
{
    use UtilContainerAwareTrait;


    protected static $dbUsing = 'default';

    protected static $uuid1;
    protected static $uuid2;
    protected static $uuid3;

    public static $getErrorCode;
    public static $getErrorMessage;


    protected function buildMock()
    {
        $dbDiff = new DbDiff(self::$db);

        return $dbDiff;
    }


    /**
     * @return DbDiff
     */
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
                return DbDiffTest::$getErrorCode;
            }));

        $db->expects($this->any())
            ->method('getErrorMessage')
            ->will($this->returnCallback(function () {
                return DbDiffTest::$getErrorMessage;
            }));

        $dbDiff = new DbDiff($db);

        return $dbDiff;
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
     * @expectedException \Exception
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
     * @expectedException \Exception
     * @expectedExceptionMessage Db execute fail
     */
    public function testCommitWithDbFail()
    {
        $dbDiff = $this->buildMockWithFakeDb();

        $dataNew = [
            self::$tableUser => [
                'uuid'  => self::$uuid1,
            ],
        ];

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
                                "new": "' . self::$uuid1 . '",
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
     * @expectedException \Exception
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
     * @expectedException \Exception
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
        $dataNew = [
            self::$tableUser => [
                'uuid'  => null,
            ],
        ];
        $dataOld = [
            self::$tableUser => [
                'uuid'  => self::$uuid1,
                'title' => 'User Title The Third',
                'age'   => 4200,
                'credit'    => '42',
                'joindate'  => '2012-01-02',
            ],
        ];

        $dbDiff->compare($dataNew, $dataOld);
        $diff = $dbDiff->getDiff();
        $diff = $diff[self::$tableUser][0];

        $this->assertEquals('DELETE', $diff['mode']);
        $this->assertEquals(4, count($diff['column']));
    }


    /**
     * @expectedException \Exception
     * @expectedExceptionMessage all null
     */
    public function testCompareWithNullPkInBothDataNewAndOld()
    {
        $dbDiff = $this->buildMock();
        $dataNew = [
            self::$tableUser => [
                'uuid'  => null,
            ],
        ];

        $dbDiff->compare($dataNew);
    }


    public function testCompareWithUpdateModeWithSameDataNewAndOld()
    {
        $dbDiff = $this->buildMock();
        $dataNew = [
            self::$tableUser => [
                'uuid'  => self::$uuid1,
                'title' => 'User Title The Third',
                'age'   => 4200,
                'credit'    => '42',
                'joindate'  => '2012-01-02',
            ],
        ];

        $dbDiff->compare($dataNew, $dataNew);

        $this->assertEmpty($dbDiff->getDiff());
    }


    public function testExecute()
    {
        $dbDiff = $this->buildMock();

        // Normal insert
        $dataNew1 = [
            self::$tableUser => [
                'uuid'  => self::$uuid1,
                'title' => 'User Title',
                'age'   => 42,
                'credit'    => '0.42',
                'joindate'  => '2014-01-02',
            ],
        ];

        $dbDiff->execute($dataNew1);
        $this->assertEquals(1, $dbDiff->getRowCount());

        $diff = $dbDiff->getDiff();
        $this->assertEquals('INSERT', $diff[self::$tableUser][0]['mode']);
        $this->assertEquals(1, count($diff[self::$tableUser][0]['pk']));
        $this->assertEquals(4, count($diff[self::$tableUser][0]['column']));

        $this->assertTrue($dbDiff->isCommitted());
        $this->assertTrue($dbDiff->isExecuted());


        // Insert with PK column only
        $dataNew2 = [
            self::$tableUser => [
                'uuid'  => self::$uuid2,
            ],
        ];

        $dbDiff->execute($dataNew2);
        $this->assertEquals(1, $dbDiff->getRowCount());
        $diff = $dbDiff->getDiff();
        $this->assertEquals(0, count($diff[self::$tableUser][0]['column']));

        $this->assertEquals(2, self::$db->getRowCount(self::$tableUser));


        // Update row with $uuid1, and delete row with $uuid2
        $dataNewChanged = [
            self::$tableUser => [
                // Modify from $dataNew1
                [
                    'uuid'  => self::$uuid1,
                    'title' => 'User Title Changed',
                    'age'   => 420,
                    'credit'    => '4.2',
                    'joindate'  => '2013-01-02',
                ],
                [
                    'uuid'  => null,
                ]
            ],
        ];
        $dataOld = [
            self::$tableUser => [
                $dataNew1[self::$tableUser],
                $dataNew2[self::$tableUser],
            ],
        ];
        $dbDiff->execute($dataNewChanged, $dataOld);

        $this->assertEquals(2, $dbDiff->getRowCount());
        $this->assertEquals(
            420,
            self::$db->getByKey(self::$tableUser, self::$uuid1, 'age', 'uuid')
        );
        $this->assertEquals(1, self::$db->getRowCount(self::$tableUser));


        // Rollback last update and delete
        $dbDiff->rollback();

        $this->assertTrue($dbDiff->isRollbacked());
        $this->assertEquals(
            42,
            self::$db->getByKey(self::$tableUser, self::$uuid1, 'age', 'uuid')
        );
        $this->assertEquals(2, self::$db->getRowCount(self::$tableUser));


        // Then commit again
        $dbDiff->commit();

        $this->assertTrue($dbDiff->isCommitted());
        $this->assertEquals(
            420,
            self::$db->getByKey(self::$tableUser, self::$uuid1, 'age', 'uuid')
        );
        $this->assertEquals(1, self::$db->getRowCount(self::$tableUser));
    }


    /**
     * @expectedException \Exception
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

        $dataNew = [
            self::$tableUser => [
                'uuid'  => self::$uuid3,
            ],
        ];

        $condition = "WHERE uuid = '" . self::$uuid3 . "'";


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
     * @expectedException \Exception
     * @expectedExceptionMessage empty
     */
    public function testExecuteWithEmptyDataNew()
    {
        $dbDiff = $this->buildMock();
        $dbDiff->execute([], null);
    }


    /**
     * @expectedException \Exception
     * @expectedExceptionMessage PK not all assigned
     */
    public function testExecuteWithNotEnoughPkInDataNew()
    {
        $dbDiff = $this->buildMock();

        // No PK column uuid
        $dataNew = [
            self::$tableUser => [
                'title' => 'User Title',
            ],
        ];

        $dbDiff->execute($dataNew);
    }


    /**
     * @expectedException \Exception
     * @expectedExceptionMessage must have PK
     */
    public function testExecuteWithTableHaveNoPk()
    {
        $dbDiff = $this->buildMock();

        // No PK column uuid
        $dataNew = [
            'table_not_exist' => [
                'title' => 'User Title',
            ],
        ];

        $dbDiff->execute($dataNew);
    }


    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid json
     */
    public function testImportInvalidJson()
    {
        $dbDiff = $this->buildMock();

        $dbDiff->import('{}');
    }


    /**
     * @expectedException \Exception
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
     * @expectedException \Exception
     * @expectedExceptionMessage Db execute fail
     */
    public function testRollbackWithDbFail()
    {
        $dbDiff = $this->buildMockWithFakeDb();

        $dataNew = [
            self::$tableUser => [
                'uuid'  => self::$uuid1,
            ],
        ];

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
                                "new": "' . self::$uuid1 . '",
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
     * @expectedException \Exception
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
     * @expectedException \Exception
     * @expectedExceptionMessage No diff data
     */
    public function testRollbackWithoutDiffData()
    {
        $dbDiff = $this->buildMock();
        $dbDiff->rollback();
    }
}
