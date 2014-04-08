<?php
namespace Fwlib\Db\Diff;

use Fwlib\Db\Diff\RowSet;

/**
 * RowSet executor interface
 *
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-04-08
 */
interface ExecutorInterface
{
    /**
     * Commit row set, change db
     *
     * @param   RowSet  $rowSet
     * @return  ExecutorInterface
     */
    public function commit(RowSet $rowSet);


    /**
     * Execute row set if not executed
     *
     * @param   RowSet  $rowSet
     * @return  ExecutorInterface
     */
    public function execute(RowSet $rowSet);


    /**
     * Rollback committed row set
     *
     * @param   RowSet  $rowSet
     * @return  ExecutorInterface
     */
    public function rollback(RowSet $rowSet);
}
