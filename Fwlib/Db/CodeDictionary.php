<?php
namespace Fwlib\Db;


/**
 * Code dictionary manager
 *
 * Eg: code-name table in db.
 *
 * @package     Fwlib\Db
 * @copyright   Copyright 2011-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2011-07-15
 */
class CodeDictionary
{
    /**
     * Column name, should not be empty
     *
     * @var array
     */
    protected $column = array('code', 'title');

    /**
     * Left delimiter in search condition
     *
     * @var string
     */
    protected $delimiterLeft = '{';

    /**
     * Right delimiter in search condition
     *
     * @var string
     */
    protected $delimiterRight = '}';

    /**
     * Dictionary data array
     *
     * @var array
     */
    protected $dict = array();

    /**
     * Primary key column name or array
     *
     * Privary key column is used to get or search, MUST exist in $column.
     *
     * @var string|array
     */
    protected $primaryKey = 'code';

    /**
     * Code table name in db
     *
     * If table name is empty, getSql() will return empty.
     *
     * @var string
     */
    protected $table = 'code_dictionary';


    /**
     * Get relate value for given pk
     *
     * @param   mixed   $arPk   Array or string of pk
     * @param   mixed   $col    Array or string of cols for return
     * @return  mixed
     */
    public function get($arPk, $col = '')
    {
        if (empty($arPk)) {
            return null;
        }

        if (!is_array($arPk)) {
            $arPk = array($arPk);
        }

        $arCol = $this->getColumn($col);

        $ar = array();
        foreach ($arPk as $pk) {
            if (isset($this->dict[$pk])) {
                $ar[$pk] = $this->getColumnData($this->dict[$pk], $arCol);
            } else {
                $ar[$pk] = null;
            }
        }

        if (1 == count($ar)) {
            return array_shift($ar);
        } else {
            return $ar;
        }
    }


    /**
     * Get cols you want to query
     *
     * If $col not assigned, assign as first col which is not pk.
     *
     * Use '*' for all cols.
     *
     * @param   mixed   $col    Array or string of cols.
     * @return  mixed
     */
    protected function getColumn($col = '')
    {
        $arCol = array();

        if ('*' == $col) {
            $arCol = $this->column;

        } elseif (empty($col)) {
            // Assign first col not pk
            $colWithoutPk = array_diff($this->column, (array)$this->primaryKey);
            $arCol = array(array_shift($colWithoutPk));

        } else {
            // Find valid cols
            if (is_string($col)) {
                $col = explode(',', $col);
                array_walk($col, 'trim');
            }
            $arCol = array_intersect($col, $this->column);
        }

        if (1 == count($arCol)) {
            return array_shift($arCol);
        } else {
            return $arCol;
        }
    }


    /**
     * Get value from data array by assigned cols
     *
     * @param   array   $arData
     * @param   mixed   $col
     * @return  mixed
     */
    protected function getColumnData($arData, $col)
    {
        if (empty($arData) || empty($col)) {
            // This should not occur
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }
        if (!is_array($col)) {
            $col = array($col);
        }

        $ar = array();
        foreach ($col as $v) {
            if (isset($arData[$v])) {
                $ar[$v] = $arData[$v];
            }
        }

        if (1 == count($ar)) {
            return array_shift($ar);
        } else {
            return $ar;
        }
    }


    /**
     * Get SQL for write dict data to db
     *
     * @param   object  $db     Fwlib\Bridge\Adodb
     * @param   boolean $withTruncate
     * @return  string
     */
    public function getSql($db, $withTruncate = true)
    {
        if (empty($this->table)) {
            throw new \Exception('Table not set');
        }

        if (empty($db) || !$db->isConnected()) {
            trigger_error('Db empty or not connected.', E_USER_WARNING);
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }


        // Result sql
        $sql = '';

        // Mysql set names
        // @codeCoverageIgnoreStart
        if ($db->isDbMysql()) {
            $sql .= 'SET NAMES \''
                . str_replace('UTF-8', 'UTF8', strtoupper($db->profile['lang']))
                . '\'' . $db->getSqlDelimiter();
        }
        // @codeCoverageIgnoreEnd

        // Truncate part ?
        if ($withTruncate) {
            $sql .= $this->getSqlTruncate($db);
        }

        // Begin transaction
        $sql .= $db->getSqlTransBegin();

        // Data
        // INSERT INTO table (col1, col2) VALUES (val1, val2)[DELIMITER]
        $dictTable = $this->table;
        $colList = $this->column;
        foreach ((array)$this->dict as $k => $row) {
            $valList = array();
            foreach ($row as $key => $val) {
                $valList[] = $db->quoteValue($dictTable, $key, $val);
            }

            $sql .= 'INSERT INTO ' . $dictTable
                . ' (' . implode(', ', $colList) . ')'
                . ' VALUES (' . implode(', ', $valList) . ')'
                . $db->getSqlDelimiter();
        }

        // End transaction
        $sql .= $db->getSqlTransCommit();

        return $sql;
    }


