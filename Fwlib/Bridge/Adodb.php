<?php
namespace Fwlib\Bridge;

use Fwlib\Db\SqlGenerator;
use Fwlib\Util\AbstractUtilAware;
use Fwlib\Util\StringUtil;

/**
 * Extended ADOdb class
 *
 * Diff with ADOdb official method of extend sub-class by db type, neither
 * direct extend ADOdb class, here use ADOdb instance as property only, and
 * use magic function __call __get __set to route ADOdb method to it.  The
 * opposite this way is no modification to RecordSet class.
 *
 *
 * Notice:
 *
 * ADOdb for sybase under Windows 2003, call Affected_Rows() will cause
 * process error.
 *
 *
 * Encoding convert:
 *
 * If charset of database and system are different, this class will try to do
 * encoding convert before query, see __call() for affected method.
 *
 * Encoding convert for query result will NOT automatic done, although we
 * provide a method convertEncodingRs() to do this manually.
 *
 * @package     Fwlib\Bridge
 * @copyright   Copyright 2008-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2008-04-08
 */
class Adodb extends AbstractUtilAware
{

    /**
     * PHP script charset
     *
     * Used to compare with db charset.
     *
     * This is charset of PHP script. Operation system has their default
     * charset, but text editor can specify other charset too.
     *
     * @var string
     */
    public $charsetPhp = 'UTF-8';

    /**
     * Real ADOdb connection object
     *
     * @var object
     */
    protected $conn = null;

    /**
     * Table schema
     *
     * {
     *  table: {
     *      col: {
     *          name: ts
     *          max_length: -1
     *          type: timestamp
     *          scale:
     *          not_null:
     *          primary_key:
     *          auto_increment:
     *          binary:
     *          unsigned:
     *          zerofill:
     *          has_default: 1
     *          default_value: CURRENT_TIMESTAMP
     *      }
     *  }
     * }
     *
     * Notice: col is ADOFieldObjct object, not array !
     *
     * @var array
     */
    public $metaColumn = array();

    /**
     * Table column name array, with upper case column name as index
     *
     * {
     *  COLUMN: column
     * }
     *
     * @var array
     */
    public $metaColumnName = array();

    /**
     * Primary key columns of table
     *
     * {
     *  tbl: colPk
     *  OR
     *  tbl: [pkCol1, pkCol2]
     * }
     *
     * @var array
     */
    public $metaPrimaryKey = array();

    /**
     * Db profile
     *
     * {host, user, pass, name, type, lang}
     *
     * @var array
     */
    public $profile = null;

    /**
     * Total query count
     *
     * @var int
     */
    public $queryCount = 0;

    /**
     * Sql generator object
     *
     * @var object
     */
    public $sqlGenerator;


    /**
     * constructor
     *
     * $dbprofile = {type:, host:, user:, pass:, name:, lang:,}
     * type: mysql/sybase_ase etc.
     * name: db name to select.
     * lang: db server charset.
     *
     * if $pathAdodb is empty, should load ADOdb through ClassLoader.
     *
     * @var param   array   $profile
     * @var param   string  $pathAdodb      Include path of original ADOdb
     */
    public function __construct($profile, $pathAdodb = '')
    {
        // Unset for auto new
        unset($this->sqlGenerator);

        // @codeCoverageIgnoreStart
        // Include ADOdb lib
        if (!empty($pathAdodb)) {
            require_once($pathAdodb);
        }
        // @codeCoverageIgnoreEnd

        // Trigger AutoLoader for ADOdb
        new \ADOFetchObj;

        $this->profile = $profile;
        $this->conn = ADONewConnection($profile['type']);
    }


