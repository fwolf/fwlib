<?php
namespace Fwlib\Db;

use Fwlib\Util\UtilAwareInterface;
use Fwlib\Util\UtilContainer;
use Fwlib\Util\UtilContainerInterface;

/**
 * SQL Generator
 *
 * Covered SQL clause:
 * {
 *  DELETE,
 *  FROM,
 *  GROUPBY,
 *  HAVING,
 *  INSERT,
 *  LIMIT,
 *  ORDERBY,
 *  SELECT,
 *  SET,
 *  UPDATE,
 *  VALUES,
 *  WHERE,
 * }
 *
 * When combime SQL parts, add space before clause keywords except DELETE,
 * SELECT, INSERT, UPDATE.
 *
 * Notice: call genClause() method directly works, but beware the result may
 * from mixed config from several set() before, use clear() to clear them, or
 * use getClause() method to avoid this.
 *
 * @copyright   Copyright 2003-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2003-08-25
 */
class SqlGenerator implements UtilAwareInterface
{
    /**
     * Db connection
     *
     * @var object
     */
    protected $db;

    /**
     * Param array index by SQL part
     *
     * @var array
     */
    protected $paramPart = array();

    /**
     * Generated SQL string part
     *
     * @var string
     */
    protected $sqlPart = array();

    /**
     * @var UtilContainer
     */
    protected $utilContainer = null;


    /**
     * Constructor
     *
     * @param   Fwlib\Bridge\Adodb  &$db    Db object
     */
    public function __construct(&$db)
    {
        if (!empty($db)) {
            $this->db = &$db;
        }
    }


    /**
     * Clear all or some parts set param
     *
     * @param   string  $part
     */
    public function clear($part = '')
    {
        // order by => ORDERBY
        $part = str_replace(' ', '', (strtoupper($part)));

        // Reset-able part
        $arPart = array(
            'DELETE',
            'FROM',
            'GROUPBY',
            'HAVING',
            'INSERT',
            'LIMIT',
            'ORDERBY',
            'SELECT',
            'SET',
            'UPDATE',
            'VALUES',
            'WHERE',
        );

        if (empty($part) || 'all' == $part) {
            $this->paramPart = array();
            $this->sqlPart = array();
        } else {
            // Reset part split by comma
            $arToClear = explode(',', $part);
            foreach ($arToClear as $s) {
                unset($this->paramPart[$s]);
                unset($this->sqlPart[$s]);
            }
        }
    }


    /**
     * Generate DELETE sql
     *
     * @param   array   $part
     * @return  string
     */
    public function genDelete($part = null)
    {
        $arrayUtil = $this->getUtil('Array');

        if (!empty($part) && is_array($part)) {
            // Using prefered parts in $part only
            $ar = &$part;
        } else {
            // Using all parts, by below sequence
            $ar = array('DELETE', 'WHERE', 'ORDERBY', 'LIMIT');
        }

        $sql = '';
        foreach ($ar as $v) {
            $sql .= $arrayUtil->getIdx($this->sqlPart, strtoupper($v), '');
        }

        return $sql;
    }


    /**
     * Generate INSERT sql
     *
     * @param   array   $part
     * @return  string
     */
    public function genInsert($part = array())
    {
        $arrayUtil = $this->getUtil('Array');

        if (!empty($part) && is_array($part)) {
            // Using prefered parts in $part only
            $ar = &$part;
        } else {
            // Using all parts, by below sequence
            $ar = array('INSERT', 'VALUES');
        }

        $sql = '';
        foreach ($ar as $v) {
            $sql .= $arrayUtil->getIdx($this->sqlPart, strtoupper($v), '');
        }

        return $sql;
    }


    /**
     * Generate SELECT sql
     *
     * @param   array   $part
     * @return  string
     */
    public function genSelect($part = array())
    {
        $arrayUtil = $this->getUtil('Array');

        if (!empty($part) && is_array($part)) {
            // Using prefered parts in $part only
            $ar = &$part;
        } else {
            // Using all parts, by below sequence
            $ar = array(
                'SELECT', 'FROM', 'WHERE', 'GROUPBY', 'HAVING',
                'ORDERBY', 'LIMIT'
            );
        }

        $sql = '';
        foreach ($ar as $v) {
            $sql .= $arrayUtil->getIdx($this->sqlPart, strtoupper($v), '');
        }

        return $sql;
    }


    /**
     * Generate SQL part, convert param array to string with seperator
     *
     * @param mixed     $param
     * @param string    $seperator  Should have space included
     * @return string
     */
    protected function genSqlArray($param, $seperator = ', ')
    {
        $sql = '';
        if (!empty($param) && is_array($param)) {
            // Key of param array is not used
            foreach ($param as $v) {
                $sql .= "$seperator$v";
            }
        } else {
            $sql .= "$seperator$param";
        }
        // Remove heading seperator and space
        $sql = substr($sql, strlen($seperator));

        return $sql;
    }


