<?php
namespace Fwlib\Db;

use Fwlib\Bridge\Adodb;
use Fwlib\Db\Exception\DbNotConnectedException;

/**
 * Trait for classes use Adodb
 *
 * Have no outer feature, no interface needed.
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait AdodbAwareTrait
{
    /**
     * Db connection instance
     *
     * @var Adodb
     */
    protected $db = null;


    /**
     * Get db connection instance
     *
     * Can not provide default return value if db property is null, because db
     * is strong environment dependence,  and standard service container do not
     * provide db service. This can be changed by extending in production
     * environment.
     *
     * @see \Fwlib\Base\ServiceContainer
     * @see \FwlibTest\Aide\TestServiceContainerAwareTrait
     *
     * @return  Adodb
     * @throws  DbNotConnectedException
     */
    protected function getDb()
    {
        if (is_null($this->db) || !$this->db->isConnected()) {
            throw new DbNotConnectedException('Db is not connected');
        }

        return $this->db;
    }


    /**
     * Setter of $db
     *
     * @param   Adodb   $db
     * @return  static
     */
    public function setDb(Adodb $db)
    {
        $this->db = $db;

        return $this;
    }
}