    /**
     * Redirect method call to ADOdb
     *
     * @var string  $name   Method name
     * @var array   $arg    Method argument
     * @return  mixed
     */
    public function __call($name, $arg)
    {
        // Before call, convert $sql encoding first
        // Method list by ADOdb doc order

        if (in_array(
            $name,
            array(
                'Execute',
                'SelectLimit',
                'Prepare',
                'PrepareSP',
                'GetOne',
                'GetRow',
                'GetAll',
                'GetCol',
                'GetAssoc',
                'ExecuteCursor',
            )
        )) {
            // $sql is the 1st param
            $this->convertEncodingSql($arg[0]);
        } elseif (in_array(
            $name,
            array(
                'CacheExecute',
                'CacheSelectLimit',
                'CacheGetOne',
                'CacheGetRow',
                'CacheGetAll',
                'CacheGetCol',
                'CacheGetAssoc',
            )
        )) {
            // $sql is the 2nd param
            $this->convertEncodingSql($arg[1]);
        }

        // Count db query times, for all Adodb instance.
        // Use static var so multi Adodb object can be included in count.
        // Use standalone func for easy extend by sub class.
        // CacheXxx() method is not counted.
        if (in_array(
            $name,
            array(
                'Execute', 'SelectLimit', 'GetOne', 'GetRow', 'GetAll',
                'GetCol', 'GetAssoc', 'ExecuteCursor'
            )
        )) {
            $this->countQuery();
        }

        return call_user_func_array(array($this->conn, $name), $arg);
    }


    /**
     * Redirect property get to ADOdb
     *
     * @param   string    $name
     * @return  mixed
     */
    public function __get($name)
    {
        if ('sqlGenerator' == $name) {
            $this->sqlGenerator = $this->newInstanceSqlGenerator();
            return $this->sqlGenerator;
        } else {
            return $this->conn->$name;
        }
    }


    /**
     * Redirect property set to adodb
     *
     * @param string    $name
     * @param mixed     $val
     */
    public function __set($name, $val)
    {
        // For object need auto new in this class instead of $this->conn, need
        // check in __get() and __set() both. If only treat in __get(), the
        // assign will happen, but assign to $this->conn->property, next time
        // when it's used, will trigger __get() again, and do useless
        // newInstance() again.
        if ('sqlGenerator' == $name) {
            $this->$name = $val;
        } else {
            $this->conn->$name = $val;
        }
    }


    /**
     * Connect to db
     *
     * If db is mysql, will auto execute 'set names utf8'.
     *
     * @see $profile
     * @param   $forcenew         Force new connection
     * @return  boolean
     */
    public function connect($forcenew = false)
    {
        if (!$forcenew && $this->isConnected()) {
            return true;
        }


        // @codeCoverageIgnoreStart
        // Mysqli doesn't allow port in host, grab it out and set
        if ('mysqli' == strtolower($this->conn->databaseType)) {
            $ar = array();
            $i = preg_match('/:(\d+)$/', $this->profile['host'], $ar);
            if (0 < $i) {
                $this->conn->port = $ar[1];
                $this->profile['host'] =
                    preg_replace('/:(\d+)$/', '', $this->profile['host']);
            }
        }
        // @codeCoverageIgnoreEnd


        try {
            // Disable error display tempratory
            $iniDisplayErrors = ini_get('display_errors');
            ini_set('display_errors', '0');

            $rs = $this->conn->Connect(
                $this->profile['host'],
                $this->profile['user'],
                $this->profile['pass'],
                $this->profile['name']
            );

            // Recover original error display setting
            ini_set('display_errors', $iniDisplayErrors);

            if (empty($rs)) {
                // @codeCoverageIgnoreStart
                throw new \Exception($this->conn->ErrorMsg(), -1);
                // @codeCoverageIgnoreEnd
            }
        } catch (\Exception $e) {
            // @codeCoverageIgnoreStart
            // Log and output error
            $trace = "======== Adodb Connect Error ========\n"
                //. $e->getTraceAsString() . "\n"
                . $e->getMessage() . "\n";
            error_log($trace);

            if (!$this->getUtil('Env')->isCli()) {
                $trace = $this->getUtil('StringUtil')->encodeHtml($trace);
            }
            echo $trace;

            return false;
            // @codeCoverageIgnoreEnd
        }

        // @codeCoverageIgnoreStart
        // Mysql db need to 'set names' after connect
        if ($this->isDbMysql()) {
            $this->conn->Execute(
                'SET NAMES "'
                . str_replace('UTF-8', 'UTF8', strtoupper($this->profile['lang']))
                . '"'
            );
        }
        // @codeCoverageIgnoreEnd

        return true;
    }


    /**
     * Convert encoding from db to sys
     *
     * Mostly used on query result.
     *
     * @param   mixed   &$rs    (Array of)string, not RecordSet object
     * @return mixed
     */
    public function convertEncodingRs(&$rs)
    {
        if (empty($rs) || $this->charsetPhp == $this->profile['lang']) {
            return $rs;
        }

        if (is_array($rs)) {
            foreach ($rs as &$val) {
                $this->convertEncodingRs($val);
            }
            unset($val);
        } elseif (is_string($rs)) {
            $rs = mb_convert_encoding(
                $rs,
                $this->charsetPhp,
                $this->profile['lang']
            );
        }

        return $rs;
    }


