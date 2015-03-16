<?php
namespace Fwlib\Db\Diff;

use Fwlib\Db\Diff\Executor;
use Fwlib\Db\Diff\ExecutorInterface;
use Fwlib\Db\Diff\Row;
use Fwlib\Db\Diff\RowSet;

/**
 * Manage and execute RowSet
 *
 * The execute of RowSet is done by Executor.
 *
 * @copyright   Copyright 2012-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Manager
{
    /**
     * @var ExecutorInterface
     */
    protected $executor = null;

    /**
     * Cache of table primaryKey, reduce db query
     *
     * @var array
     */
    protected $primaryKeyCache = [];

    /**
     * @var RowSet
     */
    protected $rowSet = null;


    /**
     * Constructor
     *
     * @param   RowSet|string   $rowSet
     */
    public function __construct($rowSet = null)
    {
        if (empty($rowSet)) {
            $this->renew();
        } else {
            $this->setRowSet($rowSet);
        }
    }


    /**
     * Add a row to $rowSet
     *
     * @param   string      $table
     * @param   array|null  $old
     * @param   array|null  $new
     * @return  Manager
     */
    public function addRow($table, $old, $new)
    {
        $this->checkIfCanAddRow();

        if ($this->isSame($old, $new)) {
            return $this;
        }

        $primaryKey = $this->getPrimaryKey($table);

        if (empty($old)) {
            // INSERT mode
            $this->checkPrimaryKeyExist($primaryKey, $new);
            $old = null;

        } elseif (empty($new)) {
            // DELETE mode
            $this->checkPrimaryKeyExist($primaryKey, $old);
            $new = null;

        } else {
            // UPDATE mode
            $this->checkPrimaryKeyExist($primaryKey, $old);
            $this->checkPrimaryKeyExist($primaryKey, $new);
        }

        $this->rowSet->addRow(
            $this->createRow($table, $primaryKey, $old, $new)
        );

        return $this;
    }


    /**
     * Add multiple rows to $rowSet
     *
     * If both $oldRows and $newRows are not empty, their key should keep same
     * value and sequence.
     *
     * @param   string      $table
     * @param   array|null  $oldRows
     * @param   array|null  $newRows
     * @return  Manager
     */
    public function addRows($table, $oldRows, $newRows)
    {
        $this->checkIfCanAddRow();

        if (empty($oldRows) && empty($newRows)) {
            return $this;
        }

        if (empty($oldRows)) {
            $keys = array_keys($newRows);
        } else {
            $keys = array_keys($oldRows);
        }

        foreach ($keys as $key) {
            $this->addRow(
                $table,
                empty($oldRows) ? null : $oldRows[$key],
                empty($newRows) ? null : $newRows[$key]
            );
        }

        return $this;
    }


    /**
     * If it can add row now ?
     *
     * If current row set is null or executed, it can't
     */
    protected function checkIfCanAddRow()
    {
        if (is_null($this->rowSet)) {
            throw new \Exception('No RowSet set');
        }

        if ($this->rowSet->isExecuted()) {
            throw new \Exception(
                'Can\'t add row when RowSet is already executed'
            );
        }
    }


    /**
     * Check primary key exists as index in data array, or throw exception
     *
     * @param   array|string    $primaryKey
     * @param   array           $data
     */
    protected function checkPrimaryKeyExist($primaryKey, $data)
    {
        foreach ((array)$primaryKey as $key) {
            if (!isset($data[$key])) {
                throw new \Exception(
                    "Primary key $key is not included in data array"
                );
            }
        }
    }


    /**
     * Commit row set, change db
     *
     * @return  Manager
     */
    public function commit()
    {
        $this->getExecutor()->commit($this->rowSet);

        return $this;
    }


    /**
     * Create a new Row instance
     *
     * This method can be extend to use custom Row class.
     *
     * @param   string          $table
     * @param   array|string    $primaryKey
     * @param   array|null      $old
     * @param   array|null      $new
     * @return  Row
     */
    protected function createRow($table, $primaryKey, $old, $new)
    {
        return new Row($table, $primaryKey, $old, $new);
    }


    /**
     * Execute row set if not executed
     *
     * @return  Manager
     */
    public function execute()
    {
        $this->getExecutor()->execute($this->rowSet);

        return $this;
    }


    /**
     * Getter of RowSet Executor
     *
     * @return  ExecutorInterface
     */
    protected function getExecutor()
    {
        if (is_null($this->executor)) {
            $this->executor = new Executor();
        }

        return $this->executor;
    }


    /**
     * Get table primary key
     *
     * This method can be extend to use different db connection.
     *
     * @param   string  $table
     * @return  array|string
     */
    protected function getPrimaryKey($table)
    {
        return 'uuid';

        /**
         * After removed dependence of Adodb, there are no way to retrieve
         * table primary key, so here is 2 solution:
         *
         * - Use stable primary key or have a map here to got it
         * - Query from db by Adodb or other db library
         *
         * By default we use solid 'uuid' as primary key, the old code using
         * Adodb is commented below.
         */

        /*
        if (isset($this->primaryKeyCache[$table])) {
            return $this->primaryKeyCache[$table];
        }

        $primaryKey = $this->getDb()->getMetaPrimaryKey($table);

        if (empty($primaryKey)) {
            throw new \Exception("Table $table has no primary key");
        }

        $this->primaryKeyCache[$table] = $primaryKey;
        return $primaryKey;
         */
    }


    /**
     * Getter of $rowSet
     *
     * @return  RowSet
     */
    public function getRowSet()
    {
        return $this->rowSet;
    }


    /**
     * Check if old and new data array are same
     *
     * @param   array|null  $old
     * @param   array|null  $new
     * @return  boolean
     */
    protected function isSame($old, $new)
    {
        if (empty($old) && empty($new)) {
            return true;
        }

        if ((empty($old) && !empty($new)) ||
            (!empty($old) && empty($old))
        ) {
            return false;
        }

        $diff = array_diff_assoc((array)$old, (array)$new);

        return empty($diff);
    }


    /**
     * Create a new empty $rowSet
     *
     * @return  Manager
     */
    public function renew()
    {
        $this->rowSet = new RowSet();

        return $this;
    }


    /**
     * Rollback committed row set
     *
     * @return  Manager
     */
    public function rollback()
    {
        $this->getExecutor()->rollback($this->rowSet);

        return $this;
    }


    /**
     * Setter of RowSet Executor
     *
     * @param   ExecutorInterface   $executor
     * @return  Manager
     */
    public function setExecutor(ExecutorInterface $executor)
    {
        $this->executor = $executor;

        return $this;
    }


    /**
     * Setter of RowSet
     *
     * @param   RowSet|string   $rowSet
     * @return  Manager
     */
    public function setRowSet($rowSet)
    {
        if (is_string($rowSet)) {
            $rowSet = new RowSet($rowSet);
        }

        $this->rowSet = $rowSet;

        return $this;
    }
}
