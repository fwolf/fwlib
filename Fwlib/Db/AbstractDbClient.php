<?php
namespace Fwlib\Db;

use Fwlib\Base\AbstractAutoNewObj;
use Fwlib\Bridge\Adodb;

/**
 * Db client class with property $db
 *
 * @package     Fwlib\Db
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-10-08
 */
abstract class AbstractDbClient extends AbstractAutoNewObj
{
    /**
     * Db connection object
     *
     * @var Fwlib\Bridge\Adodb
     */
    public $db = null;

    /**
     * Db profile
     *
     * {host, user, pass, name, type, lang}
     *
     * @var array
     */
    public $dbProfile = array();


    /**
     * Constructor
     *
     * @param   array   $dbProfile
     */
    public function __construct($dbProfile = null)
    {
        // Unset for auto new
        unset($this->db);

        if (!empty($dbProfile)) {
            $this->setDbProfile($dbProfile);
        }
    }


    /**
     * Create database connection
     *
     * $fetchMode:
     * 0 ADODB_FETCH_DEFAULT
     * 1 ADODB_FETCH_NUM
     * 2 ADODB_FETCH_ASSOC (default)
     * 3 ADODB_FETCH_BOTH
     *
     * @param   array   $dbProfile
     * @param   int     $fetchMode
     * @return  Fwlib\Bridge\Adodb
     */
    protected function connectDb($dbProfile, $fetchMode = 2)
    {
        $conn = new Adodb($dbProfile);

        if ($conn->connect()) {
            // Connect successful, set fetch mode
            $conn->SetFetchMode($fetchMode);

            return $conn;

        } else {
            // Connect fail
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }
    }


    /**
     * New db object
     *
     * @return  Fwlib\Bridge\Adodb
     */
    protected function newObjDb()
    {
        return $this->connectDb($this->dbProfile);
    }


    /**
     * Set db profile
     *
     * Notice: Db profile is NOT checked or validated.
     *
     * @param   array   $dbProfile
     * @param   boolean $connect    Connect to db after profile set.
     * @return  boolean
     */
    public function setDbProfile($dbProfile, $connect = true)
    {
        if (!empty($dbProfile) && is_array($dbProfile)) {
            $this->dbProfile = $dbProfile;

            if ($connect) {
                $this->db = $this->connectDb($this->dbProfile);

                return !is_null($this->db);
            } else {
                return true;
            }

        } else {
            return false;
        }
    }
}