    /**
     * Convert encoding from sys to db
     *
     * Mostly used on SQL statement.
     *
     * @param   mixed   &$sql
     * @return  mixed
     */
    public function convertEncodingSql(&$sql)
    {
        if (empty($sql) || $this->charsetPhp == $this->profile['lang']) {
            return $sql;
        }

        if (is_array($sql)) {
            foreach ($sql as &$val) {
                $this->convertEncodingSql($val);
            }
            unset($val);
        } elseif (is_string($sql)) {
            $sql = mb_convert_encoding(
                $sql,
                $this->profile['lang'],
                $this->charsetPhp
            );
        }

        return $sql;
    }


    /**
     * Count how many db query have executed
     *
     * Can be extend to count on multi db objects.
     *
     * @param   int $step
     */
    protected function countQuery($step = 1)
    {
        $this->queryCount += $step;
    }


    /**
     * Delete rows by condition user given
     *
     * Return value:
     * -1 error,
     * 0 not found,
     * N > 0 number of deleted rows.
     *
     * @param   string  $table
     * @param   string  $cond   Condition, can be where, having etc, raw sql string, not null.
     * @return  int
     */
    public function delRow($table, $cond)
    {
        $cond = trim($cond);
        if (empty($cond)) {
            return -1;
        }

        $this->executePrepare(
            $this->sqlGenerator->get(array('DELETE' => $table))
            . ' ' . $cond
        );

        if (0 != $this->conn->ErrorNo()) {
            // @codeCoverageIgnoreStart
            // Error is rollbacked, no errorno return

            // Execute error
            return -1;

            // @codeCoverageIgnoreEnd
        } else {
            return $this->conn->Affected_Rows();
        }
    }


    /**
     * Dummy for ADOdb's ErrorMsg()
     *
     * @return  string
     */
    public function errorMessage()
    {
        return $this->conn->ErrorMsg();
    }


    /**
     * Alias of errorMessage() for backward compatible
     */
    public function errorMsg()
    {
        return $this->errorMessage();
    }


    /**
     * Dummy for ADOdb's ErrorNo()
     *
     * @return  int
     */
    public function errorCode()
    {
        return $this->conn->errorNo();
    }


    /**
     * Alias of errorCode() for backward compatible
     */
    public function errorNo()
    {
        return $this->errorCode();
    }


    /**
     * Execute SQL, without transaction
     *
     * @param   mixed   $sql        SQL statement or sqlCfg for SqlGenerator
     * @param   mixed   $inputArr
     * @return  object
     */
    public function execute($sql, $inputArr = false)
    {
        if (is_array($sql)) {
            $sql = $this->sqlGenerator->get($sql);
        }

        $this->convertEncodingSql($sql);

        $this->countQuery();

        return $this->conn->Execute($sql, $inputArr);
    }


    /**
     * Prepare and execute sql, with transaction
     *
     * @param   string  $sql
     * @param   array   $inputArr   Optional parameters in sql
     * @return  object
     */
    public function executePrepare($sql, $inputArr = false)
    {
        if (is_array($sql)) {
            $sql = $this->sqlGenerator->getPrepared($sql);
        }

        $this->convertEncodingSql($sql);

        $stmt = $this->conn->Prepare($sql);

        $this->conn->BeginTrans();

        $rs = $this->conn->Execute($stmt, $inputArr);

        $this->countQuery();

        if (0 != $this->conn->ErrorNo()) {
            // @codeCoverageIgnoreStart

            // Log error
            trigger_error(
                'ErrorNo: ' . $this->conn->ErrorNo()
                . "\nErrorMsg: " . $this->conn->ErrorMsg(),
                E_USER_ERROR
            );
            $this->conn->RollbackTrans();
            return -1;

            // @codeCoverageIgnoreEnd
        } else {
            $this->conn->CommitTrans();
            return $rs;
        }
    }


