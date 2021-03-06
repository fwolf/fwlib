<?php
namespace FwlibTest\Db;

use Fwlib\Db\SyncDbSchema;
use Fwlib\Test\AbstractDbRelateTest;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class SyncDbSchemaTest extends AbstractDbRelateTest
{
    protected static $dbUsing = 'default';
    private static $logTable = 'log_sync_db_schema';

    /** @var SyncDbSchema */
    private static $sds = null;


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        // Delete log table if exists
        if (self::$db->isTableExist(self::$logTable)) {
            self::$db->execute('DROP TABLE ' . self::$logTable);
        }
    }


    public function testConstruct()
    {
        $this->assertFalse(self::$db->isTableExist(self::$logTable));

        $this->expectOutputRegex(
            '/Log table \w+ does not exists, create it, done\./'
        );

        self::$sds = (new SyncDbSchema)
            ->setLogTable(self::$logTable)
            ->setDb($this->getServiceContainer()->getDb());

        $this->assertEquals(self::$logTable, self::$sds->getLogTable());
        $this->assertTrue(self::$db->isTableExist(self::$logTable));

        self::$sds->charsetPhp = 'UTF-8';
    }


    public function testConstruct2()
    {
        $this->expectOutputRegex('/Log table \w+ already exists\./');
        $sds = (new SyncDbSchema)
            ->setLogTable(self::$logTable)
            ->setDb($this->getServiceContainer()->getDb());
        unset($sds);
    }


    /**
     * 1
     */
    public function testExecuteFirstSql()
    {
        $arColumn = self::$db->getMetaColumn(self::$tableUser);
        $this->assertFalse(isset($arColumn['temp1']));

        $this->expectOutputRegex('/Total \d+ SQL executed successful./');
        self::$sds->setSql(
            42,
            'ALTER TABLE ' . self::$tableUser . '
                ADD COLUMN temp1 INT NOT NULL DEFAULT 0'
        );
        self::$sds->execute();

        $arColumn = self::$db->getMetaColumn(self::$tableUser, true);
        $this->assertTrue(isset($arColumn['temp1']));
    }


    /**
     * 2 No more SQL
     */
    public function testExecuteNoUnDoneSql()
    {
        $this->expectOutputRegex('/No un-done SQL to do./');
        self::$sds->execute();
    }


    /**
     * 3 Add an error SQL
     */
    public function testExecuteErrorSql()
    {
        $this->expectOutputRegex('/Execute abort./');
        self::$sds->setSql(
            43,
            'ALTER TABLE ' . self::$tableUser . '
                ADD COLUMN temp1 INT NOT NULL DEFAULT 0'
        );
        self::$sds->execute();

        $this->assertEquals(43, self::$sds->lastId);
        $this->assertEquals(42, self::$sds->lastIdDone);
    }


    /**
     * 4 Add SQL with smaller id, will not execute
     */
    public function testExecuteSmallId()
    {
        $this->expectOutputRegex('/No un-done SQL to do./');
        self::$sds->setSql(
            22,
            'ALTER TABLE ' . self::$tableUser . '
                ADD COLUMN temp2 INT NOT NULL DEFAULT 0'
        );
        self::$sds->execute();

        // Error SQL 43 is cleared by execute()
        $this->assertEquals(42, self::$sds->getLastId());
        $this->assertEquals(42, self::$sds->getLastIdDone());
    }
}
