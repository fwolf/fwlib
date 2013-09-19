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
    protected $dbUsing = '';

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
     */
    public function __construct()
    {
        parent::__construct();

        $this->connectDb($this->dbUsing);
    }


    /**
     * Connect to db and assign to static property $dbXxx
     *
     * @see $dbUsing
     * @param   string   $profile    Db profile, multi splitted by ','
     */
    protected function connectDb($profile)
    {
        if (empty($profile)) {
            return;
        }

        $profileKey = array();
        $varName = array();

        $profileAr = explode(',', $profile);
        foreach ($profileAr as $type) {
            $type = trim($type);
            switch ($type) {
                case 'default':
                    $profileKey[] = 'default';
                    $varName[] = 'db';
                    break;
                case 'sybase':
                    $profileKey[] = 'sybase';
                    $varName[] = 'dbSyb';
                    break;
                default:
                    $profileKey[] = $type;
                    $varName[] = 'db' . ucfirst($type);
            }
        }

        // New db connection
        foreach ($profileKey as $i => $key) {
            $dbprofile = ConfigGlobal::get('dbserver.' . $key);
            if (!empty($dbprofile['host'])) {
                $name = $varName[$i];
                if (is_null(self::${$name})) {
                    self::${$name} = new Adodb($dbprofile);
                    self::${$name}->connect();
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
            'CREATE TABLE ' . self::$tblUser . '(
                uuid        CHAR(36)        NULL,
                title       VARCHAR(255)    NULL,
                age         INTEGER         NOT NULL DEFAULT 0,
                credit      DECIMAL(6, 2)   NULL,
                joindate    DATETIME        NULL,
                uuidGroup   CHAR(36)        NULL,
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
            'CREATE TABLE ' . self::$tblGroup . '(
                uuid        CHAR(36)        NULL,
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