    /**
     * Find name of timestamp column of a table
     *
     * Timestamp column are various for different db, hard to test.
     *
     * @param   $table
     * @return  string
     */
    public function findColumnTs($table)
    {
        $arCol = $this->getMetaColumn($table);
        if (empty($arCol)) {
            return '';
        }

        // @codeCoverageIgnoreStart
        if ($this->isDbSybase()) {
            // Sybase's timestamp column must be lower cased.
            // If col name is 'timestamp', will auto assign (timestamp) type.
            $rs = $this->execute(
                array(
                    'SELECT' => array(
                        'name'      => 'a.name',
                        'length'    => 'a.length',
                        'usertype'  => 'a.usertype',
                        'type'      => 'b.name',
                    ),
                    'FROM'  => array(
                        'a' => 'syscolumns',
                        'b' => 'systypes'
                    ),
                    'WHERE' => array(
                        "a.id = object_id('$table')",
                        'a.type = b.type',
                        'a.usertype = b.usertype',
                        // Without below line, can retrieve sybase's col info
                        'b.name = "timestamp"',
                    ),
                )
            );
            if (!empty($rs) && 0 < $rs->RowCount()) {
                return $rs->fields['name'];
            } else {
                return '';
            }

        } elseif ($this->isDbMysql()) {
            // Check 'type'
            foreach ($arCol as $k => $v) {
                if (isset($v->type) && 'timestamp' == $v->type) {
                    return $k;
                }
            }

        } else {
            // Do not trigger error, null means no implemented.
            return null;

            trigger_error(
                __CLASS__ . '::findColumnTs() for '
                . $this->profile['type']
                . ' not implemented!',
                E_USER_ERROR
            );
        }
        // @codeCoverageIgnoreEnd

        // No timestamp found
        return '';
    }


    /**
     * Generate SQL statement
     *
     * User should avoid use SELECT/UPDATE/INSERT/DELETE simultaneously.
     *
     * Generate order by SQL statement format order.
     *
     * UPDATE/INSERT/DELETE is followed by [TBL_NAME], so need not use FROM.
     *
     * @see Fwlib\Db\SqlGenerator
     * @param   array   $sqlConfig
     * @return  string
     */
    public function generateSql($sqlConfig)
    {
        if (!empty($sqlConfig)) {
            return $this->sqlGenerator->get($sqlConfig);
        } else {
            return '';
        }
    }


    /**
     * Generate SQL statement for Prepare
     *
     * Format like value -> ? or :name, and quote chars removed.
     *
     * @see generateSql()
     * @see Fwlib\Db\SqlGenerator
     * @param   array   $sqlConfig
     * @return  string
     */
    public function generateSqlPrepared($sqlConfig)
    {
        if (!empty($sqlConfig)) {
            return $this->sqlGenerator->getPrepared($sqlConfig);
        } else {
            return '';
        }
    }


    /**
     * Get data from single table using PK
     *
     * $pkVal, $col, $pkCol support multi valuesplit by ',' or array,
     * eg: 'val' || 'val1, val2' || array('val1', 'val2')
     *
     * $col can include value 'colName AS colAlias'.
     *
     * '*' can be used for $col, means all cols in table, this way can't use
     * inner cache, not recommend.
     *
     * Notice: if $col is array, must indexed by number start from 0.
     *
     * Also, this function can be used to retrieve data from a table with
     * other condition by assign $pkCol to non-PK column, but it SHOULD ONLY
     * use on unique index or maximum 1 record exists.
     *
     * @param   string  $table
     * @param   mixed   $pkVal
     * @param   mixed   $col            Cols need to retrieve
     * @param   mixed   $pkCol          PK column name, null to auto get
     * @return  mixed                   Single/array, null if error occur
     */
    public function getByPk($table, $pkVal, $col = null, $pkCol = null)
    {
        $stringUtil = $this->getUtil('StringUtil');

        // Treat PK col
        if (empty($pkCol)) {
            $pkCol = $this->getMetaPrimaryKey($table);
        }

        // Convert PK value and col name to array
        if (!is_array($pkVal)) {
            if (is_string($pkVal)) {
                $pkVal = $stringUtil->toArray($pkVal, ',');
            } else {
                // @codeCoverageIgnoreStart
                $pkVal = array($pkVal);
                // @codeCoverageIgnoreEnd
            }
        }
        if (!is_array($pkCol)) {
            if (is_string($pkCol)) {
                $pkCol = $stringUtil->toArray($pkCol, ',');
            } else {
                // @codeCoverageIgnoreStart
                $pkCol = array($pkCol);
                // @codeCoverageIgnoreEnd
            }
        }

        // $pkCol need to be array same count with $pkVal
        if (count($pkVal) != count($pkCol)) {
            // @codeCoverageIgnoreStart
            trigger_error('PK value and column not match.', E_USER_WARNING);
            return null;
            // @codeCoverageIgnoreEnd
        }

        // Convert col to proper array
        if (empty($col)) {
            $col = '*';
        }
        if ('*' == $col) {
            // Drop uppercased index
            $col = array_values($this->getMetaColumnName($table));
        }
        if (!is_array($col)) {
            if (is_string($col)) {
                $col = $stringUtil->toArray($col, ',');
            } else {
                // Column is not array nor string? is int? should not happen
                // @codeCoverageIgnoreStart
                $col = array($col);
                // @codeCoverageIgnoreEnd
            }
        }

        // $pkVal, $col, $pkCol all converted to array

        // Retrieve from db
        $sqlCfg = array(
            'SELECT'    => $col,
            'FROM'      => $table,
            'LIMIT'     => 1,
        );
        while (!empty($pkVal)) {
            $pkName = array_shift($pkCol);
            $sqlCfg['WHERE'][] = $pkName . ' = '
                . $this->quoteValue($table, $pkName, array_shift($pkVal));
            unset($pkName);
        }
        $rs = $this->execute($sqlCfg);
        $ar = array();
        if (!empty($rs) && !$rs->EOF) {
            $ar = $rs->GetRowAssoc(false);
        }

        // Return value
        if (empty($ar)) {
            return null;

        } else {
            if (1 == count($ar)) {
                return array_pop($ar);

            } else {
                return $ar;
            }
        }
    }