    /**
     * Get SQL for write dict data to db, truncate part.
     *
     * @param   object  $db Fwlib\Bridge\Adodb
     * @return  string
     */
    public function getSqlTruncate($db)
    {
        $sql = 'TRUNCATE TABLE ' . $this->table
            . $db->getSqlDelimiter();

        if (!$db->isDbSybase()) {
            $sql = $db->getSqlTransBegin() . $sql . $db->getSqlTransCommit();
        }

        return $sql;
    }


    /**
     * Search for data fit given condition
     *
     * In condition, use {col} and native php syntax, delimiter can change use
     * setConfig().
     *
     * @param   string  $condition
     * @param   string  $col        Wanted cols.
     * @return  array   2-dim array of result.
     */
    public function search($condition = '', $col = '*')
    {
        if (empty($condition)) {
            return $this->dict;
        }
        if (empty($this->dict)) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }
        $col = $this->getColumn($col);

        $colWithDelimiter = array();
        foreach ($this->column as $v) {
            $colWithDelimiter[] = $this->delimiterLeft . $v .
                $this->delimiterRight;
        }

        // Loop check
        $result = array();
        $condition = "return ($condition);";
        foreach ($this->dict as $k => &$row) {
            $condition_t = str_replace($colWithDelimiter, $row, $condition);
            eval($condition_t);
            if (eval($condition_t)) {
                $result[$k] = $this->getColumnData($row, $col);
            }
        }
        unset($row);

        return $result;
    }


    /**
     * Set dict value
     *
     * @param   array   $data    1 or 2-dim data array.
     * @return  $this
     */
    public function set($data)
    {
        if (empty($data)) {
            trigger_error('Empty data given.', E_USER_NOTICE);
            // @codeCoverageIgnoreStart
            return $this;
            // @codeCoverageIgnoreEnd
        }
        $column = $this->column;
        if (empty($column)) {
            trigger_error('Dict column not defined.', E_USER_WARNING);
            // @codeCoverageIgnoreStart
            return $this;
            // @codeCoverageIgnoreEnd
        }

        // Convert 1-dim to 2-dim
        if (!is_array($data[array_rand($data)])) {
            $data = array($data);
        }


        $pk = $this->primaryKey;
        foreach ($data as $row) {
            $ar = array();
            foreach ($column as $col) {
                // @codeCoverageIgnoreStart
                if (empty($row)) {
                    trigger_error('Given data not fit all column.', E_USER_WARNING);
                } else {
                    $ar[$col] = array_shift($row);
                }
                // @codeCoverageIgnoreEnd
            }

            // Single pk as array index
            if (!empty($pk) && is_string($pk)) {
                if (!empty($ar[$pk])) {
                    $this->dict[$ar[$pk]] = $ar;
                } else {
                    // @codeCoverageIgnoreStart
                    trigger_error('Data not include dict pk.', E_USER_WARNING);
                    $this->dict[] = $ar;
                    // @codeCoverageIgnoreEnd
                }
            } else {
                // Multi pk or no pk
                $this->dict[] = $ar;
            }
        }

        return $this;
    }


    /**
     * Setter of $column
     *
     * @param   array   $column
     * @return  CodeDictionary
     */
    public function setColumn(array $column)
    {
        $this->column = $column;

        return $this;
    }


    /**
     * Setter of $delimiterLeft and $delimiterRight
     *
     * @param   string  $delimiterLeft
     * @param   string  $delimiterRight
     * @return  CodeDictionary
     */
    public function setDelimiter($delimiterLeft, $delimiterRight = null)
    {
        $this->delimiterLeft = $delimiterLeft;

        if (is_null($delimiterRight)) {
            $delimiterRight = $delimiterLeft;
        }

        $this->delimiterRight = $delimiterRight;

        return $this;
    }


    /**
     * Setter of $primaryKey
     *
     * @param   string|array    $primaryKey
     * @return  CodeDictionary
     */
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }


    /**
     * Setter of $table
     *
     * @param   string  $table
     * @return  CodeDictionary
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }
}
