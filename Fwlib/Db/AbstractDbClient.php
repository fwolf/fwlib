<?php
namespace Fwlib\Db;

use Fwlib\Base\AbstractAutoNewObj;

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
     * Constructor
     *
     * @param   object  $db
     */
    public function __construct($db = null)
    {
        // Unset for auto new
        unset($this->db);
        if (!is_null($db)) {
            $this->db = $db;
        }
    }


    /**
     * New db object
     *
     * @return  Fwlib\Bridge\Adodb
     */
    protected function newObjDb()
    {
        $this->checkServiceContainer();

        return $this->serviceContainer->get('Db');
    }
}