    /**
     * Get table schema
     *
     * @see $metaColumn
     * @param   string  $table
     * @param   boolean $forcenew   Force to retrieve instead of read from cache
     * @return  array
     */
    public function getMetaColumn($table, $forcenew = false)
    {
        if (!isset($this->metaColumn[$table]) || (true == $forcenew)) {
            $this->metaColumn[$table] = $this->conn->MetaColumns($table);
            if (empty($this->metaColumn[$table])) {
                return null;
            }

            // Convert columns to native case
            $colName = $this->getMetaColumnName($table, $forcenew);
            // $colName = array(COLUMN => column), $c is UPPER CASED
            $art = array();
            foreach ($this->metaColumn[$table] as $c => $ar) {
                $art[$colName[strtoupper($c)]] = $ar;
            }
            $this->metaColumn[$table] = $art;

            // @codeCoverageIgnoreStart
            // Fix sybase display timestamp column as varbinary
            if ($this->isDbSybase()) {
                $s = $this->findColumnTs($table);
                if (!empty($s)) {
                    $this->metaColumn[$table][$s]->type = 'timestamp';
                }
            }
            // @codeCoverageIgnoreEnd
        }

        return $this->metaColumn[$table];
    }


    /**
     * Get table column name
     *
     * @see $metaColumnName
     * @param   string  $table
     * @param   boolean $forcenew   Force to retrieve instead of read from cache
     * @return  array
     */
    public function getMetaColumnName($table, $forcenew = false)
    {
        if (!isset($this->metaColumnName[$table]) || (true == $forcenew)) {
            $this->metaColumnName[$table] = $this->conn->MetaColumnNames($table);
        }
        return $this->metaColumnName[$table];
    }


