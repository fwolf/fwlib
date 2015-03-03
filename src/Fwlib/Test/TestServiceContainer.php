<?php
namespace Fwlib\Test;

use Fwlib\Base\ServiceContainer;
use Fwlib\Bridge\Adodb;
use Fwlib\Config\GlobalConfig;

/**
 * Service Container for test case
 *
 * Include environment requirement like db for running test, should not use in
 * production.
 *
 * @codeCoverageIgnore
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class TestServiceContainer extends ServiceContainer
{
    /**
     * Create db instance and do connect
     *
     * $fetchMode:
     * 0 ADODB_FETCH_DEFAULT
     * 1 ADODB_FETCH_NUM
     * 2 ADODB_FETCH_ASSOC (default)
     * 3 ADODB_FETCH_BOTH
     *
     * @param   array   $profile
     * @return  Adodb
     */
    protected function connectDb(array $profile)
    {
        $conn = new Adodb($profile);

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
     * Create Adodb service object, default db
     *
     * @return  Adodb
     */
    protected function createDb()
    {
        return $this->connectDb(
            GlobalConfig::getInstance()->get('dbserver.default')
        );
    }


    /**
     * Create Adodb service object, Mysql db
     *
     * @return  Adodb
     */
    protected function createMysqlDb()
    {
        return $this->connectDb(
            GlobalConfig::getInstance()->get('dbserver.mysql')
        );
    }


    /**
     * Create Adodb service object, Sybase db
     *
     * @return  Adodb
     */
    protected function createSybaseDb()
    {
        return $this->connectDb(
            GlobalConfig::getInstance()->get('dbserver.sybase')
        );
    }


    /**
     * @return  Adodb
     */
    public function getDb()
    {
        return $this->get('Db');
    }


    /**
     * @return  Adodb
     */
    public function getMysqlDb()
    {
        return $this->get('MysqlDb');
    }


    /**
     * @return  Adodb
     */
    public function getSybaseDb()
    {
        return $this->get('SybaseDb');
    }
}
