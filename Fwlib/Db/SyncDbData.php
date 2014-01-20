<?php
namespace Fwlib\Db;

use Fwlib\Bridge\Adodb;
use Fwlib\Util\AbstractUtilAware;
use Fwlib\Util\UuidBase36;

/**
 * Sync data between 2 db source with same schema
 *
 * Support one-way sync only.
 *
 * Sync define is an array, which key is source table, and value is dest table
 * or array of it. Sync is based on timestamp column in source table, on db
 * table should have at most 1 timestamp column, so use source table name as
 * key of define array is fine. Eg:
 *
 * {
 *  tableSrce1: tableDest1,
 *  tableSrce2: [tableDest2a, tableDest2b],
 * }
 *
 * By default data from srce is directly write to dest, but you can do some
 * convert by define method convertData[TableSrce]To[TableDest](), it's
 * parameter is data array retrieved from srce, and return value should be
 * data array to write to dest. These convert method will automatic be called
 * if exists and fit srce/dest table name.
 *
 * When sync job is done for a table, the latest timestamp will save in record
 * table in dest db, next time sync job will start from this timestamp.
 *
 * Avoid concurrenct run by file lock.
 * @link http://stackoverflow.com/questions/16048648
 *
 * @package     Fwlib\Base
 * @copyright   Copyright 2008-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2008-05-20
 */
class SyncDbData extends AbstractUtilAware
{
    /**
     * Number of rows have processed
     *
     * Shared by syncDelete() and syncOneway().
     *
     * @var integer
     */
    protected $batchDone = 0;

    /**
     * Maxinum rows to process per run
     *
     * If dest table is array, the actual rows synced may exceed this limit.
     *
     * @var integer
     */
    public $batchSize = 1000;

    /**
     * Source db connection
     *
     * @var Fwlib\Bridge\Adodb
     */
    protected $dbSrce = null;

    /**
     * Destination db connection
     *
     * @var Fwlib\Bridge\Adodb
     */
    protected $dbDest = null;

    /**
     * Lock file handle
     *
     * @var resource
     */
    protected $lock = null;

    /**
     * Lock file to avoid concurrence run
     *
     * @var string
     */
    public $lockfile = 'sync-db-data.lock';

    /**
     * Log message array
     *
     * @var array
     */
    public $logMessage = array();

    /**
     * Name of record table
     *
     * @var string
     */
    public $tableRecord = 'sync_db_data_record';

    /**
     * Output all log message directly
     *
     * @var boolean
     */
    public $verbose = false;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->log('========  ' . date('Y-m-d H:i:s') . '  ========');