    /**
     * Get primary key column of a table
     *
     * Return single string value or array for multi column primary key.
     *
     * @param   string  $table
     * @param   boolean $forcenew   Force to retrieve instead of read from cache
     * @return  mixed
     * @see $metaPrimaryKey
     */
    public function getMetaPrimaryKey($table, $forcenew = false)
    {
        if (!isset($this->metaPrimaryKey[$table]) || (true == $forcenew)) {
            // Find using ADOdb first
            $ar = $this->conn->MetaPrimaryKeys($table);

            // @codeCoverageIgnoreStart
            if (false == $ar || empty($ar)) {
                /**
                 * ADOdb not support, find PK manually
                 *
                 * For Sybase:
                 * @link http://topic.csdn.net/t/20030117/17/1369396.html
                 *
                 * SELECT name, keycnt
                 *      , index_col(tableName, indid, 1)    -- 1st PK col
                 *      , index_col(tableName, indid, 2)    -- 2nd PK col if has
                 * FROM sysindexes
                 * WHERE status & 2048 = 2048
                 *      AND id = object_id(tableName)
                 *
                 * keycnt is column count in PK. if PK index is not cursor
                 * index(by 0x10 bit in status), its keycnt - 1.
                 *
                 * Test pass for PK include 2 columns.
                 */
                if ($this->isDbSybase()) {
                    $rs = $this->executePrepare(
                        array(
                            'SELECT' => array(
                                'name', 'keycnt',
                                'k1' => "index_col('$table', indid, 1)",
                                'k2' => "index_col('$table', indid, 2)",
                                'k3' => "index_col('$table', indid, 3)",
                            ),
                            'FROM'  => 'sysindexes',
                            'WHERE' => array(
                                'status & 2048 = 2048 ',
                                "id = object_id('$table')",
                            )
                        )
                    );
                    if (true == $rs && 0 < $rs->RowCount()) {
                        // Got
                        $ar = array($rs->fields['k1']);
                        if (!empty($rs->fields['k2'])) {
                            $ar[] = $rs->fields['k2'];
                        }
                        if (!empty($rs->fields['k3'])) {
                            $ar[] = $rs->fields['k3'];
                        }
                    } else {
                        // Table have no primary key
                        $ar = '';
                    }
                }
            }
            // @codeCoverageIgnoreEnd

            // Convert columns to native case
            if (!empty($ar)) {
                $colName = $this->GetMetaColumnName($table);
                // $colName = array(COLUMN => column), $c is UPPER CASED
                $art = array();
                foreach ($ar as $idx => $col) {
                    $art[] = $colName[strtoupper($col)];
                }
                $ar = $art;
            }

            if (is_array($ar) && 1 == count($ar)) {
                // Only 1 primary key column
                $ar = $ar[0];
            }

            // Set to cache
            if (!empty($ar)) {
                $this->metaPrimaryKey[$table] = $ar;
            }
        }

        if (isset($this->metaPrimaryKey[$table])) {
            return $this->metaPrimaryKey[$table];

        } else {
            return null;
        }
    }


    /**
     * Get string describe of profile
     *
     * Usually used for identify db source.
     *
     * @param   string  $separator
     * @return  string
     */
    public function getProfileString($separator = '-')
    {
        return $this->profile['type'] . $separator .
            $this->profile['host'] . $separator .
            $this->profile['name'];
    }


    /**
     * Get rows count by condition user given
     *
     * Return value:
     * -1: error,
     * N >= 0: number of rows.
     *
     * @param   string  $table
     * @param   string  $cond   Condition, raw sql, can be WHERE, HAVING etc
     * @return  int
     */
    public function getRowCount($table, $cond = '')
    {
        $sqlCfg = array(
            'SELECT'    => array('c' => 'COUNT(1)'),
            'FROM'      => $table,
        );
        $rs = $this->executePrepare(
            $this->sqlGenerator->get($sqlCfg)
            . ' ' . $cond
        );
        if (false == $rs || 0 != $this->conn->ErrorNo()
            || 0 == $rs->RowCount()
        ) {
            // Execute error, rare happen
            // @codeCoverageIgnoreStart
            return -1;
            // @codeCoverageIgnoreEnd
        } else {
            return $rs->fields['c'];
        }
    }


    /**
     * Get delimiter between SQL for various db
     *
     * @param   string  $tail   Tail of line for eye candy
     * @return  string
     */
    public function getSqlDelimiter($tail = "\n")
    {
        // @codeCoverageIgnoreStart
        if ($this->isDbMysql()) {
            $delimiter = ';';

        } elseif ($this->isDbSybase()) {
            $delimiter = '';

        } else {
            trigger_error(
                __CLASS__ . '::getSqlDelimiter() for db type '
                . $this->profile['type'] . ' not implement.',
                E_USER_WARNING
            );
            $delimiter = '';
        }
        // @codeCoverageIgnoreEnd

        return $delimiter . $tail;
    }


    /**
     * Get SQL: begin transaction
     *
     * @return  string
     */
    public function getSqlTransBegin()
    {
        // @codeCoverageIgnoreStart
        if ($this->isDbMysql()) {
            $header = 'START';
        } else {
            $header = 'BEGIN';
        }
        // @codeCoverageIgnoreEnd

        return $header . ' TRANSACTION' . $this->getSqlDelimiter();
    }