    /**
     * Generate SQL part, which param is array and need use AS in it
     *
     * @param mixed     $param  Param content, array or string,
     *                          {k: v} means 'k AS v',
     *                          [v] means 'v AS v',
     *                          remember to check $reverse param below.
     * @param boolean   $useAs  Sybase table alias can't use AS
     * @param boolean   $quote  AS column alias, need to be quoted(true),
     *                          AS table alias, need not to be quoted(false).
     * @param boolean   $reverse    Default true: {k: v} means 'v AS k'.
     *                              Because array key must be unique, and a
     *                              table can have many alias, so use unique
     *                              alias as key in param array. Also this
     *                              will make define code pretty and short,
     *                              expecially when mixed items with/without
     *                              alias.  Eg: {tbl1, a: tbl2}
     * @return string
     */
    protected function genSqlArrayAs(
        $param,
        $useAs = true,
        $quote = false,
        $reverse = true
    ) {
        $sql = '';
        if (!empty($param) && is_array($param)) {
            foreach ($param as $k => $v) {
                // If there are space in $v, it need to be quoted, so always
                // quote it except number (here will not have float value).
                if (is_int($k)) {
                    $sql .= ", $v";
                } else {
                    // table AS a
                    // table AS 'a'
                    $split = ($quote) ? "'" : '';
                    $as = ($useAs) ? 'AS ' : '';

                    // Reverse as is only usefull for particular db type
                    // @codeCoverageIgnoreStart
                    if ($reverse) {
                        $sql .= ", $v $as$split{$k}$split";
                    } else {
                        $sql .= ", $k $as$split{$v}$split";
                    }
                    // @codeCoverageIgnoreEnd
                }
            }
        } else {
            $sql .= ", $param";
        }


        $sql = substr($sql, 2);

        return $sql;
    }


    /**
     * Generate SQL part, SET subparse of UPDATE
     *
     * @param array $param  Array only, string will use original value.
     *                      Array($k=>$v) means 'SET $k = $v, ' in sql,
     * @return string
     */
    protected function genSqlArraySet($param)
    {
        $sql = '';
        if (!empty($param) && is_array($param)) {
            foreach ($param as $k => $v) {
                $sql .= ", $k = " . $this->genSqlQuote($this->paramPart['UPDATE'], $k, $v);
            }
            $sql = ' SET ' . substr($sql, 2);
        } else {
            // String param, add 'SET ' if user fogot
            if ('SET ' != substr(strtoupper(trim($param)), 0, 4)) {
                $sql .= ' SET ';
            }
            $sql .= $param;
        }

        return $sql;
    }


    /**
     * Generate SQL part, VALUES subparse of INSERT
     *
     * @param array $param  Array only, string will use original value.
     *                      Array($k=>$v) means '($k) VALUES ($v)' in sql.
     * @return string
     */
    protected function genSqlArrayValues($param)
    {
        $sql = '';
        if (!empty($param) && is_array($param)) {
            $sql1 = '';
            $sql2 = '';
            foreach ($param as $k => $v) {
                $sql1 .= ', ' . $k;
                $sql2 .= ', '
                    . $this->genSqlQuote($this->paramPart['INSERT'], $k, $v);
            }
            $sql1 = substr($sql1, 2);
            $sql2 = substr($sql2, 2);
            $sql .= '(' . $sql1 . ') VALUES (' . $sql2 . ')';
        } else {
            $sql = $param;
        }

        return $sql;
    }


    /**
     * Smart quote value in sql, by check columns type
     *
     * @param   string  $tbl
     * @param   string  $col
     * @param   mixed   $val
     * @return  string
     */
    protected function genSqlQuote($tbl, $col, $val)
    {
        return $this->db->quoteValue($tbl, $col, $val);
    }


    /**
     * Generate UPDATE sql
     * @param   array   $part
     * @return  string
     */
    public function genUpdate($part = array())
    {
        $arrayUtil = $this->getUtil('Array');

        if (!empty($part) && is_array($part)) {
            // Using prefered parts in $part only
            $ar = &$part;
        } else {
            // Using all parts, by below sequence
            $ar = array('UPDATE', 'SET', 'WHERE', 'ORDERBY', 'LIMIT');
        }

        $sql = '';
        foreach ($ar as $v) {
            $sql .= $arrayUtil->getIdx($this->sqlPart, strtoupper($v), '');
        }

        return $sql;
    }


