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
 * provide a method convertEncodingResult() to do this manually.
 *
 * @copyright   Copyright 2008-2014 Fwolf
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
    protected $queryCount = 0;

    /**
     * Sql generator object
     *
     * @var SqlGenerator
     */
    protected $sqlGenerator;


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

        // From ADOdb 5.11 Execute 2d array is disabled by default, we need
        // enable it for using write etc.
        $this->conn->bulkBind = true;
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
        return $this->conn->$name;
    }


    /**
     * Redirect property set to adodb
     *
     * @param string    $name
     * @param mixed     $val
     */
    public function __set($name, $val)
    {
        // For object need auto new in this class instead of $this->conn, with
        // machenishm in class AbstractAutoNewInsance with newInstanceXxx()
        // method, need check in __get() and __set() both. If only treat in
        // __get(), the new instance and assign operate will happen, but its
        // assigned to $this->conn->property, instead of $this->property, next
        // time when it's used(get), will trigger __get() again, and do
        // useless newInstanceXxx() again.
        //
        // By use get method similar with getService(), this is not problem
        // anymore.

        $this->conn->$name = $val;
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


        // To eliminate sybase 'Changed database context to XXX' message,
        // should edit php.ini and change mssql.min_message_severity to 11.
        // @link https://bugs.php.net/bug.php?id=34784


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
     * @param   array|string    &$result    Array or string, not RecordSet object
     * @return  array|string
     */
    public function convertEncodingResult(&$result)
    {
        if (empty($result) || $this->charsetPhp == $this->profile['lang']) {
            return $result;
        }

        if (is_array($result)) {
            foreach ($result as &$value) {
                $this->convertEncodingResult($value);
            }
            unset($value);

        } elseif (is_string($result)) {
            $result = mb_convert_encoding(
                $result,
                $this->charsetPhp,
                $this->profile['lang']
            );
        }

        return $result;
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
     * @param   string  $condition  Not empty, can be raw sql where, having etc
     * @return  int
     */
    public function deleteRow($table, $condition)
    {
        $condition = trim($condition);
        if (empty($condition)) {
            return -1;
        }

        $this->executePrepare(
            $this->getSqlGenerator()->get(array('DELETE' => $table))
            . ' ' . $condition
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
     * Alias of errorMessage() for backward compatible
     */
    public function errorMsg()
    {
        return $this->getErrorMessage();
    }


    /**
     * Alias of errorCode() for backward compatible
     */
    public function errorNo()
    {
        return $this->getErrorCode();
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
            $sql = $this->getSqlGenerator()->get($sql);
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
            $sql = $this->getSqlGenerator()->getPrepared($sql);
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
            return $this->getSqlGenerator()->get($sqlConfig);
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
            return $this->getSqlGenerator()->getPrepared($sqlConfig);
        } else {
            return '';
        }
    }


    /**
     * Get single row data from single table using key
     *
     * Also, this method can be used to retrieve data from a table by primary
     * or unique key, default and recommend for primary key, which can be auto
     * retrieved from table meta.
     *
     * Whatever key is used, the result should only contain maxinum one row,
     * or the result is wrong, commonly only include data of first match row.
     *
     *
     * $keyValue, $column, $keyColumn support multiple value split by ',' or
     * array, eg: 'value' or 'value1, value2' or array('value1', 'value2')
     *
     * $column can use style like 'colName AS colAlias'.
     *
     * '*' can be used for $column, means all columns in table.
     *
     * Notice: if $column is array, must indexed by number start from 0.
     *
     * @param   string          $table
     * @param   int|string      $keyValue
     * @param   string|array    $column     Empty or '*' for all column
     * @param   string|array    $keyColumn  Empty to use primary key
     * @return  int|string|array    Single value or array of it, null if error occur
     */
    public function getByKey(
        $table,
        $keyValue,
        $column = null,
        $keyColumn = array()
    ) {
        $stringUtil = $this->getUtil('StringUtil');

        // Treat key column
        if (empty($keyColumn)) {
            $keyColumn = $this->getMetaPrimaryKey($table);
        }

        // Convert key value and column name to array
        if (is_string($keyValue)) {
                $keyValue = $stringUtil->toArray($keyValue, ',');
        } else {
            $keyValue = (array)$keyValue;
        }

        if (is_string($keyColumn)) {
            $keyColumn = $stringUtil->toArray($keyColumn, ',');
        } else {
            $keyColumn = (array)$keyColumn;
        }

        // $keyColumn need to be array same count with $keyValue
        if (count($keyValue) != count($keyColumn)) {
            // @codeCoverageIgnoreStart
            trigger_error('Key value and column not match.', E_USER_WARNING);
            return null;
            // @codeCoverageIgnoreEnd
        }


        if (empty($column) || '*' == $column) {
            // Drop uppercased index
            $column = array_values($this->getMetaColumnName($table));

        } elseif (!is_array($column)) {
            if (is_string($column)) {
                $column = $stringUtil->toArray($column, ',');
            } else {
                // Column is not array nor string? is int? should not happen
                // @codeCoverageIgnoreStart
                $column = array($column);
                // @codeCoverageIgnoreEnd
            }
        }

        // $keyValue, $column, $keyColumn all converted to array


        // Retrieve from db
        $sqlConfig = array(
            'SELECT'    => $column,
            'FROM'      => $table,
            'LIMIT'     => 1,
        );
        while (!empty($keyValue)) {
            $singleKey = array_shift($keyColumn);
            $sqlConfig['WHERE'][] = $singleKey . ' = '
                . $this->quoteValue($table, $singleKey, array_shift($keyValue));
            unset($singleKey);
        }
        $rs = $this->execute($sqlConfig);
        $ar = array();
        if (!empty($rs) && !$rs->EOF) {
            $ar = $rs->FetchRow();
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
     * Dummy for ADOdb's ErrorNo()
     *
     * @return  int
     */
    public function getErrorCode()
    {
        return $this->conn->ErrorNo();
    }


    /**
     * Dummy for ADOdb's ErrorMsg()
     *
     * @return  string
     */
    public function getErrorMessage()
    {
        return $this->conn->ErrorMsg();
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
                $s = $this->getMetaTimestamp($table);
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

            // @codeCoverageIgnoreStart
            if ($this->isDbSybase()) {
                /**
                 * MetaPrimaryKey() in ADOdb has error(till v5.18), find PK
                 * manually.
                 *
                 * @link http://topic.csdn.net/t/20030117/17/1369396.html
                 *
                 * SELECT name, keycnt,
                 *      index_col(tableName, indid, 1),    -- 1st PK col
                 *      index_col(tableName, indid, 2)     -- 2nd PK col if has
                 * FROM sysindexes
                 * WHERE status & 2048 = 2048
                 *      AND id = object_id(tableName)
                 *
                 * keycnt is column count in PK. if PK index is not cursor
                 * index(by 0x10 bit in status), its keycnt - 1.
                 *
                 * Test pass for PK include 2 columns.
                 */
                $rs = $this->execute(
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

            } else {
                // Find using ADOdb first
                $ar = $this->conn->MetaPrimaryKeys($table);
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
     * Get name of timestamp column of a table
     *
     * Timestamp column are various for different db, hard to test.
     *
     * @param   $table
     * @return  string
     */
    public function getMetaTimestamp($table)
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
                __CLASS__ . '::getMetaTimestamp() for '
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
     * Getter of $profile
     *
     * @return  array
     */
    public function getProfile()
    {
        return $this->profile;
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
     * Getter of $queryCount
     *
     * @return  int
     */
    public function getQueryCount()
    {
        return $this->queryCount;
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
            $this->getSqlGenerator()->get($sqlCfg)
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
     * Get SqlGenerator instance
     *
     * @return  SqlGenerator
     */
    protected function getSqlGenerator()
    {
        if (is_null($this->sqlGenerator)) {
            $this->sqlGenerator = new SqlGenerator($this);
        }

        return $this->sqlGenerator;
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
    public function isTimestampUnique()
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
        $sql = $this->getSqlGenerator()->getPrepared($sqlCfg);
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

        } catch (\Exception $e) {
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
