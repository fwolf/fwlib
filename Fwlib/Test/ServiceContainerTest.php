<?php
namespace Fwlib\Test;

use Fwlib\Base\AbstractServiceContainer;
use Fwlib\Bridge\Adodb;
use Fwlib\Config\ConfigGlobal;

/**
 * Service Container for testcase
 *
 * @codeCoverageIgnore
 *
 * @package     Fwlib\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-11-07
 */
class ServiceContainerTest extends AbstractServiceContainer
{
    /**
     * New db instance and do connect
     *
     * $fetchMode:
     * 0 ADODB_FETCH_DEFAULT
     * 1 ADODB_FETCH_NUM
     * 2 ADODB_FETCH_ASSOC (default)
     * 3 ADODB_FETCH_BOTH
     *
     * @param   string   $dbProfile
     */
    protected function connectDb($dbProfile)
    {
        $conn = new Adodb($dbProfile);

        if ($conn->connect()) {
            // Connect successful, set fetch mode
            $conn->SetFetchMode(2);

            return $conn;

        } else {
            // Connect fail
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }
    }


    /**
     * New Adodb service object, default db
     *
     * @return  object
     */
    protected function newDb()
    {
        $dbProfile = ConfigGlobal::get('dbserver.default');

        return $this->connectDb($dbProfile);
    }


    /**
     * New Adodb service object, Mysql db
     *
     * @return  object
     */
    protected function newDbMysql()
    {
        $dbProfile = ConfigGlobal::get('dbserver.mysql');

        return $this->connectDb($dbProfile);
    }


    /**
     * New Adodb service object, Sybase db
     *
     * @return  object
     */
    protected function newDbSyb()
    {
        $dbProfile = ConfigGlobal::get('dbserver.sybase');

        return $this->connectDb($dbProfile);
    }
}