    /**
     * Get SQL: commit transaction
     *
     * @return  string
     */
    public function getSqlTransCommit()
    {
        return 'COMMIT' . $this->getSqlDelimiter();
    }


    /**
     * Get SQL: rollback transaction
     *
     * @return  string
     */
    public function getSqlTransRollback()
    {
        return 'ROLLBACK' . $this->GetSqlDelimiter();
    }


    /**
     * If current db is connected successful
     *
     * @return  boolean
     */
    public function isConnected()
    {
        return $this->conn->IsConnected();
    }


    /**
     * If current db type is mysql
     *
     * @return  boolean
     */
    public function isDbMysql()
    {
        return ('mysql' == substr($this->conn->databaseType, 0, 5));
    }


    /**
     * If current db type is sybase
     *
     * @return  boolean
     */
    public function isDbSybase()
    {
        return ('sybase' == substr($this->profile['type'], 0, 6));
    }


    /**
     * If a table exists in db ?
     *
     * @param   string  $table
     * @return  boolean
     */
    public function isTableExist($table)
    {
        $table = addslashes($table);

        // @codeCoverageIgnoreStart
        if ($this->isDbSybase()) {
            $sql = 'SELECT count(1) AS c FROM sysobjects WHERE name = "'
                . $table . '" AND type = "U"';
            $rs = $this->execute($sql);
            return (0 != $rs->fields['c']);

        } elseif ($this->isDbMysql()) {
            $sql = "SHOW TABLES LIKE '$table'";
            $rs = $this->execute($sql);
            return (0 != $rs->RowCount());

        } else {
            // :THINK: Better method ?
            $sql = "SELECT 1 FROM $table";
            $rs = $this->execute($sql);
            return (0 == $this->conn->ErrorNo());
        }
        // @codeCoverageIgnoreEnd
    }


    /**
     * If timestamp column's value is unique
     *
     * @return  boolean
     */
    public function isTsUnique()
    {
        // Default for: sybase
        $b = true;

        // @codeCoverageIgnoreStart
        if ($this->isDbMysql()) {
            $b = false;
        }
        // @codeCoverageIgnoreEnd

        return $b;
    }


    /**
     * New SqlGenerator property
     *
     * @return  Fwlib\Db\SqlGenerator
     */
    protected function newInstanceSqlGenerator()
    {
        return new SqlGenerator($this);
    }


    /**
     * Generate a bind placeholder portably
     *
     * @param   string  $name
     * @return  string
     */
    public function param($name)
    {
        return $this->conn->Param($name);
    }


    /**
     * Smart quote string in sql, by check columns type
     *
     * @param   string  $table
     * @param   string  $col
     * @param   mixed   $val
     * @return  string
     */
    public function quoteValue($table, $col, $val)
    {
        $this->getMetaColumn($table);
        if (!isset($this->metaColumn[$table][$col]->type)) {
            trigger_error(
                "Column to quote not exists($table.$col).",
                E_USER_WARNING
            );

            // @codeCoverageIgnoreStart
            // Return quoted value for safety
            $val = stripslashes($val);
            return $this->conn->qstr($val, false);
            // @codeCoverageIgnoreEnd
        }

        $type = $this->metaColumn[$table][$col]->type;
        if (in_array(
            $type,
            array(
                'bigint',
                'bit',
                'decimal',
                'double',
                'float',
                'int',
                'intn',     // Sybase - tinyint
                'mediumint',
                'numeric',
                'numericn', // Sybase - numeric
                'real',
                'smallint',
                'tinyint',
            )
        )) {
            // Need not quote, output directly
            return $val;

        } elseif ($this->isDbSybase() && 'timestamp' == $type) {
            // Sybase timestamp
            // @codeCoverageIgnoreStart
            return '0x' . $val;
            //elseif ($this->IsDbSybase() && 'varbinary' == $type && 'timestamp' == $col)
            // @codeCoverageIgnoreEnd

        } else {
            // Need quote, use db's quote method
            $val = stripslashes($val);
            return $this->conn->qstr($val, false);
        }
    }


    /**
     * Set PHP script file charset
     *
     * @param   string  $charset
     * @see $charsetPhp
     */
    public function setCharsetPhp($charset)
    {
        $this->charsetPhp = $charset;
    }


