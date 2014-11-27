<?php
namespace Fwlib\Db;

use Fwlib\Base\AbstractAutoNewInstance;

/**
 * Db client class with property $db
 *
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 */
abstract class AbstractDbClient extends AbstractAutoNewInstance
{
    /**
     * Db connection object
     *
     * @var object
     */
    protected $db = null;


    /**
     * Constructor
     *
     * @param   object  $db
     */
    public function __construct($db = null)
    {
        if (!is_null($db)) {
            $this->db = $db;
        }
    }


    /**
     * New db object
     *
     * @return  object
     */
    protected function getDb()
    {
        if (is_null($this->db)) {
            $this->db = $this->getService('Db');
        }

        return $this->db;
    }
}
