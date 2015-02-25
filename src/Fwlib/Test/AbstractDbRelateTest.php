<?php
namespace Fwlib\Test;

use Fwlib\Bridge\Adodb;
use Fwlib\Bridge\PHPUnitTestCase;

/**
 * Parent class for db relate tests
 *
 * @codeCoverageIgnore
 *
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
abstract class AbstractDbRelateTest extends PHPunitTestCase
{
    /**
     * Db connection, default
     *
     * @var Adodb
     */
    protected static $db = null;

    /**
     * Db connection, mysql
     *
     * @var Adodb
     */
    protected static $dbMysql = null;

    /**
     * Db connection, Sybase
     *
     * @var Adodb
     */
    protected static $dbSybase = null;

    /**
     * Using db profile
     *
     * Available value: default, mysql, sybase
     *
     * Multiple value can join with comma.
     *
     * Extend and change this value in child class to select which db to use.
     *
     * @var string
     */
    protected static $dbUsing = '';

    /**
     * Test table: group
     *
     * @var string
     */
    protected static $tableGroup = 'test_group';

    /**
     * Test table: user
     *
     * @var string
     */
    protected static $tableUser = 'test_user';

    /**
     * Test table: user_group
     *
     * @var string
     */
    protected static $tableUserGroup = 'test_user_group';


    /**
     * Connect to db and assign to static property $dbXxx
     *
     * @see $dbUsing
     * @param   string   $profile    Using db profile, multi splitted by ','
     */
    protected static function connectDb($profile)
    {
        if (empty($profile)) {
            return;
        }

        $dbName = [];

        $profileAr = explode(',', $profile);
        foreach ($profileAr as $type) {
            $type = trim($type);

            if ('default' == $type) {
                $dbName[] = 'db';
            } else {
                $dbName[] = 'db' . ucfirst($type);
            }
        }

        // Get db connection from ServiceContainer
        $sc = ServiceContainerTest::getInstance();
        foreach ($dbName as $name) {
            /** @var Adodb $db */
            $db = &self::${$name};

            if (is_null($db)) {
                $db = $sc->get($name);

                if (is_null($db) || !$db->isConnected()) {
                    self::markTestSkipped("Db $name can't connect.");
                }
            }
        }
    }


    /**
     * @param   Adodb   $db
     */
    protected static function createTable($db)
    {
        // Try drop table in case last test didn't success
        self::dropTable($db);

        $groupTable = self::$tableGroup;
        $userTable = self::$tableUser;
        $userGroupTable = self::$tableUserGroup;

        // Create test table
        $db->execute(
            "CREATE TABLE {$groupTable}(
                uuid        CHAR(36)        NOT NULL,
                title       CHAR(255)       NULL,
                PRIMARY KEY (uuid)
            );
            "
        );

        if (0 != $db->getErrorCode()) {
            self::markTestSkipped(
                'Create test table group error: ' .
                $db->getErrorMessage()
            );
        }

        $db->execute(
            "CREATE TABLE {$userTable}(
                uuid        CHAR(36)        NOT NULL,
                title       VARCHAR(255)    NULL,
                age         INTEGER         NOT NULL DEFAULT 0,
                credit      DECIMAL(10, 2)  NULL,
                joindate    DATETIME        NULL,
                PRIMARY KEY (uuid)
            );
            "
        );

        if (0 != $db->getErrorCode()) {
            self::markTestSkipped(
                'Create test table user error: ' .
                $db->getErrorMessage()
            );
        }

        $db->execute(
            "CREATE TABLE {$userGroupTable}(
                uuid        CHAR(36)        NOT NULL,
                uuid_user   CHAR(36)        NOT NULL,
                uuid_group  CHAR(36)        NOT NULL,
                PRIMARY KEY (uuid)
            );
            "
        );

        if (0 != $db->getErrorCode()) {
            self::markTestSkipped(
                'Create test table user_group error: ' .
                $db->getErrorMessage()
            );
        }
    }


    /**
     * @param   Adodb  $db
     */
    protected static function dropTable($db)
    {
        $groupTable = self::$tableGroup;
        $userTable = self::$tableUser;
        $userGroupTable = self::$tableUserGroup;

        if ($db->isTableExist(self::$tableUserGroup)) {
            $db->execute(
                "DROP TABLE {$userGroupTable}"
            );
        }

        if ($db->isTableExist(self::$tableGroup)) {
            $db->execute(
                "DROP TABLE {$groupTable}"
            );
        }

        if ($db->isTableExist(self::$tableUser)) {
            $db->execute(
                "DROP TABLE {$userTable}"
            );
        }
    }


    public static function setUpBeforeClass()
    {
        self::connectDb(static::$dbUsing);

        // Create test table
        if (!is_null(self::$dbMysql) && self::$dbMysql->isConnected()) {
            self::createTable(self::$dbMysql);
        }
        if (!is_null(self::$dbSybase) && self::$dbSybase->isConnected()) {
            self::createTable(self::$dbSybase);
        }

        if (!is_null(self::$db) && self::$db->isConnected() &&
            !self::$db->isTableExist(self::$tableUser)) {
            self::createTable(self::$db);
        }
    }


    public static function tearDownAfterClass()
    {
        if (!is_null(self::$dbMysql) && self::$dbMysql->isConnected()) {
            self::dropTable(self::$dbMysql);
        }
        if (!is_null(self::$dbSybase) && self::$dbSybase->isConnected()) {
            self::dropTable(self::$dbSybase);
        }

        if (!is_null(self::$db) && self::$db->isConnected() &&
            self::$db->isTableExist(self::$tableUser)) {
            self::dropTable(self::$db);
        }
    }
}
