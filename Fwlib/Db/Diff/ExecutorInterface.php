<?php
namespace Fwlib\Db\Diff;

use Fwlib\Db\Diff\RowSet;

/**
 * RowSet executor interface
 *
 * @copyright   Copyright 2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
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