    /**
     * Get result SQL statement
     *
     * Notice: If $config include SELECT, UPDATE, INSERT, DELETE
     * simultaneously, system will select the first occurs by raw order.
     *
     * @param   array   $config     {SELECT: , FROM: ...}
     *                              If obmit, use $this->paramPart
     * @param   string  $part       SELECT/UPDATE ... etc
     * @return  string
     */
    public function get($config = array(), $part = '')
    {
        $part = strtoupper($part);
        $this->set($config);

        // Got real action
        if (empty($part)) {
            foreach ($this->paramPart as $key => $val) {
                // SELECT/UPDATE/INSERT/DELETE ? Use the 1st occured one
                if (in_array(
                    $key,
                    array('SELECT', 'UPDATE', 'INSERT', 'DELETE')
                )) {
                    $part = $key;
                    break;
                }
            }
        }

        // No part to do
        if (empty($part) || !isset($this->paramPart[$part])) {
            return '';
        }

        // Call seperate func to generate sql
        $part = ucfirst(strtolower($part));
        $sql = $this->{'gen' . $part}(array_keys($config));

        return $sql;
    }


    /**
     * Get DELETE sql only
     *
     * @param   array   $config
     * @return  string
     */
    public function getDelete($config = array())
    {
        return $this->get($config, 'DELETE');
    }


    /**
     * Get INSERT sql only
     *
     * @param   array   $config
     * @return  string
     */
    public function getInsert($config = array())
    {
        return $this->get($config, 'INSERT');
    }


    /**
     * Get SQL statement for PREPARE usage
     *
     * Used need replace actual value with Adodb::param(col), to generate sql
     * use placeholder (? or :name by db type), this method will auto remove
     * quote.
     *
     * When execute a prepared SQL, db system will auto add quote, but it
     * depends on type of value, NOT type of db column.
     *
     * Known replaceQuote list:
     * mssql: double single-quote
     * mysql: backslash-quote
     *
     * @param   array   $param
     * @return  string
     */
    public function getPrepared($param = array())
    {
        $sql = $this->get($param);

        // @codeCoverageIgnoreStart
        // Remove duplicate ' in sql add by SqlGenerator,
        if ("''" == $this->db->replaceQuote) {
            $quote = "'";
        } else {
            $quote = $this->db->replaceQuote;
        }
        // @codeCoverageIgnoreEnd

        // Remove quote
        $sql = preg_replace(
            "/([\s,\(]){$quote}([\?\:\w\-_]+){$quote}([\s,\)])/i",
            "$1$2$3",
            $sql
        );

        return $sql;
    }


    /**
     * Get SELECT sql only
     *
     * @param   array   $config
     * @return  string
     */
    public function getSelect($config = array())
    {
        return $this->get($config, 'SELECT');
    }


    /**
     * Get UPDATE sql only
     *
     * @param   array   $config
     * @return  string
     */
    public function getUpdate($config = array())
    {
        return $this->get($config, 'UPDATE');
    }


    /**
     * Get util instance
     *
     * Same with Fwlib\Util\AbstractUtilAware::getUtil()
     *
     * @param   string  $name
     * @return  object  Util instance
     */
    protected function getUtil($name)
    {
        if (is_null($this->utilContainer)) {
            $this->setUtilContainer(null);
        }

        return $this->utilContainer->get($name);
    }


    /**
     * Set param
     *
     * Un-recoginized clause is ignored.
     *
     * @param   array   &$config
     * @return  string
     */
    public function set(&$config)
    {
        if (empty($config) || !is_array($config)) {
            return '';
        }

        // Global clause order, will sort by this.
        $clauseOrder = array(
            'SELECT', 'DELETE', 'INSERT', 'UPDATE',
            'VALUES', 'FROM', 'SET',
            'WHERE', 'GROUPBY', 'HAVING', 'ORDERBY', 'LIMIT'
        );

        // Re-order sql part
        $ar = array();
        $config = array_change_key_case($config, CASE_UPPER);
        foreach ($clauseOrder as $clause) {
            if (isset($config[$clause])) {
                $ar[$clause] = &$config[$clause];
            }
        }
        // Write data back to config
        $config = $ar;

        foreach ($config as $part => $param) {
            // Write config to param array
            $part = ucfirst(strtolower($part));
            $this->{"Set$part"}($param);
        }
    }


    /**
     * Set and treat DELETE param
     *
     * @param   mixed   $param
     * @return  string
     */
    public function setDelete($param)
    {
        $this->paramPart['DELETE'] = $param;

        $this->sqlPart['DELETE'] = 'DELETE FROM ' . $param;

        return $this->sqlPart['DELETE'];
    }


    /**
     * Set and treat FROM param
     *
     * @param   mixed   $param
     * @return  string
     */
    public function setFrom($param)
    {
        $this->paramPart['FROM'] = $param;

        // In 'FROM tbl AS alias' clause, no space allowed in alias, so need
        // not add quote to it.
        $this->sqlPart['FROM'] = ' FROM '
            . $this->genSqlArrayAs($param, false, false, true);

        return $this->sqlPart['FROM'];
    }


