<?php
namespace Fwlib\Db;

use Fwlib\Bridge\Adodb;

/**
 * Code dictionary manager
 *
 * Eg: code-name table in db.
 *
 * The primary key can only contain ONE column, its used as key for $dict.
 * Single primary key should fit most need, or your data are possibly not code
 * dictionary.
 *
 * To support composite primary key, there can extend this class with a
 * generateDictIndex() method, the dict data array will be generated from all
 * primark key column value. In this scenario it is hard for get() and set()
 * method to recoginize array param is key of many rows or primary key array,
 * so more complicated work to do, maybe not suit for code dictionary.
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
     * For use primary key as array index, and avoid write it again in value
     * array, it should use set() in constructor to initialize dict data.
     *
     * @var array
     */
    protected $dict = array();

    /**
     * Primary key column name
     *
     * Privary key column is used to get or search, MUST exist in $column.
     *
     * @var string
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
     * Get value for given keys
     *
     * @param   int|string|array    $key
     * @param   string|array        $column
     * @return  int|string|array
     */
    public function get($key, $column = '')
    {
        if (empty($key)) {
            return null;
        }

        $resultColumn = $this->parseColumn($column);

        $result = array();
        foreach ((array)$key as $index) {
            if (isset($this->dict[$index])) {
                $result[$index] = $this->getColumnData($index, $resultColumn);

            } else {
                $result[$index] = null;
            }
        }

        // If only have 1 row
        if (1 == count($result)) {
            $result = array_shift($result);

            // If only have 1 column
            if (1 == count($result)) {
                $result = array_shift($result);
            }
        }

        return $result;
    }


    /**
     * Getter of $dict
     *
     * @return  array
     */
    public function getAll()
    {
        return $this->dict;
    }


    /**
     * Get data of columns by given dict index
     *
     * @param   int|string  $index
     * @param   array       $column
     * @return  array
     */
    protected function getColumnData($index, array $column)
    {
        return array_intersect_key(
            $this->dict[$index],
            array_fill_keys($column, null)
        );
    }


    /**
     * Get SQL for write dict data to db
     *
     * @param   Adodb   $db
     * @param   boolean $withTruncate
     * @return  string
     */
    public function getSql(Adodb $db, $withTruncate = true)
    {
        if (empty($this->table)) {
            return '';
        }

        if (!$db->isConnected()) {
            throw new \Exception('Database not connected');
        }


        // Result sql
        $sql = '';

        // Mysql set names
        // @codeCoverageIgnoreStart
        if ($db->isDbMysql()) {
            $profile = $db->getProfile();
            $sql .= 'SET NAMES \''
                . str_replace('UTF-8', 'UTF8', strtoupper($profile['lang']))
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
        foreach ($this->dict as $k => $row) {
            $valueList = array();
            foreach ($row as $key => $val) {
                $valueList[] = $db->quoteValue($this->table, $key, $val);
            }

            $sql .= 'INSERT INTO ' . $this->table
                . ' (' . implode(', ', $this->column) . ')'
                . ' VALUES (' . implode(', ', $valueList) . ')'
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
     * Parse columns you want to query
     *
     * If $column not assigned, assign as first col which is not primary key.
     *
     * Use '*' for all columns.
     *
     * @param   string|array    $column
     * @return  array
     */
    protected function parseColumn($column = '')
    {
        $result = array();

        if ('*' == $column) {
            $result = $this->column;

        } elseif (empty($column)) {
            // Assign first col not pk
            $columnWithoutPk = array_diff(
                $this->column,
                (array)$this->primaryKey
            );
            $result = array(array_shift($columnWithoutPk));

        } else {
            // Find valid columns
            if (is_string($column)) {
                $column = explode(',', $column);
                array_walk($column, 'trim');
            }
            $result = array_intersect($column, $this->column);
        }

        return $result;
    }


    /**
     * Search for data fit given condition
     *
     * In condition, use {column} and native php syntax, delimiter can change
     * use setDelimiter().
     *
     * @param   string  $condition
     * @param   string  $column
     * @return  array
     */
    public function search($condition = '', $column = '*')
    {
        if (empty($condition) || empty($this->dict)) {
            return array();
        }

        $columnWithDelimiter = array();
        foreach ($this->column as $v) {
            $columnWithDelimiter[] = $this->delimiterLeft . $v .
                $this->delimiterRight;
        }

        $resultColumn = $this->parseColumn($column);

        $result = array();
        $condition = "return ($condition);";
        foreach ($this->dict as $index => &$row) {
            $conditionResult =
                str_replace($columnWithDelimiter, $row, $condition);
            eval($conditionResult);

            if (eval($conditionResult)) {
                $result[$index] = $this->getColumnData($index, $resultColumn);
            }
        }
        unset($row);

        return $result;
    }


    /**
     * Set dict value
     *
     * @param   array   $data    1 or 2-dim data array.
     * @return  CodeDictionary
     */
    public function set(array $data)
    {
        if (empty($data)) {
            return $this;
        }

        if (empty($this->column)) {
            throw new \Exception('Dictionary column not defined');
        }

        if (!in_array($this->primaryKey, $this->column)) {
            throw new \Exception(
                'Defined columns didn\'nt include primary key'
            );
        }

        // Convert 1-dim to 2-dim
        if (!is_array(current($data))) {
            $data = array($data);
        }


        foreach ($data as &$row) {
            try {
                $columnValueArray = array_combine(
                    $this->column,
                    $row
                );
            } catch (\Exception $e) {
                throw new \Exception(
                    'Given data didn\'t contain all columns'
                );
            }

            $primaryKeyValue = $columnValueArray[$this->primaryKey];

            if (empty($primaryKeyValue)) {
                throw new \Exception(
                    'Primary key value is empty or not set'
                );
            }

            $this->dict[$primaryKeyValue] = $columnValueArray;
        }
        unset($row);

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
