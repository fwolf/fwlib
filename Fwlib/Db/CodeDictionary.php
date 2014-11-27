<?php
namespace Fwlib\Db;

use Fwlib\Bridge\Adodb;

/**
 * Code dictionary manager
 *
 * Eg: code-name table in db.
 *
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
 *
 * There are 2 way to initialize a code dictionary:
 *
 * - Use set method for property and dict data
 * - Inherit to a child class and set in property define
 *
 * These 2 way can mixed in use. If dict data is defined and not index by
 * primary key, a method will be called in constructor to fix it. This method
 * also change column value array to associate array index by column name, so
 * the dict array define are as simple as param of set().
 *
 * @copyright   Copyright 2011-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class CodeDictionary
{
    /**
     * Columns name, should not be empty
     *
     * @var array
     */
    protected $columns = array('code', 'title');

    /**
     * Dictionary data array
     *
     * @var array
     */
    protected $dictionary = array();

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
     * Constructor
     */
    public function __construct()
    {
        // $dictionary is never used now, need not do reset() on it
        if (!empty($this->dictionary) && 0 === key($this->dictionary)) {
            $this->fixDictionaryIndex();
        }
    }


    /**
     * Fix dictionary array index
     *
     * Use primary key value as index of first dimention, and column name as
     * index of second dimention(column value array).
     */
    protected function fixDictionaryIndex()
    {
        $dictionary = $this->dictionary;
        $this->dictionary = array();

        $this->set($dictionary);
    }


    /**
     * Get value for given key
     *
     * If $columns is array, will use directly without parseColumns().
     *
     * Child class can simplify this method to improve speed by avoid parse
     * columns, get columns data by index.
     *
     * @param   int|string|array    $key
     * @param   string|array        $columns
     * @return  int|string|array
     */
    public function get($key, $columns = '')
    {
        if (!isset($this->dictionary[$key])) {
            return null;
        }

        $resultColumns = is_array($columns) ? $columns
            : $this->parseColumns($columns);

        $result = array_intersect_key(
            $this->dictionary[$key],
            array_fill_keys($resultColumns, null)
        );

        // If only have 1 column
        if (1 == count($result)) {
            $result = array_shift($result);
        }

        return $result;
    }


    /**
     * Getter of $dictionary
     *
     * @return  array
     */
    public function getAll()
    {
        return $this->dictionary;
    }


    /**
     * Get value for given keys
     *
     * @param   array               $keys
     * @param   string|array        $columns
     * @return  array
     */
    public function getMultiple(array $keys, $columns = '')
    {
        if (empty($keys)) {
            return null;
        }

        $resultColumns = is_array($columns) ? $columns
            : $this->parseColumns($columns);

        $result = array();
        foreach ($keys as $singleKey) {
            $result[$singleKey] = $this->get($singleKey, $resultColumns);
        }

        return $result;
    }


    /**
     * Get SQL for write dictionary data to db
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
        if ($db->isDbMysql()) {
            $profile = $db->getProfile();
            $sql .= 'SET NAMES \''
                . str_replace('UTF-8', 'UTF8', strtoupper($profile['lang']))
                . '\'' . $db->getSqlDelimiter();
        }

        // Truncate part ?
        if ($withTruncate) {
            $sql .= $this->getSqlTruncate($db);
        }

        // Begin transaction
        $sql .= $db->getSqlTransBegin();

        // Data
        // INSERT INTO table (col1, col2) VALUES (val1, val2)[DELIMITER]
        foreach ($this->dictionary as $k => $row) {
            $valueList = array();
            foreach ($row as $key => $val) {
                $valueList[] = $db->quoteValue($this->table, $key, $val);
            }

            $sql .= 'INSERT INTO ' . $this->table
                . ' (' . implode(', ', $this->columns) . ')'
                . ' VALUES (' . implode(', ', $valueList) . ')'
                . $db->getSqlDelimiter();
        }

        // End transaction
        $sql .= $db->getSqlTransCommit();

        return $sql;
    }


    /**
     * Get SQL for write dictionary data to db, truncate part.
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
    protected function parseColumns($column = '')
    {
        $result = array();

        if ('*' == $column) {
            $result = $this->columns;

        } elseif (empty($column)) {
            // Assign first col not pk
            $columnWithoutPk = array_diff(
                $this->columns,
                (array)$this->primaryKey
            );
            $result = array(array_shift($columnWithoutPk));

        } else {
            // Find valid columns
            if (is_string($column)) {
                $column = explode(',', $column);
                array_walk($column, 'trim');
            }
            $result = array_intersect($column, $this->columns);
        }

        return $result;
    }


    /**
     * Search for data fit given condition
     *
     * $checkMethod is a function take $row as parameter and return boolean
     * value, can be anonymouse function or other callable.
     *
     * @param   callable        $condition
     * @param   string|array    $columns
     * @return  array
     */
    public function search($checkMethod, $columns = '*')
    {
        if (empty($this->dictionary)) {
            return array();
        }

        $resultColumns = is_array($columns) ? $columns
            : $this->parseColumns($columns);

        $results = array();
        foreach ($this->dictionary as $index => $row) {
            if ($checkMethod($row)) {
                $results[$index] = $this->get($index, $resultColumns);
            }
        }

        return $results;
    }


    /**
     * Set dictionary value
     *
     * @param   array   $data    1 or 2-dim data array.
     * @return  CodeDictionary
     */
    public function set(array $data)
    {
        if (empty($data)) {
            return $this;
        }

        if (empty($this->columns)) {
            throw new \Exception('Dictionary column not defined');
        }

        if (!in_array($this->primaryKey, $this->columns)) {
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
                    $this->columns,
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

            $this->dictionary[$primaryKeyValue] = $columnValueArray;
        }
        unset($row);

        return $this;
    }


    /**
     * Setter of $columns
     *
     * @param   array   $columns
     * @return  CodeDictionary
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;

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