    /**
     * Smart write data row(s) to db
     *
     * Can auto check row existence, and decide to use INSERT or UPDATE, this
     * require primary key column included in $data array.  Also, table MUST
     * have primary key defined.
     *
     * Param $data can include single row(1-dim array, index is column name)
     * or multiple rows(2-dim array, index layer 1 MUST be number and will not
     * write to db, layer 2 is same as single row).
     *
     * Param $mode is case insensitive:
     * A: auto detect, for multiple rows data, will only detect by FIRST row.
     * U: update,
     * I: insert,
     *
     * Return number of inserted or updated rows:
     * -1: got error,
     * N >=0: success, which N is affected rows.
     *
     * Even data to write exists in db and same, it will still do write
     * operation, and been counted in return value.
     *
     * @param   string  $table
     * @param   array   $data   Row(s) data
     * @param   string  $mode   Write mode
     * @return  int
     */
    public function write($table, $data, $mode = 'A')
    {
        // Find primary key column first
        $arPk = $this->getMetaPrimaryKey($table);

        // Convert single row data to multi row mode
        if (!isset($data[0])) {
            $data = array(0 => $data);
        }

        // Convert primary key to array if it's single string now
        if (!is_array($arPk)) {
            $arPk = array(0 => $arPk);
        }

        // Columns in $data
        $arCols = array_keys($data[0]);
        // Check if primary key is assigned in $data
        $arPkInData = true;
        foreach ($arPk as $key) {
            if (!in_array($key, $arCols)) {
                $arPkInData = false;
            }
        }
        // If no primary key column in $data, return -1
        if (false == $arPkInData) {
            return -1;
        }

        $mode = strtoupper($mode);
        // Auto detemine mode
        if ('A' == $mode) {
            $where = ' WHERE ';
            foreach ($arPk as $key) {
                $where .= " $key = "
                    . $this->quoteValue($table, $key, $data[0][$key])
                    . ' AND ';
            }
            $where = substr($where, 0, strlen($where) - 5);
            if (0 < $this->getRowCount($table, $where)) {
                $mode = 'U';
            } else {
                $mode = 'I';
            }
        }

        // Prepare sql
        if ('U' == $mode) {
            $sqlCfg = array(
                'UPDATE' => $table,
                'LIMIT' => 1,
                );
            // Primary key cannot change, so exclude them from SET clause,
            // Here use prepare, actual value will assign later, do quote
            // then.
            // :NOTICE: Remember to put PK data to end of row data array when
            // assign actual value, because WHERE clause is after SET clause.
            foreach ($arPk as $key) {
                $sqlCfg['WHERE'][] = "$key = "
                    . $this->conn->Param($key);
                unset($arCols[array_search($key, $arCols)]);
            }
            foreach ($arCols as $key) {
                $sqlCfg['SET'][$key] = $this->conn->Param($key);
            }

        } elseif ('I' == $mode) {
            $arVal = array();
            foreach ($arCols as $key) {
                $arVal[$key] = $this->conn->Param($key);
            }
            $sqlCfg = array(
                'INSERT' => $table,
                'VALUES' => $arVal,
            );
        }
        $sql = $this->sqlGenerator->getPrepared($sqlCfg);
        // @codeCoverageIgnoreStart
        if (empty($sql)) {
            return -1;
        }
        // @codeCoverageIgnoreEnd

        // Change PK position in data array
        if ('U' == $mode) {
            foreach ($data as &$row) {
                foreach ($arPk as $key) {
                    $v = $row[$key];
                    unset($row[$key]);
                    $row[$key] = $v;
                }
            }
            unset($row);
        }

        // Convert data encoding
        $this->convertEncodingSql($data);

        // Do db prepare
        $stmt = $this->conn->Prepare($sql);

        // @codeCoverageIgnoreStart
        // Execute, actual write data
        $this->conn->BeginTrans();
        try {
            $this->conn->Execute($stmt, $data);

        } catch (Exception $e) {
            // Show error message ?
            $this->conn->RollbackTrans();
            return -1;
        }

        // Any other error ?
        if (0 != $this->conn->ErrorNo()) {
            // Log error
            trigger_error(
                'ErrorNo: ' . $this->conn->ErrorNo() . "\n" .
                'ErrorMsg: ' . $this->conn->ErrorMsg(),
                E_USER_WARNING
            );
            $this->RollbackTrans();
            return -1;

        } else {
            $this->conn->CommitTrans();
            return count($data);
        }
        // @codeCoverageIgnoreEnd
    }
}
