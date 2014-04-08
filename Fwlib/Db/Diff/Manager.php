<?php
namespace Fwlib\Db\Diff;

use Fwlib\Bridge\Adodb;
use Fwlib\Db\Diff\Row;
use Fwlib\Db\Diff\RowSet;

/**
 * Manage and execute RowSet
 *
 * @copyright   Copyright 2012-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2012-12-12
 */
class Manager
{
    /**
     * @var Fwlib\Bridge\Adodb
     */
    protected $db = null;

    /**
     * Cache of table primaryKey, reduce db query
     *
     * @var array
     */
    protected $primaryKeyCache = array();

    /**
     * @var RowSet
     */
    protected $rowSet = null;


    /**
     * Constructor
     *
     * @param   Adodb   $db
     * @param   RowSet  $rowSet
     */
    public function __construct($db = null, RowSet $rowSet = null)
    {
        $this->db = $db;

        if (empty($rowSet)) {
            $this->renew();
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
                empty($oldRow) ? null : $oldRow[$key],
                empty($newRow) ? null : $newRow[$key]
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
     * Check primary key exists as index in data array, or thorw exception
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
        if (empty($this->rowSet) || 0 == $this->rowSet->getRowCount()) {
            return $this;
        }

        if ($this->rowSet->isCommitted()) {
            throw new \Exception('RowSet is already committed');
        }

        $sqlArray = $this->generateCommitSql();
        $db = $this->getDb();

        $db->BeginTrans();

        try {
            foreach ((array)$sqlArray as $sql) {
                $db->execute($sql);

                if (0 != $db->getErrorCode()) {
                    throw new \Exception($db->getErrorMessage());
                }
            }

            $db->CommitTrans();
            $this->rowSet->setCommitted();

        } catch (\Exception $e) {
            $db->RollbackTrans();

            throw new \Exception($e->getMessage());
        }

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
        if ($this->rowSet->isExecuted()) {
            throw new \Exception('RowSet is already executed');
        }

        $this->commit();

        return $this;
    }


    /**
     * Generate commit sql array
     *
     * Didn't check execute status of row set.
     *
     * @return  array
     */
    protected function generateCommitSql()
    {
        $sqlArray = array();
        $db = $this->getDb();

        foreach ($this->rowSet->getRows() as $row) {
            $sqlConfig = array();
            $table = $row->getTable();

            switch ($row->getMode()) {
                case 'INSERT':
                    $sqlConfig['INSERT'] = $table;

                    $sqlConfig['VALUES'] = $row->getNew();

                    break;

                case 'DELETE':
                    $sqlConfig['DELETE'] = $table;
                    // Limit rowcount to 1 for safety
                    $sqlConfig['LIMIT'] = 1;

                    foreach ((array)$row->getPrimaryKey() as $key) {
                        $sqlConfig['WHERE'][] = $key . ' = ' .
                            $db->quoteValue($table, $key, $row->getOld($key));
                    }

                    break;

                case 'UPDATE':
                    $sqlConfig['UPDATE'] = $table;
                    // Limit rowcount to 1 for safety
                    $sqlConfig['LIMIT'] = 1;

                    $sqlConfig['SET'] = $row->getNewWithoutPrimaryKey();

                    foreach ((array)$row->getPrimaryKey() as $key) {
                        $sqlConfig['WHERE'][] = $key . ' = ' .
                            $db->quoteValue($table, $key, $row->getOld($key));
                    }

                    break;

                default:
                    throw new \Exception("Invalid mode {$row->getMode()}");
            }

            $sqlArray[] = $db->generateSql($sqlConfig);
        }

        return $sqlArray;
    }


    /**
     * Generate rollback sql array
     *
     * Didn't check execute status of row set.
     *
     * @return  array
     */
    protected function generateRollbackSql()
    {
        $sqlArray = array();
        $db = $this->getDb();

        foreach ($this->rowSet->getRows() as $row) {
            $sqlConfig = array();
            $table = $row->getTable();

            switch ($row->getMode()) {
                case 'INSERT':
                    $sqlConfig['DELETE'] = $table;
                    // Limit rowcount to 1 for safety
                    $sqlConfig['LIMIT'] = 1;

                    foreach ((array)$row->getPrimaryKey() as $key) {
                        $sqlConfig['WHERE'][] = $key . ' = ' .
                            $db->quoteValue($table, $key, $row->getNew($key));
                    }

                    break;

                case 'DELETE':
                    $sqlConfig['INSERT'] = $table;

                    $sqlConfig['VALUES'] = $row->getOld();

                    break;

                case 'UPDATE':
                    $sqlConfig['UPDATE'] = $table;
                    // Limit rowcount to 1 for safety
                    $sqlConfig['LIMIT'] = 1;

                    $sqlConfig['SET'] = $row->getOldWithoutPrimaryKey();

                    foreach ((array)$row->getPrimaryKey() as $key) {
                        $sqlConfig['WHERE'][] = $key . ' = ' .
                            $db->quoteValue($table, $key, $row->getNew($key));
                    }

                    break;

                default:
                    throw new \Exception("Invalid mode {$row->getMode()}");
            }

            $sqlArray[] = $db->generateSql($sqlConfig);
        }

        return $sqlArray;
    }


    /**
     * Getter of db connection
     *
     * @return  Adodb
     */
    protected function getDb()
    {
        return $this->db;
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
        if (isset($this->primaryKeyCache[$table])) {
            return $this->primaryKeyCache[$table];
        }

        $primaryKey = $this->getDb()->getMetaPrimaryKey($table);

        if (empty($primaryKey)) {
            throw new \Exception("Table $table has no primary key");
        }

        $this->primaryKeyCache[$table] = $primaryKey;
        return $primaryKey;
    }


    /**
     * Getter of $rowSet
     *
     * @return  Rowset
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
        if (empty($this->rowSet) || 0 == $this->rowSet->getRowCount()) {
            return $this;
        }

        if ($this->rowSet->isRollbacked()) {
            throw new \Exception('RowSet is already rollbacked');
        }

        $sqlArray = $this->generateRollbackSql();
        $db = $this->getDb();

        $db->BeginTrans();

        try {
            foreach ((array)$sqlArray as $sql) {
                $db->execute($sql);

                if (0 != $db->getErrorCode()) {
                    throw new \Exception($db->getErrorMessage());
                }
            }

            $db->CommitTrans();
            $this->rowSet->setRollbacked();

        } catch (\Exception $e) {
            $this->db->RollbackTrans();

            throw new \Exception($e->getMessage());
        }

        return $this;
    }


    /**
     * Setter of db connection
     *
     * @param   Adodb   $db
     * @return  Manager
     */
    public function setDb(Adodb $db)
    {
        $this->db = $db;

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
