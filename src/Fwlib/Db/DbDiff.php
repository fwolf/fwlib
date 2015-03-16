<?php
namespace Fwlib\Db;

use Fwlib\Bridge\Adodb;
use Fwlib\Util\UtilContainerAwareTrait;

/**
 * Compare db data before and after write
 *
 * Diff data array format:
 * {table: [{mode, 'pk': pk, 'column': column}]}
 *
 * mode is one of INSERT, DELETE, UPDATE.
 *
 * pk is array of primary key and their new/old value, format:
 * {pk: {new, old}}.
 *
 * column is array of other columns changed, format:
 * {column: {new, old}}.
 *
 * @deprecated
 *
 * @copyright   Copyright 2012-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class DbDiff
{
    use UtilContainerAwareTrait;


    /**
     * Counter of DbDiff changed rows
     *
     * @var int
     */
    protected $rowCount = 0;

    /**
     * @var Adodb
     */
    protected $db = null;

    /**
     * Diff data array
     *
     * @var array
     */
    protected $diff = [];

    /**
     * DbDiff execute status
     *
     * 0    not executed
     * 100  committed
     * -100 rollbacked
     *
     * @var int
     */
    protected $executeStatus = 0;


    /**
     * Constructor
     *
     * @param   Adodb   $db
     */
    public function __construct($db)
    {
        $this->db = $db;
    }


    /**
     * Check PK array is valid or throw exception
     *
     * @param   string  $table
     * @param   array   $pkArray
     * @param   array   &$rowArray
     */
    protected function checkPkArray($table, array $pkArray, array &$rowArray)
    {
        if (empty($pkArray)) {
            throw new \Exception(
                "Table $table must have PK defined"
            );
        }

        // array_intersect() is less loop than array_key_exist() check.
        // Can't use isset when do array_key_exist(), bcs isset(null) == false
        foreach ($rowArray as $index => &$row) {
            if (count($pkArray) !=
                count(array_intersect($pkArray, array_keys($row)))
            ) {
                throw new \Exception(
                    "PK not all assigned in new array," .
                    " table $table index $index"
                );
            }
        }
        unset($row);
    }


    /**
     * Commit diff result, change db
     *
     * @return  DbDiff
     */
    public function commit()
    {
        if (empty($this->diff)) {
            throw new \Exception('No diff data');
        }

        if ($this->isCommitted()) {
            throw new \Exception(
                'Committed DbDiff can\'t commit again'
            );
        }

        $sqlArray = $this->generateCommitSql();

        $this->db->BeginTrans();

        try {
            foreach ((array)$sqlArray as $sql) {
                $this->db->execute($sql);

                if (0 != $this->db->getErrorCode()) {
                    throw new \Exception($this->db->getErrorMessage());
                }
            }

            $this->db->CommitTrans();
            $this->rowCount = count($sqlArray);
            $this->executeStatus = 100;

        } catch (\Exception $e) {
            $this->db->RollbackTrans();

            throw new \Exception($e->getMessage());
        }

        return $this;
    }


    /**
     * Compare new data with old data in db
     *
     * New/old array are all assoc, index by table column.  PK column must
     * included in new array, but its value can be null.
     *
     * In new array, null PK value means DELETE operate.
     * In old array, null PK value means INSERT operate.
     *
     * Multi table and row supported.
     *
     * New/old array format:
     * {table: [{column: value}]}
     * If only need to change a single row, can use simple format:
     * {table: {column: value}}
     *
     * If old array is null, will read its value from db by Pk in new array.
     *
     * Row index in new/old array must match, it is used to connect same row
     * in new and old data, so either use a meaningful row index, or use default
     * integer index and keep same data order in new and old.
     *
     * @param   array   $dataNew
     * @param   array   $dataOld
     * @return  DbDiff
     */
    public function compare(array $dataNew, array $dataOld = null)
    {
        if (empty($dataNew)) {
            throw new \Exception('New data array can\'t be empty');
        }


        $diff = [];
        foreach ($dataNew as $table => &$rowArrayNew) {
            // Convert row array to 2-dim array
            if (!is_array(current($rowArrayNew))) {
                $rowArrayNew = [$rowArrayNew];
            }

            $pkArray = (array)$this->db->getMetaPrimaryKey($table);
            $this->checkPkArray($table, $pkArray, $rowArrayNew);

            $diffOfTable = $this->compareTable(
                $table,
                $pkArray,
                $rowArrayNew,
                $dataOld
            );

            if (!empty($diffOfTable)) {
                $diff[$table] = $diffOfTable;
            }
        }
        unset($rowArrayNew);

        $this->diff = $diff;

        return $this;
    }


    /**
     * Compare different of a single row
     *
     * $rowNew MUST contain all PK columns.
     *
     * @param   array   &$rowNew
     * @param   array   $pkArray
     * @param   array   &$rowOld
     * @return  array
     */
    protected function compareRow(
        array &$rowNew,
        array $pkArray,
        array &$rowOld = null
    ) {
        $mode = $this->compareRowMode($rowNew, $pkArray, $rowOld);

        $pkDiff = $this->compareRowPk($rowNew, $pkArray, $rowOld);

        $columnArray = array_keys($rowNew);
        if (!is_null($rowOld)) {
            $columnArray = array_merge(array_keys($rowOld));
        }
        $columnDiff = $this->compareRowColumn(
            $mode,
            $rowNew,
            $columnArray,
            $rowOld
        );

        // UPDATE with no column change will be skipped
        if ('UPDATE' == $mode && empty($columnDiff)) {
            return null;

        } else {
            return [
                'mode'   => $mode,
                'pk'     => $pkDiff,
                'column' => $columnDiff,
            ];
        }
    }


    /**
     * Compare column values, return diff array
     *
     * Pk column should be removed already.
     *
     * @param   string  $mode
     * @param   array   &$rowNew
     * @param   array   $columnArray
     * @param   array   &$rowOld
     * @return  array
     */
    protected function compareRowColumn(
        $mode,
        array &$rowNew,
        array $columnArray,
        array &$rowOld = null
    ) {
        $diff = [];

        foreach ((array)$columnArray as $column) {
            $valueNew = isset($rowNew[$column]) ? $rowNew[$column] : null;
            $valueOld = isset($rowOld[$column]) ? $rowOld[$column] : null;

            // Set useless column data to null. New value in DELETE mode is
            // useless, and old value in INSERT mode is useless too.
            if ('DELETE' == $mode) {
                $valueNew = null;
            }
            if ('INSERT' == $mode) {
                $valueOld = null;
            }

            // Skip equal value, they are not included in result diff array.
            // Change between null and non-null value (include '' and 0,
            // although they == null) will be kept. But 1 == '1' will be
            // skipped, so not using === and check equal only if both value
            // are not null.
            if ((is_null($valueNew) && is_null($valueOld)) ||
                (!is_null($valueNew) && !is_null($valueOld) &&
                    $valueNew == $valueOld)
            ) {
                continue;
            }

            $diff[$column] = [
                'new'   => $valueNew,
                'old'   => $valueOld,
            ];
        }

        return $diff;
    }




    /**
     * Get compare mode of a single row
     *
     * Mode: INSERT/UPDATE/DELETE
     *
     * Empty PK value are allowed, but null PK value will change mode flag and
     * ignore other non-null pk column values.
     *
     * @param   array   &$rowNew
     * @param   array   $pkArray
     * @param   array   &$rowOld
     * @return  string
     */
    protected function compareRowMode(
        array &$rowNew,
        array $pkArray,
        array &$rowOld = null
    ) {
        $nullPkInNew = false;
        $nullPkInOld = false;

        foreach ($pkArray as $pk) {
            if (is_null($rowNew[$pk])) {
                $nullPkInNew = true;
            }
            if (!isset($rowOld[$pk]) || is_null($rowOld[$pk])) {
                $nullPkInOld = true;
            }
        }

        if ($nullPkInNew && !$nullPkInOld) {
            $mode = 'DELETE';
        } elseif (!$nullPkInNew && $nullPkInOld) {
            $mode = 'INSERT';
        } elseif (!$nullPkInNew && !$nullPkInOld) {
            $mode = 'UPDATE';
        } else {
            throw new \Exception(
                'Pk in new and old array are all null'
            );
        }

        return $mode;
    }


    /**
     * Compare and extract PK values, return diff array
     *
     * Pk column are removed from new and old row columns.
     *
     * @param   array   &$rowNew
     * @param   array   $pkArray
     * @param   array   &$rowOld
     * @return  array
     */
    protected function compareRowPk(
        array &$rowNew,
        array $pkArray,
        array &$rowOld = null
    ) {
        $diff = [];

        foreach ($pkArray as $pk) {
            $diff[$pk] = [
                'new'   => $rowNew[$pk],
                'old'   => isset($rowOld[$pk]) ? $rowOld[$pk] : null,
            ];

            unset($rowNew[$pk]);
            unset($rowOld[$pk]);
        }

        return $diff;
    }


    /**
     * Compare different for a single table
     *
     * @param   string  $table
     * @param   array   $pkArray
     * @param   array   &$rowArrayNew
     * @param   array   &$dataOld
     * @return  array
     */
    protected function compareTable(
        $table,
        array $pkArray,
        array &$rowArrayNew,
        array &$dataOld = null
    ) {
        $diff = [];
        foreach ($rowArrayNew as $index => &$rowNew) {
            $columnArray = array_keys($rowNew);
            $pkValueArray = array_intersect_key(
                $rowNew,
                array_fill_keys($pkArray, null)
            );

            $rowOld = $this->prepareRowOld(
                $table,
                $index,
                $pkValueArray,
                $columnArray,
                $pkArray,
                $dataOld
            );

            $diffOfRow = $this->compareRow($rowNew, $pkArray, $rowOld);
            if (!empty($diffOfRow)) {
                $diff[] = $diffOfRow;
            }
        }
        unset($rowOld);

        return $diff;
    }


    /**
     * Compare and commit diff result
     *
     * If $dataNew is null, will use internal stored $diff.
     *
     * @param   array   $dataNew
     * @param   array   $dataOld
     * @return  DbDiff
     */
    public function execute(array $dataNew = null, array $dataOld = null)
    {
        if (!is_null($dataNew)) {
            $this->reset();
            $this->compare($dataNew, $dataOld);
        }

        if (!$this->isExecuted()) {
            $this->commit();

        } else {
            throw new \Exception(
                'Committed or rollbacked DbDiff can\'t execute again'
            );
        }

        return $this;
    }


    /**
     * Export to json string
     *
     * @return  string
     */
    public function export()
    {
        $json = $this->getUtilContainer()->getJson();

        return $json->encodeUnicode(
            [
                'rowCount'  => $this->rowCount,
                'executeStatus' => $this->executeStatus,
                'diff'          => $this->diff,
            ]
        );
    }


    /**
     * Generate commit sql array from diff result
     *
     * @return  array
     */
    protected function generateCommitSql()
    {
        $sqlArray = [];
        $db = $this->db;

        foreach ($this->diff as $table => $rowArray) {
            foreach ((array)$rowArray as $index => $row) {
                $sqlConfig = [];

                switch ($row['mode']) {
                    case 'INSERT':
                        $sqlConfig['INSERT'] = $table;

                        $columnArray = $row['pk'] + $row['column'];
                        foreach ($columnArray as $k => $v) {
                            $sqlConfig['VALUES'][$k] = $v['new'];
                        }

                        break;

                    case 'DELETE':
                        $sqlConfig['DELETE'] = $table;
                        // Limit row count to 1 for safety
                        $sqlConfig['LIMIT'] = 1;

                        foreach ($row['pk'] as $k => $v) {
                            $sqlConfig['WHERE'][] = $k . ' = ' .
                                $db->quoteValue($table, $k, $v['old']);
                        }

                        break;

                    case 'UPDATE':
                        $sqlConfig['UPDATE'] = $table;
                        // Limit row count to 1 for safety
                        $sqlConfig['LIMIT'] = 1;

                        foreach ($row['column'] as $k => $v) {
                            $sqlConfig['SET'][$k] = $v['new'];
                        }

                        foreach ($row['pk'] as $k => $v) {
                            $sqlConfig['WHERE'][] = $k . ' = ' .
                                $db->quoteValue($table, $k, $v['new']);
                        }

                        break;

                    default:
                        throw new \Exception("Invalid mode {$row['mode']}");
                }

                $sqlArray[] = $db->generateSql($sqlConfig);
            }
        }

        return $sqlArray;
    }


    /**
     * Generate rollback sql array from diff result
     *
     * @return  array
     */
    protected function generateRollbackSql()
    {
        $sqlArray = [];
        $db = $this->db;

        foreach ($this->diff as $table => $rowArray) {
            foreach ((array)$rowArray as $index => $row) {
                $sqlConfig = [];

                switch ($row['mode']) {
                    case 'INSERT':
                        $sqlConfig['DELETE'] = $table;
                        // Limit row count to 1 for safety
                        $sqlConfig['LIMIT'] = 1;

                        foreach ($row['pk'] as $k => $v) {
                            $sqlConfig['WHERE'][] = $k . ' = ' .
                                $db->quoteValue($table, $k, $v['new']);
                        }

                        break;

                    case 'DELETE':
                        $sqlConfig['INSERT'] = $table;

                        $columnArray = $row['pk'] + $row['column'];
                        foreach ($columnArray as $k => $v) {
                            $sqlConfig['VALUES'][$k] = $v['old'];
                        }

                        break;

                    case 'UPDATE':
                        $sqlConfig['UPDATE'] = $table;
                        // Limit row count to 1 for safety
                        $sqlConfig['LIMIT'] = 1;

                        foreach ($row['column'] as $k => $v) {
                            $sqlConfig['SET'][$k] = $v['old'];
                        }

                        foreach ($row['pk'] as $k => $v) {
                            $sqlConfig['WHERE'][] = $k . ' = ' .
                                $db->quoteValue($table, $k, $v['old']);
                        }

                        break;

                    default:
                        throw new \Exception("Invalid mode {$row['mode']}");
                }

                $sqlArray[] = $db->generateSql($sqlConfig);
            }
        }

        return $sqlArray;
    }


    /**
     * Getter of $diff
     *
     * @return  array
     */
    public function getDiff()
    {
        return $this->diff;
    }


    /**
     * Getter of $rowCount
     *
     * @return  int
     */
    public function getRowCount()
    {
        return $this->rowCount;
    }


    /**
     * Import from a json string
     *
     * Notice: Import empty string will report error.
     *
     * @param   string  $json
     * @return  DbDiff
     */
    public function import($json)
    {
        $this->reset();

        $info = $this->getUtilContainer()->getJson()
            ->decode($json, true);

        try {
            $this->rowCount = $info['rowCount'];
            $this->executeStatus = $info['executeStatus'];
            $this->diff = $info['diff'];

        } catch (\Exception $e) {
            throw new \Exception(
                'Invalid json string to import: ' . $e->getMessage()
            );
        }

        return $this;
    }


    /**
     * Is DbDiff executed/committed ?
     *
     * @return  bool
     */
    public function isCommitted()
    {
        return 100 == $this->executeStatus;
    }


    /**
     * Is DbDiff executed ?
     *
     * Return true when committed or rollbacked.
     *
     * @return  bool
     */
    public function isExecuted()
    {
        return 0 != $this->executeStatus;
    }


    /**
     * Is DbDiff rollbacked ?
     *
     * @return  bool
     */
    public function isRollbacked()
    {
        return -100 == $this->executeStatus;
    }


    /**
     * Prepare a single old row for compare
     *
     * If old row are not set, will query db using pk value from new row,
     * return null for not exists(INSERT mode).
     *
     * Also convert old row array to 2-dim.
     *
     * @param   string      $table
     * @param   int|string  $index
     * @param   array       $pkValueArray
     * @param   array       $columnArray
     * @param   array       $pkArray
     * @param   array       &$dataOld
     * @return  array
     */
    protected function prepareRowOld(
        $table,
        $index,
        array $keyValueArray,
        array $columnArray,
        array $keyArray,
        array &$dataOld = null
    ) {
        // Convert to 2-dim array
        if (isset($dataOld[$table]) && !is_array(current($dataOld[$table]))) {
            $dataOld[$table] = [$dataOld[$table]];
        }

        if (isset($dataOld[$table][$index])) {
            return $dataOld[$table][$index];
        }

        // Need query from db
        $rs = $this->db->getByKey(
            $table,
            $keyValueArray,
            $columnArray,
            $keyArray
        );

        // If row only have one column, convert back to array
        $rs = is_null($rs) ? $rs : (array)$rs;

        return $rs;
    }


    /**
     * Reset code, message and flag
     *
     * @return  DbDiff
     */
    public function reset()
    {
        $this->rowCount = 0;
        $this->executeStatus = 0;
        $this->diff = [];

        return $this;
    }


    /**
     * Rollback committed diff result
     *
     * @return  DbDiff
     */
    public function rollback()
    {
        if (empty($this->diff)) {
            throw new \Exception('No diff data');
        }

        if ($this->isRollbacked()) {
            throw new \Exception(
                'Rollbacked DbDiff can\'t rollback again'
            );
        }

        $sqlArray = $this->generateRollbackSql();

        $this->db->BeginTrans();

        try {
            foreach ((array)$sqlArray as $sql) {
                $this->db->execute($sql);

                if (0 != $this->db->getErrorCode()) {
                    throw new \Exception($this->db->getErrorMessage());
                }
            }

            $this->db->CommitTrans();
            // Rollback operate doesn't change $rowCount
            $this->executeStatus = -100;

        } catch (\Exception $e) {
            $this->db->RollbackTrans();

            throw new \Exception($e->getMessage());
        }

        return $this;
    }
}
