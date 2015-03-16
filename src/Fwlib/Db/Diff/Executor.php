<?php
namespace Fwlib\Db\Diff;

use Fwlib\Bridge\Adodb;
use Fwlib\Db\Diff\ExecutorInterface;
use Fwlib\Db\Diff\RowSet;

/**
 * RowSet executor
 *
 * All dependence of Adodb moved to this class. If it need to use other db
 * library rather than Adodb, just extend this class or write another Executor
 * then bind to Manager.
 *
 * @copyright   Copyright 2012-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Executor implements ExecutorInterface
{
    /**
     * @var Adodb
     */
    protected $db = null;

    /**
     * Cache of table primaryKey, reduce db query
     *
     * @var array
     */
    protected $primaryKeyCache = [];


    /**
     * Constructor
     *
     * @param   Adodb   $db
     */
    public function __construct(Adodb $db = null)
    {
        $this->db = $db;
    }


    /**
     * {@inheritdoc}
     */
    public function commit(RowSet $rowSet)
    {
        if (empty($rowSet) || 0 == $rowSet->getRowCount()) {
            return $this;
        }

        if ($rowSet->isCommitted()) {
            throw new \Exception('RowSet is already committed');
        }

        $sqlArray = $this->generateCommitSql($rowSet);
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
            $rowSet->setCommitted();

        } catch (\Exception $e) {
            $db->RollbackTrans();

            throw new \Exception($e->getMessage());
        }

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function execute(RowSet $rowSet)
    {
        if ($rowSet->isExecuted()) {
            throw new \Exception('RowSet is already executed');
        }

        $this->commit($rowSet);

        return $this;
    }


    /**
     * Generate commit sql array
     *
     * Did not check execute status of row set.
     *
     * @param   RowSet  $rowSet
     * @return  array
     */
    protected function generateCommitSql(RowSet $rowSet)
    {
        $sqlArray = [];
        $db = $this->getDb();

        foreach ($rowSet->getRows() as $row) {
            $sqlConfig = [];
            $table = $row->getTable();

            switch ($row->getMode()) {
                case 'INSERT':
                    $sqlConfig['INSERT'] = $table;

                    $sqlConfig['VALUES'] = $row->getNew();

                    break;

                case 'DELETE':
                    $sqlConfig['DELETE'] = $table;
                    // Limit row count to 1 for safety
                    $sqlConfig['LIMIT'] = 1;

                    foreach ((array)$row->getPrimaryKey() as $key) {
                        $sqlConfig['WHERE'][] = $key . ' = ' .
                            $db->quoteValue($table, $key, $row->getOld($key));
                    }

                    break;

                case 'UPDATE':
                    $sqlConfig['UPDATE'] = $table;
                    // Limit row count to 1 for safety
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
     * Did not check execute status of row set.
     *
     * @param   RowSet  $rowSet
     * @return  array
     */
    protected function generateRollbackSql(RowSet $rowSet)
    {
        $sqlArray = [];
        $db = $this->getDb();

        // Rollback SQL is in reverse order of commit
        foreach (array_reverse($rowSet->getRows()) as $row) {
            $sqlConfig = [];
            $table = $row->getTable();

            switch ($row->getMode()) {
                case 'INSERT':
                    $sqlConfig['DELETE'] = $table;
                    // Limit row count to 1 for safety
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
                    // Limit row count to 1 for safety
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
     * {@inheritdoc}
     */
    public function rollback(RowSet $rowSet)
    {
        if (empty($rowSet) || 0 == $rowSet->getRowCount()) {
            return $this;
        }

        if ($rowSet->isRollbacked()) {
            throw new \Exception('RowSet is already rollbacked');
        }

        $sqlArray = $this->generateRollbackSql($rowSet);
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
            $rowSet->setRollbacked();

        } catch (\Exception $e) {
            $db->RollbackTrans();

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
}
