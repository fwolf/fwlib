<?php
namespace Fwlib\Test;

use Fwlib\Bridge\Adodb;
use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Config\ConfigGlobal;
use Fwlib\Test\ServiceContainerTest;

/**
 * Parent class for db relate tests
 *
 * @codeCoverageIgnore
 *
 * @package     Fwlib\Base
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-09-11
 */
abstract class AbstractDbRelateTest extends PHPunitTestCase
{
    /**
     * Db connection, default
     *
     * @var Fwlib\Bridge\Adodb
     */
    protected static $db = null;

    /**
     * Db connection, mysql
     *
     * @var Fwlib\Bridge\Adodb
     */
    protected static $dbMysql = null;

    /**
     * Db connection, Sybase
     *
     * @var Fwlib\Bridge\Adodb
     */
    protected static $dbSyb = null;

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

        $dbName = array();

        $profileAr = explode(',', $profile);
        foreach ($profileAr as $type) {
            $type = trim($type);
            switch ($type) {
                case 'default':
                    $dbName[] = 'db';
                    break;
                case 'sybase':
                    $dbName[] = 'dbSyb';
                    break;
                default:
                    $dbName[] = 'db' . ucfirst($type);
            }
        }

        // Get db connection from ServiceContainer
        $sc = ServiceContainerTest::getInstance();
        foreach ($dbName as $name) {
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
     * @param   Fwlib\Bridge\Adodb  $db
     */
    protected static function createTable($db)
    {
        // Try drop table in case last test didn't success
        self::dropTable($db);

        // Create test table
        $db->execute(
            'CREATE TABLE ' . self::$tableGroup . '(
                uuid        CHAR(36)        NOT NULL,
                title       CHAR(255)       NULL,
                PRIMARY KEY (uuid)
            );
            '
        );

        if (0 != $db->errorNo()) {
            self::markTestSkipped(
                'Create test table group error: ' .
                $db->errorMsg()
            );
        }

        $db->execute(
            'CREATE TABLE ' . self::$tableUser . '(
                uuid        CHAR(36)        NOT NULL,
                title       VARCHAR(255)    NULL,
                age         INTEGER         NOT NULL DEFAULT 0,
                credit      DECIMAL(6, 2)   NULL,
                joindate    DATETIME        NULL,
                PRIMARY KEY (uuid)
            );
            '
        );

        if (0 != $db->errorNo()) {
            self::markTestSkipped(
                'Create test table user error: ' .
                $db->errorMsg()
            );
        }

        $db->execute(
            'CREATE TABLE ' . self::$tableUserGroup . '(
                uuid        CHAR(36)        NOT NULL,
                uuidUser    CHAR(36)        NOT NULL,
                uuidGroup   CHAR(36)        NOT NULL,
                PRIMARY KEY (uuid)
            );
            '
        );

        if (0 != $db->errorNo()) {
            self::markTestSkipped(
                'Create test table user_group error: ' .
                $db->errorMsg()
            );
        }
    }


    /**
     * @param   Fwlib\Bridge\Adodb  $db
     */
    protected static function dropTable($db)
    {
        $db->execute(
            'DROP TABLE ' . self::$tableUserGroup
        );

        $db->execute(
            'DROP TABLE ' . self::$tableGroup
        );

        $db->execute(
            'DROP TABLE ' . self::$tableUser
        );
    }


    public static function setUpBeforeClass()
    {
        self::connectDb(static::$dbUsing);

        // Create test table
        if (!is_null(self::$dbMysql) && self::$dbMysql->isConnected()) {
            self::createTable(self::$dbMysql);
        }
        if (!is_null(self::$dbSyb) && self::$dbSyb->isConnected()) {
            self::createTable(self::$dbSyb);
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
        if (!is_null(self::$dbSyb) && self::$dbSyb->isConnected()) {
            self::dropTable(self::$dbSyb);
        }

        if (!is_null(self::$db) && self::$db->isConnected() &&
            self::$db->isTableExist(self::$tableUser)) {
            self::dropTable(self::$db);
        }
    }
}
