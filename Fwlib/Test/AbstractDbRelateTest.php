<?php
namespace Fwlib\Test;

use Fwlib\Bridge\Adodb;
use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Config\ConfigGlobal;

/**
 * Parent class for db relate tests
 *
 * @codeCoverageIgnore
 *
 * @package     FwlibTest\Base
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+FwlibTest@gmail.com>
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
     * Test table: user
     *
     * @var string
     */
    protected static $tblUser = 'test_user';

    /**
     * Test table: group
     *
     * @var string
     */
    protected static $tblGroup = 'test_group';


    /**
     * Constructor
     *
     * Call this in SubClass::construct() with param to select db.
     *
     * @param   string  $profile    Db profile selected, split by comma
     */
    public function __construct($profile = 'default')
    {
        parent::__construct();

        // New db connection, default
        $dbprofile = ConfigGlobal::get('dbserver.default');
        if (false !== strpos($profile, 'default') &&
            !empty($dbprofile['host'])
        ) {
            self::$db = new Adodb($dbprofile);
            self::$db->connect();
        }

        // New db connection, mysql
        $dbprofile = ConfigGlobal::get('dbserver.mysql');
        if (false !== strpos($profile, 'mysql') &&
            !empty($dbprofile['host'])
        ) {
            self::$dbMysql = new Adodb($dbprofile);
            self::$dbMysql->connect();
        }

        // New db connection, sybase
        $dbprofile = ConfigGlobal::get('dbserver.sybase');
        if (false !== strpos($profile, 'sybase') &&
            !empty($dbprofile['host'])
        ) {
            self::$dbSyb = new Adodb($dbprofile);
            self::$dbSyb->connect();
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
            'CREATE TABLE ' . self::$tblUser . '(
                uuid        CHAR(36)        NULL,
                age         INTEGER         NOT NULL DEFAULT 0,
                credit      DECIMAL(6, 2)   NULL,
                title       VARCHAR(255)    NULL,
                joindate    DATETIME        NULL,
                uuidGroup   CHAR(36)        NULL
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
            'CREATE TABLE ' . self::$tblGroup . '(
                uuid        CHAR(36)        NULL,
                title       CHAR(255)       NULL
            );
            '
        );

        if (0 != $db->errorNo()) {
            self::markTestSkipped(
                'Create test table group error: ' .
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
            'DROP TABLE ' . self::$tblUser
        );

        $db->execute(
            'DROP TABLE ' . self::$tblGroup
        );
    }


    public static function setUpBeforeClass()
    {
        if (!is_null(self::$dbMysql) && self::$dbMysql->isConnected()) {
            self::createTable(self::$dbMysql);
        }
        if (!is_null(self::$dbSyb) && self::$dbSyb->isConnected()) {
            self::createTable(self::$dbSyb);
        }

        if (!is_null(self::$db) && self::$db->isConnected() &&
            !self::$db->checkTblExist(self::$tblUser)) {
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
            self::$db->checkTblExist(self::$tblUser)) {
            self::dropTable(self::$db);
        }
    }
}
