<?php
namespace Fwlib\Db;

use Fwlib\Base\AbstractAutoNewConfig;

/**
 * Code dictionary manager
 *
 * Eg: code-name table in db.
 *
 * @package     Fwlib\Db
 * @copyright   Copyright 2011-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2011-07-15
 */
class CodeDictionary extends AbstractAutoNewConfig
{
    /**
     * Dictionary data array
     *
     * @var array
     */
    protected $dict = array();


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->init();

        $column = $this->config['column'];
        if (empty($column)) {
            // @codeCoverageIgnoreStart
            throw new \Exception('Dict col not defined.');
            // @codeCoverageIgnoreEnd
        }
    }


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
        $colAll = $this->config['column'];
        $arCol = array();

        if ('*' == $col) {
            $arCol = $colAll;

        } elseif (empty($col)) {
            // Assign first col not pk
            $colWithoutPk = array_diff($colAll, (array)$this->config['pk']);
            $arCol = array(array_shift($colWithoutPk));

        } else {
            // Find valid cols
            if (is_string($col)) {
                $col = explode(',', $col);
                array_walk($col, 'trim');
            }
            $arCol = array_intersect($col, $colAll);
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
        if (empty($db) || !$db->isConnected()) {
            trigger_error('Db empty or not connected.', E_USER_WARNING);
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }
        $table = $this->config['table'];
        if (empty($table)) {
            trigger_error('Dict table not set.', E_USER_WARNING);
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
        $dictTable = $this->config['table'];
        $colList = $this->config['column'];
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
        $sql = 'TRUNCATE TABLE ' . $this->config['table']
            . $db->getSqlDelimiter();

        if (!$db->isDbSybase()) {
            $sql = $db->getSqlTransBegin() . $sql . $db->getSqlTransCommit();
        }

        return $sql;
    }


    /**
     * Init config, set data structure
     *
     * Usually override by sub class.
     *
     * @return  object
     */
    public function init()
    {
        // Col is array of string column name
        $this->setConfig('column', array('code', 'title'));
        // PK is string column name for single, or array of string for multi.
        // PK MUST in Col list.
        $this->setConfig('pk', 'code');
        // Table name is used to generate SQL
        $this->setConfig('table', 'code_dictionary');

        // Delimiter in get list condition
        $this->setConfig('delimiter-left', '{');
        $this->setConfig('delimiter-right', '}');
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
        $delimiterLeft = $this->config['delimiter-left'];
        $delimiterRight = $this->config['delimiter-right'];
        foreach ($this->config['column'] as $v) {
            $colWithDelimiter[] = $delimiterLeft . $v . $delimiterRight;
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
        $column = $this->config['column'];
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


        $pk = $this->config['pk'];
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
}
