<?php
namespace Fwlib\Db;

use Fwlib\Bridge\Adodb;
use Fwlib\Util\UtilContainerAwareTrait;

/**
 * Db data backup tool, result is pure SQL
 *
 * Test under sybase 11.92 ok.
 *
 * @copyright   Copyright 2006-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class DbDataExport
{
    use AdodbAwareTrait {
        setDb as setDbInstance;
    }
    use UtilContainerAwareTrait;


    /**
     * Columns will be exclude from export
     *
     * Check by column name, for every table to export.
     *
     * @var array
     */
    public $columnExclude = [];

    /**
     * Where to save exported sql files
     *
     * @var string
     */
    public $exportPath = '/tmp/DbDataExport';

    /**
     * SQL delimiter including EOL, set when db connected
     *
     * @var string
     */
    protected $lineEnding = '';

    /**
     * Log file
     *
     * Filename only, in path $this->exportPath.
     *
     * @var string
     * @see $exportPath
     */
    public $logFile = 'DbDataExport.log';

    /**
     * Max rows per exported file
     *
     * @var int
     */
    public $maxRowPerFile = 10000;

    /**
     * Tables will be backup
     *
     * Include needed, exclude no needed, this is result.
     *
     * @var array
     */
    protected $table = [];

    /**
     * Tables to be exclude from export
     *
     * @var array
     */
    public $tableExclude = [];

    /**
     * Table need to be group by some cols when export
     *
     * Usually used when table contains too much rows, so we split export
     * query by this Groupby column's value.
     *
     * @var array
     */
    public $tableGroupby = [];

    /**
     * Tables to be include in export
     *
     * If set, will only export tables in this list, ignore $tableExclude.
     *
     * @var array
     */
    public $tableInclude = [];

    /**
     * Print log message
     *
     * @var boolean
     */
    public $verbose = false;

    /**
     * Export with TRUNCATE statement
     *
     * @var boolean
     */
    public $withTruncate = true;


    /**
     * Convert groupby rules to where sql clauses
     *
     * Export by groupby will make it hard to control rows number in a file,
     * so we retrieve data from db by groupby rules, and convert to where
     * clause, to use in export query.
     *
     * @param   string  $tbl
     * @return  array
     */
    protected function convertGroupby2Where($tbl)
    {
        $arWhere = [];

        if (!empty($this->tableGroupby[$tbl])) {
            $groupby = $this->tableGroupby[$tbl];
            $sql = "SELECT DISTINCT $groupby FROM $tbl";
            $rs = $this->getDb()->Execute($sql);

            // Convert every row to where sql
            $cols = explode(',', $groupby);
            while (!empty($rs) && !$rs->EOF && !empty($cols)) {
                $sql = ' WHERE 1 = 1 ';
                foreach ($cols as $c) {
                    $val = $this->parseField($tbl, $rs, $c);
                    $sql .= " AND $c = $val ";
                }
                array_push($arWhere, $sql);

                $rs->MoveNext();
            }
        }

        return $arWhere;
    }


    /**
     * Convert ADOdb RecordSet to SQL text
     *
     * @param   object  $rs
     * @param   string  $tbl
     * @param   array   $cols
     * @return  string
     */
    protected function convertRs2Sql(&$rs, $tbl, $cols = [])
    {
        $sql = '';

        while (!$rs->EOF) {
            // Insert sql begin
            $sql .= "INSERT INTO $tbl (" . implode(', ', $cols) . ") VALUES (\n";

            // Fields data
            $ar = [];
            foreach ($cols as $c) {
                $val = $this->parseField($tbl, $rs, $c);
                array_push($ar, $val);
            }
            $sql .= '    ' . implode(', ', $ar) . "\n";

            // Insert sql end
            $sql .= ')' . $this->lineEnding;

            $rs->MoveNext();
        }

        return $sql;
    }


    /**
     * Export db, main entrance
     */
    public function export()
    {
        // New log file
        $logFile = $this->exportPath . '/'  . $this->logFile;
        file_put_contents($logFile, '');

        $profileString = $this->getDb()->getProfileString(':');
        $this->log("Export for db $profileString, ", false);

        $this->getTable();
        $this->log('Total ' . count($this->table) . ' tables.');
        $this->log();

        foreach ($this->table as $tbl) {
            $this->exportTable($tbl);
        }
    }


    /**
     * Export single table
     *
     * @param   string  $tbl
     */
    protected function exportTable($tbl)
    {
        $this->log('[' . $tbl . '] ', false);

        $db = $this->getDb();

        $cols = $this->getColumn($tbl);

        $rowCount = $db->getRowCount($tbl);
        $this->log('Total ' . number_format($rowCount) . ' rows.');

        // SQL header
        $sql = '';
        if ($this->withTruncate) {
            $sql .= 'TRUNCATE TABLE ' . $tbl . $this->lineEnding;
        }
        // @codeCoverageIgnoreStart
        if ($this->needIdentityInsert()) {
            $sql .= 'set identity_insert ' . $tbl . ' on' . $this->lineEnding;
        }
        // @codeCoverageIgnoreEnd


        // Prepare
        $sqlOffset = 0;
        $doneRows = 0;
        $doneBytes = 0;
        // May change later with splitted number
        $exportFile = $this->exportPath . "/$tbl.sql";

        // Groupby rules is converted to where clauses
        $arWhere = $this->convertGroupby2Where($tbl);

        while ($sqlOffset < $rowCount) {
            $this->log('.', false);

            // Execute sql
            // When use groupby and $arWhere is empty, the loop should end.
            if (!empty($arWhere)) {
                $where = array_shift($arWhere);
                $sqlSelect = "SELECT * FROM $tbl $where";
                $rs = $db->Execute($sqlSelect);

            } else {
                $sqlSelect = "SELECT * FROM $tbl";
                $sqlSelect = $db->convertEncodingSql($sqlSelect);
                $rs = $db->SelectLimit(
                    $sqlSelect,
                    $this->maxRowPerFile,
                    $sqlOffset
                );
            }
            $rsRows = $rs->RecordCount();
            if (0 != $db->ErrorNo()) {
                // @codeCoverageIgnoreStart
                $this->log("\n" . $db->ErrorMsg());
                break;
                // @codeCoverageIgnoreEnd
            } else {
                $sql .= $this->convertRs2Sql($rs, $tbl, $cols);
                $doneRows += $rsRows;
                // Move below line to after file write near unset(), will make
                // sql file number start from 0.
                $sqlOffset += $rsRows;
            }

            // Save this step to file
            $sql = $db->convertEncodingResult($sql);
            // Save to separated file, first check about how many files will
            // be used. File number start from 1.
            if ($rowCount > $this->maxRowPerFile) {
                $i = strlen(strval(ceil($rowCount / $this->maxRowPerFile)));
                $s = strval(ceil($sqlOffset / $this->maxRowPerFile));
                $s = substr(str_repeat('0', $i) . $s, $i * -1) . '.';
                $exportFile = $this->exportPath . '/' . $tbl . '.' . $s . 'sql';
                file_put_contents($exportFile, $sql);
            } else {
                $s = '';
                file_put_contents($exportFile, $sql, FILE_APPEND);
            }

            // Prepare for next loop
            $doneBytes += strlen($sql);
            unset($sql);
            $sql = '';
            unset($rs);
        }

        // End line of '.'
        if (0 < $rowCount) {
            $this->log();
        }

        // SQL tail
        // @codeCoverageIgnoreStart
        if ($this->needIdentityInsert()) {
            $sql .= 'set identity_insert ' . $tbl . ' off' . $this->lineEnding;
        }
        // @codeCoverageIgnoreEnd

        file_put_contents($exportFile, $sql, FILE_APPEND);

        $this->log(
            'Saved ' . number_format($doneRows) . ' rows, Total ' .
            number_format($doneBytes) . ' bytes.'
        );
        $this->log();
    }


    /**
     * Get columns of a table, remove $columnExclude column
     *
     * @param   string  $tbl
     * @return  array
     */
    protected function getColumn($tbl)
    {
        $colsMeta = $this->getDb()->MetaColumns($tbl);
        // @codeCoverageIgnoreStart
        if (empty($colsMeta)) {
            return [];
        }
        // @codeCoverageIgnoreEnd

        $cols = [];
        foreach ($colsMeta as $c) {
            if (!in_array($c->name, $this->columnExclude)) {
                array_push($cols, $c->name);
            }
        }

        return $cols;
    }


    /**
     * Retrieve table list from db
     *
     * @see $table
     */
    protected function getTable()
    {
        if (!empty($this->tableInclude)) {
            $this->table = $this->tableInclude;
        } else {
            $this->table = $this->getDb()->MetaTables('TABLES');
        }

        // Compute exclude
        foreach ($this->tableExclude as $tbl) {
            $idx = array_search($tbl, $this->table);
            if (false !== $idx) {
                unset($this->table[$idx]);
            }
        }
    }


    /**
     * Log message to file and print
     *
     * Will append "\n" to $msg if not end with it.
     *
     * @param   string  $msg
     * @param   boolean $newline
     */
    public function log($msg = '', $newline = true)
    {
        if ($newline) {
            $msg = $this->getUtilContainer()->getEnv()->ecl($msg, true);
        }

        $logFile = $this->exportPath . '/'  . $this->logFile;
        file_put_contents($logFile, $msg, FILE_APPEND);

        // @codeCoverageIgnoreStart
        if ($this->verbose) {
            echo $msg;
        }
        // @codeCoverageIgnoreEnd
    }


    /**
     * Determine if current db driver need set identity_insert tbl on/off
     *
     * @return  boolean
     */
    protected function needIdentityInsert()
    {
        $need = ['mssql', 'sybase', 'sybase_ase'];

        return in_array($this->getDb()->profile['type'], $need);
    }


    /**
     * Parse a field from RecordSet for use in SQL
     *
     * @param   string  $tbl
     * @param   object  $rs
     * @param   string  $field
     * @return  string
     */
    protected function parseField($tbl, &$rs, $field)
    {
        $val = $rs->Fields($field);
        if (is_null($val)) {
            $val = 'NULL';
        } else {
            $val = $this->getDb()->quoteValue($tbl, $field, $val);
        }

        return $val;
    }


    /**
     * @param   Adodb   $db
     * @return  static
     */
    public function setDb(Adodb $db)
    {
        $this->lineEnding = $db->getSqlDelimiter();

        return $this->setDbInstance($db);
    }


    /**
     * Set where to save sql files exported
     *
     * If directory does not exists, create it.
     *
     * @param   string  $path
     * @return  boolean
     */
    public function setExportPath($path)
    {
        $this->exportPath = $path;

        // Check and create
        if (file_exists($path) && !is_dir($path)) {
            $this->getUtilContainer()->getEnv()
                ->ecl('Export target path is a file.');
            return false;

        } elseif (!file_exists($path)) {
            return mkdir($path, 0700, true);
        }

        return true;
    }


    /**
     * Set tables will not be exported
     *
     * @param   array   $ar
     */
    public function setTableExclude($ar)
    {
        // Same with setTableInclude()
        // @codeCoverageIgnoreStart

        if (is_array($ar)) {
            $this->tableExclude = $ar;
        } else {
            $ar = explode(',', $ar);
            $this->tableExclude = [];
            foreach ($ar as $tbl) {
                $tbl = trim($tbl);
                if (!empty($tbl)) {
                    $this->tableExclude[] = $tbl;
                }
            }
        }

        // @codeCoverageIgnoreEnd
    }


    /**
     * Set table group by rules when export
     *
     * If given cols is empty, it will remove tbl from $tableGroupby.
     *
     * Multi cols split by ','.
     *
     * @param   string  $tbl
     * @param   string  $cols
     */
    public function setTableGroupby($tbl, $cols)
    {
        if (empty($cols)) {
            unset($this->tableGroupby[$tbl]);
        } else {
            $this->tableGroupby[$tbl] = $cols;
        }
    }


    /**
     * Set tables will only be export
     *
     * @param   array   $ar
     */
    public function setTableInclude($ar)
    {
        if (is_array($ar)) {
            $this->tableInclude = $ar;
        } else {
            $ar = explode(',', $ar);
            $this->tableInclude = [];
            foreach ($ar as $tbl) {
                $tbl = trim($tbl);
                if (!empty($tbl)) {
                    $this->tableInclude[] = $tbl;
                }
            }
        }
    }
}