    /**
     * Set and treat GROUP BY param
     *
     * @param   mixed   $param
     * @return  string
     */
    public function setGroupby($param)
    {
        $this->paramPart['GROUPBY'] = $param;

        $this->sqlPart['GROUPBY'] = ' GROUP BY ' . $this->genSqlArray($param);

        return $this->sqlPart['GROUPBY'];
    }


    /**
     * Set and treat HAVING param
     *
     * @param   mixed   $param
     * @return  string
     */
    public function setHaving($param)
    {
        $this->paramPart['HAVING'] = $param;

        // Add '(' to defend sql injection
        $this->sqlPart['HAVING'] = ' HAVING ('
            . $this->genSqlArray($param, ') AND (') . ')';

        return $this->sqlPart['HAVING'];
    }


    /**
     * Set and treat INSERT param
     *
     * @param   mixed   $param
     * @return  string
     */
    public function setInsert($param)
    {
        $this->paramPart['INSERT'] = $param;

        $this->sqlPart['INSERT'] = 'INSERT INTO ' . $param;

        // Retrieve table schema, so VALUES can detimine how to quote
        $this->db->getMetaColumn($param);

        return $this->sqlPart['INSERT'];
    }


    /**
     * Set and treat LIMIT param
     *
     * @param   mixed   $param
     * @return  string
     */
    public function setLimit($param)
    {
        // @codeCoverageIgnoreStart
        if ($this->db->isDbSybase()) {
            // Sybase does not support LIMIT clause
            $this->paramPart['LIMIT'] = '';
            $this->sqlPart['LIMIT'] = '';

        } else {
            $this->paramPart['LIMIT'] = $param;
            $this->sqlPart['LIMIT'] = ' LIMIT ' . $this->genSqlArray($param);
        }
        // @codeCoverageIgnoreEnd

        return $this->sqlPart['LIMIT'];
    }


    /**
     * Set and treat ORDER BY param
     *
     * @param   mixed   $param
     * @return  string
     */
    public function setOrderby($param)
    {
        $this->paramPart['ORDERBY'] = $param;

        $this->sqlPart['ORDERBY'] = ' ORDER BY ' . $this->genSqlArray($param);

        return $this->sqlPart['ORDERBY'];
    }


    /**
     * Set and treat SELECT param
     *
     * @param   mixed   $param
     * @return  string
     */
    public function setSelect($param)
    {
        $this->paramPart['SELECT'] = $param;

        $this->sqlPart['SELECT'] = 'SELECT ' . $this->genSqlArrayAs($param, true, true, true);

        return $this->sqlPart['SELECT'];
    }


    /**
     * Set and treat SET param
     *
     * @param   mixed   $param
     * @return  string
     */
    public function setSet($param)
    {
        $this->paramPart['SET'] = $param;

        // For UPDATE only, INSERT uses VALUES
        // User give param array(col => value)
        $this->sqlPart['SET'] = $this->genSqlArraySet($param);

        return $this->sqlPart['SET'];
    }


    /**
     * Set and treat UPDATE param
     *
     * @param   mixed   $param
     * @return  string
     */
    public function setUpdate($param)
    {
        $this->paramPart['UPDATE'] = $param;

        $this->sqlPart['UPDATE'] = 'UPDATE ' . $param;

        // Retrieve table schema, so SET can detimine how to quote
        $this->db->getMetaColumn($param);

        return $this->sqlPart['UPDATE'];
    }


    /**
     * Set and treat VALUES param
     *
     * @param   mixed   $param
     * @return  string
     */
    public function setValues($param)
    {
        $this->paramPart['VALUES'] = $param;

        // For INSERT only, UPDATE uses SET
        // User give param array(col => value)
        $this->sqlPart['VALUES'] = $this->genSqlArrayValues($param);

        return $this->sqlPart['VALUES'];
    }


    /**
     * Set and treat WHERE param
     *
     * @param   mixed   $param
     * @return  string
     */
    public function setWhere($param)
    {
        $this->paramPart['WHERE'] = $param;

        // Add '(' to defend sql injection
        $this->sqlPart['WHERE'] = ' WHERE ('
            . $this->genSqlArray($param, ') AND (') . ')';

        return $this->sqlPart['WHERE'];
    }


    /**
     * Setter of UtilContainer
     *
     * @param   UtilContainerInterface  $utilContainer
     * @return  SqlGenerator
     */
    public function setUtilContainer(
        UtilContainerInterface $utilContainer = null
    ) {
        if (is_null($utilContainer)) {
            $this->utilContainer = UtilContainer::getInstance();
        } else {
            $this->utilContainer = $utilContainer;
        }

        return $this;
    }
}
