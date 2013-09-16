<?php
namespace Fwlib\Test;

use Fwlib\Bridge\Adodb;
use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Config\ConfigGlobal;

/**
 * Parent class for db relate tests
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
     * @var object
     */
    protected static $db = null;

    /**
     * Db connection, mysql
     *
     * @var object
     */
    protected static $dbMysql = null;

    /**
     * Db connection, Sybase
     *
     * @var object
     */
    protected static $dbSyb = null;


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
}