        try {
            $this->createLock($this->lockfile);

        } catch (\Exception $e) {
            $message = "Aborted: {$e->getMessage()}";

            $this->log($message);

            throw new \Exception($message);
        }
    }


    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->releaseLock();
    }


    /**
     * Check and create record table if not exists
     *
     * @param   Fwlib\Bridge\Adodb  $db
     * @param   string  $table
     */
    protected function checkTableRecord($db, $table)
    {
        if ($db->isTableExist($table)) {
            $this->log("Record table $table already exists.");

            return;
        }

        // @codeCoverageIgnoreStart
        try {
            // Table doesn't exist, create it
            // SQL for Create table diffs from several db

            if ($db->isDbSybase()) {
                // Sybase's index was created seperated
                $db->Execute(
                    "CREATE TABLE $table (
                        uuid        CHAR(25)    NOT NULL,
                        db_prof     VARCHAR(50) NOT NULL,
                        tbl_title   VARCHAR(50) NOT NULL,
                        /* Timestamp remembered, for next round */
                        last_ts     VARCHAR(50) NOT NULL,
                        /* Timestamp for this table */
                        /* In sybase 'timestamp' must be lower cased */
                        ts          timestamp   NOT NULL,
                        constraint PK_$table PRIMARY KEY (uuid)
                    )"
                );
                $db->Execute(
                    "CREATE INDEX idx_{$table}_1 ON
                        $table (db_prof, tbl_title)
                    "
                );

            } elseif ($db->isDbMysql()) {
                // ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
                $db->Execute(
                    "CREATE TABLE $table (
                        uuid        CHAR(36)    NOT NULL,
                        db_prof     VARCHAR(50) NOT NULL,
                        tbl_title   VARCHAR(50) NOT NULL,
                        /* Timestamp remembered, for next round */
                        last_ts     VARCHAR(50) NOT NULL,
                        /* Timestamp for this table */
                        ts          TIMESTAMP   NOT NULL
                            DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        PRIMARY KEY (uuid),
                        INDEX idx_{$table}_1 (db_prof, tbl_title)
                    );"
                );

            } else {
                throw new \Exception('Create table SQL not implemented.');
            }

            $this->log("Record table $table doesn't exist, create it, done.");

        } catch (\Exception $e) {
            $message = "Record table $table doesn't exists and create fail: " .
                $e->getMessage();

            $this->log($message);

            throw new \Exception($message);
        }
        // @codeCoverageIgnoreEnd
    }


    /**
     * Create lock using lockfile
     *
     * @param   string  $lockfile
     */
    protected function createLock($lockfile)
    {
        $lockfile = sys_get_temp_dir() . "/$lockfile";
        $lock = fopen($lockfile, 'w+');

        // LOCK_NB make flock not blocking when obtain LOCK_EX fail
        if (!flock($lock, LOCK_EX | LOCK_NB)) {
            throw new \Exception('Lockfile check failed.');
        }

        // Keep lockfile info for release later
        $this->lock = $lock;
        $this->lockfile = $lockfile;
    }


    /**
     * Generate an UUID
     *
     * The UUID is PK in db record table.
     *
     * @return  string
     */
    protected function generateUuid()
    {
        return $this->getUtil('UuidBase36')->generate();
    }


    /**
     * Get last timestamp remembered
     *
     * @param   $dbDest
     * @param   $table      Table name in source db
     * @return  string      Return null if no last_ts remembered
     */
    protected function getLastTimestamp($dbDest, $table)
    {
        $dbProf = $this->getDbSrceProfileString();

        $rs = $dbDest->execute(
            array(
                'SELECT'    => 'last_ts',
                'FROM'      => $this->tableRecord,
                'WHERE'     => array(
                    "db_prof = '{$dbProf}'",
                    "tbl_title = '$table'",
                ),
                'LIMIT'     => 1,
            )
        );

        if (0 < $rs->RowCount()) {
            return $rs->fields['last_ts'];

        } else {
            return null;
        }
    }


    /**
     * Get profile string of db srce
     *
     * @return  string
     */
    protected function getDbSrceProfileString()
    {
        return $this->dbSrce->getProfileString();
    }


    /**
     * Save or output log message
     *
     * @param   string  $msg
     * @see $verbose
     */
    protected function log($msg)
    {
        $this->logMessage[] = $msg;

        // @codeCoverageIgnoreStart
        if ($this->verbose) {
            $this->getUtil('Env')->ecl($msg);
        }
        // @codeCoverageIgnoreEnd
    }


    /**
     * Release lock used lockfile
     *
     * @param   boolean $deleteLockfile
     */
    protected function releaseLock($deleteLockfile = true)
    {
        flock($this->lock, LOCK_UN);

        fclose($this->lock);

        if ($deleteLockfile) {
            unlink($this->lockfile);
        }
    }


    /**
     * Set srce and dest db connection
     *
     * @param   array|Fwlib\Bridge\Adodb    $srce
     * @param   array|Fwlib\Bridge\Adodb    $dest
     */
    public function setDb($srce, $dest)
    {
        foreach (array('dbSrce' => $srce, 'dbDest' => $dest) as $k => $v) {
            if (is_array($v)) {
                // Param is profile, new db and conect
                $this->$k = new Adodb($v);
                $this->$k->connect();

            } else {
                // Param is connected object
                $this->$k = $v;
            }
        }

        $this->checkTableRecord($this->dbDest, $this->tableRecord);
    }


    /**
     * Record last timestamp in dest db, for next round
     *
     * @param   $dbDest
     * @param   $table      Table name in source db
     * @param   $timestamp
     */
    protected function setLastTimestamp($dbDest, $table, $timestamp)
    {
        $dbProf = $this->getDbSrceProfileString();

        try {
            $timestampOld = $this->getLastTimestamp($dbDest, $table);

            // UPDATE if previous recorded, or INSERT
            if (empty($timestampOld)) {
                $dbDest->execute(
                    array(
                        'INSERT' => $this->tableRecord,
                        'VALUES' => array(
                            'uuid'      => $this->generateUuid(),
                            'db_prof'   => $dbProf,
                            'tbl_title' => $table,
                            'last_ts'   => $timestamp
                        ),
                    )
                );
            } else {
                $dbDest->execute(
                    array(
                        'UPDATE'    => $this->tableRecord,
                        'SET'       => array('last_ts' => $timestamp),
                        'WHERE'     => array(
                            "db_prof = '$dbProf'",
                            "tbl_title = '$table'",
                            ),
                        'LIMIT'     => 1,
                    )
                );
            }

        } catch (\Exception $e) {
            // @codeCoverageIgnoreStart
            $message = "Record timestamp of $table fail: {$e->getMessage()}";

            $this->log($message);

            throw new \Exception($message);
            // @codeCoverageIgnoreEnd
        }
    }


    /**
     * Sync for DELETE
     *
     * If data had been deleted from srce, delete them from dest too.
     *
     * CAUTION: This may delete data in dest not come from srce by sync.
     *
     * @param   array   &$config
     * @return  integer Rows deleted
     */
    public function syncDelete(&$config)
    {
        // syncOneway() should run before syncDelete(), and if it's not fully
        // complete in this round, syncDelete() should wait for next round.
        if ($this->batchDone >= $this->batchSize) {
            $this->log('Wait for syncOneway() fully complete, try next round.');
            return 0;
        }


        $queryCountBeforeSync = $this->dbSrce->getQueryCount() +
            $this->dbDest->getQueryCount();
        $rowsDeleted = 0;

        foreach ($config as $tableSrce => $tableDest) {
            if ($this->batchDone >= $this->batchSize) {
                $this->log("Reach batchSize limit {$this->batchSize}.");
                break;
            }

            $i = $this->syncDeleteTable($tableSrce, $tableDest);

            $this->batchDone += $i;
            $rowsDeleted += $i;
        }

        $queryCount = $this->dbSrce->getQueryCount() +
            $this->dbDest->getQueryCount() - $queryCountBeforeSync;
        $this->log(
            "syncDelete() done, total {$rowsDeleted} rows deleted," .
            " db query $queryCount times.\n"
        );

        return $rowsDeleted;
    }


    /**
     * Sync for delete, single srce table
     *
     * $tableDest can be array of dest table.
     *
     * @param   string          $tableSrce
     * @param   string|array    $tableDest
     * @return  integer     Number of rows deleted on destination db.
     */
    protected function syncDeleteTable($tableSrce, $tableDest)
    {
        if (is_array($tableDest)) {
            $i = 0;
            foreach ($tableDest as $v) {
                $i += $this->syncDeleteTable($tableSrce, $v);
            }

            return $i;
        }


        // If fewer rows in dest, need not do sync
        $iSrce = $this->dbSrce->getRowCount($tableSrce);
        $iDest = $this->dbDest->getRowCount($tableDest);
        if ($iSrce >= $iDest) {
            return 0;
        }


        $log = "syncDelete() check: $tableSrce($iSrce) <- $tableDest($iDest)";

        // Find unnecessary PK in dest using compareData[Srce]To[Dest](), it
        // should return array of PK for rows to delete in dest db. If PK in
        // dest table has multiple column, the PK value is array of these
        // columns, and the order of these column should same as db schema.
        $stringUtil = $this->getUtil('StringUtil');
        $compareFunc = 'compareData' . $stringUtil->toStudlyCaps($tableSrce)
            . 'To' . $stringUtil->toStudlyCaps($tableDest);

        if (!method_exists($this, $compareFunc)) {
            $message = "Compare method needed: $tableSrce to $tableDest.";

            $this->log($message);

            throw new \Exception($message);

        } else {
            $pkToDel = $this->$compareFunc();

            if (empty($pkToDel)) {
                return 0;

            } else {
                $pkToDel = array_slice(
                    $pkToDel,
                    0,
                    $this->batchSize - $this->batchDone
                );
                $this->dbDest->convertEncodingResult($pkToDel);

                // Read PK from dest db
                $pk = $this->dbDest->getMetaPrimaryKey($tableDest);
                // @codeCoverageIgnoreStart
                if (empty($pk)) {
                    throw new \Exception(
                        "syncDelete() need table $tableDest have PK."
                    );
                }
                // @codeCoverageIgnoreEnd
                if (!is_array($pk)) {
                    $pk = array($pk);
                }

                // Generate SQL config
                $sqlConfig = array(
                    'DELETE' => $tableDest,
                    'LIMIT' => 1,
                );
                foreach ($pk as $key) {
                    $sqlConfig['WHERE'][] = "$key = "
                        . $this->dbDest->Param($key);
                }

                // Execute SQL
                $rs = $this->dbDest->executePrepare($sqlConfig, $pkToDel);
                if (!$rs) {
                    // DELETE SQL should not error
                    // @codeCoverageIgnoreStart
                    $message = "Error when execute DELETE SQL on $tableDest.";
                    $this->log($message);
                    return 0;
                    // @codeCoverageIgnoreEnd

                } else {
                    $i = count($pkToDel);
                    $log .= ", $i rows deleted.";
                    $this->log($log);
                    return $i;
                }
            }
        }
    }


    /**
     * Oneway sync for INSERT/UPDATE
     *
     * tableInDest can be array of table name, means tableInSrce's data will
     * sync to more than 1 table in dest db.
     *
     * @param   array   &$config
     * @return  integer Rows synced, count from dest db
     */
    public function syncOneway(&$config)
    {
        $queryCountBeforeSync = $this->dbSrce->getQueryCount() +
            $this->dbDest->getQueryCount();
        $rowsSynced = 0;

        foreach ($config as $tblSrce => $tblDest) {
            if ($this->batchDone >= $this->batchSize) {
                $this->log("Reach batchSize limit {$this->batchSize}.");
                break;
            }

            $i = $this->syncOnewayTable($tblSrce, $tblDest);

            $this->batchDone += $i;
            $rowsSynced += $i;
        }

        $queryCount = $this->dbSrce->getQueryCount() +
            $this->dbDest->getQueryCount() - $queryCountBeforeSync;
        $this->log(
            "syncOneway() done, total {$rowsSynced} rows synced," .
            " db query $queryCount times.\n"
        );

        return $rowsSynced;
    }


    /**
     * Oneway sync for INSERT/UPDATE, single srce table
     *
     * @param   string  $tableSrce
     * @param   mixed   $tableDest
     * @return  integer     Number of rows synced in srce db.
     */
    protected function syncOnewayTable($tableSrce, $tableDest)
    {
        if (is_array($tableDest)) {
            $i = 0;
            foreach ($tableDest as $v) {
                $i += $this->syncOnewayTable($tableSrce, $v);
            }

            return $i;
        }


        $timestamp = $this->getLastTimestamp($this->dbDest, $tableSrce);
        $timestampColumn = $this->dbSrce->findColumnTs($tableSrce);
        if (empty($timestampColumn)) {
            $message = "Table $tableSrce in source db hasn't timestamp column.";
            $this->log($message);
            throw new \Exception($message);
        }


        // Retrieve data from source db
        $sqlConfig = array(
            'SELECT'    => '*',
            'FROM'      => $tableSrce,
            'ORDERBY'   => "$timestampColumn ASC",
            );
        if (!empty($timestamp)) {
            $timestamp = $this->dbSrce->quoteValue(
                $tableSrce,
                $timestampColumn,
                $timestamp
            );

            // Some db's timestamp have duplicate value, need to use '>=' to
            // avoid some rows been skipped.  But if N rows have same ts, and
            // N > $this->batchSize, it will be endless loop, so use '>' when
            // possible by db type.
            // @codeCoverageIgnoreStart
            if ($this->dbSrce->isTsUnique()) {
                $sqlConfig['WHERE'] = "$timestampColumn > $timestamp";
            } else {
                $sqlConfig['WHERE'] = "$timestampColumn >= $timestamp";
            }
            // @codeCoverageIgnoreEnd
        }
        $sql = $this->dbSrce->generateSql($sqlConfig);
        $rs = $this->dbSrce->SelectLimit($sql, $this->batchSize - $this->batchDone);


        if (empty($rs) || 0 >= $rs->RowCount()) {
            // @codeCoverageIgnoreStart
            return 0;
            // @codeCoverageIgnoreEnd

        } else {
            // Got data, prepare
            $dataSrce = array();
            $lastTimestamp = '';

            while (!$rs->EOF) {
                $ar = $rs->FetchRow();

                // Sybase timestamp is binary format, need convert to string
                // @codeCoverageIgnoreStart
                if ($this->dbSrce->isDbSybase()) {
                    $ar[$timestampColumn] = bin2hex($ar[$timestampColumn]);
                }
                // @codeCoverageIgnoreEnd

                // Remember timestamp, the last one will write to record table later
                $lastTimestamp = strval($ar[$timestampColumn]);

                $dataSrce[] = $ar;
            }
            $dataSrce = $this->dbSrce->convertEncodingResult($dataSrce);


            $rowsSynced = 0;
            $stringUtil = $this->getUtil('StringUtil');
            foreach ((array)$tableDest as $table) {

                // Call data convert method
                $convertFunc = 'convertData' . $stringUtil->toStudlyCaps($tableSrce)
                    . 'To' . $stringUtil->toStudlyCaps($table);

                $dataDest = array();
                if (method_exists($this, $convertFunc)) {
                    // Convert data from source db to data for destination db.
                    // If convert method return empty, will skip this row.
                    foreach ($dataSrce as &$row) {
                        $ar = $this->$convertFunc($row);
                        if (!empty($ar)) {
                            $dataDest[] = $ar;
                        }
                    }
                    unset($row);

                } else {
                    $dataDest = &$dataSrce;
                }


                // Write data to dest db
                if (!empty($dataDest)) {
                    $rowsSynced += count($dataDest);

                    // Row maybe UPDATE or INSERT, so can't use fast prepare
                    foreach ($dataDest as &$row) {
                        $this->dbDest->write($table, $row);
                    }
                    unset($row);

                    $this->log(
                        "syncOnewayTable() $tableSrce -> $table, " .
                        count($dataDest) . " rows wrote."
                    );
                }
            }

            // Notice: If a table need to write to 2 table in dest, and one
            // table write successful and another fail, the last timestamp
            // will still set.
            $this->setLastTimestamp($this->dbDest, $tableSrce, $lastTimestamp);

            return $rowsSynced;
        }
    }
}
