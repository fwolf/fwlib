<?php
namespace Fwlib\Test;

use Fwlib\Base\AbstractServiceContainer;
use Fwlib\Bridge\Adodb;
use Fwlib\Html\ListTable;

/**
 * Service Container example, also for testcase
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
     * {@inheritdoc}
     *
     * @var array
     */
    protected $serviceClass = array(
        'Curl'          => 'Fwlib\Net\Curl',
        'GlobalConfig'  => 'Fwlib\Config\GlobalConfig',
        'Smarty'        => 'Fwlib\Bridge\Smarty',
        'Util'          => 'Fwlib\Util\UtilContainer',
        'UtilContainer' => 'Fwlib\Util\UtilContainer',
        'Validator'     => 'Fwlib\Validator\Validator',
    );


    /**
     * New db instance and do connect
     *
     * $fetchMode:
     * 0 ADODB_FETCH_DEFAULT
     * 1 ADODB_FETCH_NUM
     * 2 ADODB_FETCH_ASSOC (default)
     * 3 ADODB_FETCH_BOTH
     *
     * @param   string   $profile
     */
    protected function connectDb($profile)
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
     * New Adodb service object, default db
     *
     * @return  object
     */
    protected function newDb()
    {
        $profile = $this->get('GlobalConfig')->get('dbserver.default');

        return $this->connectDb($profile);
    }


    /**
     * New Adodb service object, Mysql db
     *
     * @return  object
     */
    protected function newDbMysql()
    {
        $profile = $this->get('GlobalConfig')->get('dbserver.mysql');

        return $this->connectDb($profile);
    }


    /**
     * New Adodb service object, Sybase db
     *
     * @return  object
     */
    protected function newDbSyb()
    {
        $profile = $this->get('GlobalConfig')->get('dbserver.sybase');

        return $this->connectDb($profile);
    }


    /**
     * New ListTable service instance
     *
     * @return  ListTable
     */
    protected function newListTable()
    {
        return new ListTable($this->get('Smarty'));
    }
}
