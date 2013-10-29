<?php
namespace Fwlib\Db;

use Fwlib\Db\AbstractDbClient;
use Fwlib\Util\Env;

/**
 * Db schema synchronize & update tools
 *
 * Define schema operate SQL in array, and track their execute status with a
 * log table.
 *
 * All SQL MUST execute by defined order, so when a SQL with maximum id is
 * done, all SQL before it was done.
 *
 * Execute of SQL will stop when got error, after SQL fixed, next execute will
 * automatic clear error SQL, or update it.
 *
 * SQL identify by id, so don't change them except you know what you are
 * doing. Id can start from 0 or 1, but can only assign by accending order.
 *
 * If there are too many schema SQL, put altogether in one define file will
 * cost more memory and i/o. In this situation, you can split SQL define file
 * to small ones by step(eg: 100), then use getLastIdDone() to help check
 * which file to require.
 *
 * Other tools similar:
 * @link http://xml2ddl.berlios.de/
 *
 * @package     Fwlib\Db
 * @copyright   Copyright 2006-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2006-12-10
 */
class SyncDbSchema extends AbstractDbClient
{
    /**
     * Last schema SQL id
     *
     * @var int
     */
    public $lastId = -1;

    /**
     * Last schema SQL id which is executed
     *
     * @var int
     */
    public $lastIdDone = -1;

    /**
     * Table to track schema SQL execute status
     *
     * It should not include space in table name.
     *
     * In running product environment, if this table name changed, remember to
     * rename corresponding table in dbms.
     *
     * @var string
     */
    public $logTable = 'log_sync_db_schema';


    /**
     * Constructor
     *
     * @param   array   $dbProfile
     * @param   string  $logTable
     */
    public function __construct($dbProfile = array(), $logTable = null)
    {
        parent::__construct($dbProfile);

        if (!empty($logTable)) {
            $this->logTable = $logTable;
        }

        $this->checkLogTable();
    }


    /**
     * Check and create log table if not exists
     */
    public function checkLogTable()
    {
        $table = &$this->logTable;

        if (! $this->db->isTableExist($table)) {

            // SQL for Create table diffs by db type
            // @codeCoverageIgnoreStart
            if ($this->db->isDbSybase()) {
                $sql = "
CREATE TABLE $table (
    id      INT NOT NULL,
    done    INT DEFAULT 0,  /* 0:not do, -1:error, 1:done ok */
    sqltext TEXT,
    ts      TIMESTAMP NOT NULL,
    CONSTRAINT PK_$table PRIMARY KEY (id)
)
";
            } elseif ($this->db->isDbMysql()) {
                $sql = "
CREATE TABLE $table (
    id      INT(8) NOT NULL,
    done    TINYINT DEFAULT 0,   /* 0:not do, -1:error, 1:done ok */
    sqltext TEXT,
    ts      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
)
";
            } else {
                $sql = "
CREATE TABLE $table (
    id      INT NOT NULL,
    done    INT DEFAULT 0,   /* 0:not do, -1:error, 1:done ok */
    sqltext TEXT,
    PRIMARY KEY (id)
)
";
            }
            // @codeCoverageIgnoreEnd

            $this->db->execute($sql);
            if (0 < $this->db->errorNo()) {
                // @codeCoverageIgnoreStart
                $this->log(
                    $this->getDbError() . PHP_EOL .
                    "Log table $table doesn't exists and create fail."
                );
                exit;
                // @codeCoverageIgnoreEnd
            }

            $this->log("Log table $table doesn't exists, create it, done.");

        } else {
            $this->log("Log table $table already exists.");

            // Get last-done-id for later usage
            $this->getLastIdDone();
        }
    }


    /**
     * Del SQL from the error one
     *
     * All SQL after the error one(will be un-executed) will be deleted,
     * ignore their execute status. This is good for debug, if you got an
     * error SQL, just fix it and call sync script again.
     */
    public function delErrorSql()
    {
        $sql = "SELECT id FROM $this->logTable WHERE done=-1 ORDER BY id ASC";
        $rs = $this->db->SelectLimit($sql, 1);

        if (!$rs->EOF) {
            // Del SQL and SQL after it
            $id = $rs->fields['id'];
            $sql = "DELETE FROM {$this->logTable} WHERE id >= $id";
            $rs = $this->db->Execute($sql);

            // Check result, should be greater than 0
            $i = $this->db->Affected_Rows();
            $this->log("Clear $i SQL start from failed SQL $id.");
        }
    }


    /**
     * Execute SQLs
     */
    public function execute()
    {
        // Clear previous failed SQL
        $this->delErrorSql();


        $sql = "SELECT id, sqltext FROM $this->logTable WHERE done<>1 ORDER BY id ASC";
        $rs = $this->db->Execute($sql);

        $cntDone = 0;
        while (!$rs->EOF) {
            $id = $rs->fields['id'];
            $sql = stripslashes($rs->fields['sqltext']);

            // Some DDL SQL can't use transaction, so do raw execute.
            $this->db->execute($sql);

            // Bad sybase support, select db will got errormsg, eg:
            // Changed database context to 'db_name'
            // @codeCoverageIgnoreStart
            if ((0 == $this->db->errorNo()
                    && 0 == strlen($this->db->errorMsg()))
                || ('Changed database context t' ==
                    substr($this->db->errorMsg(), 0, 26))
            // @codeCoverageIgnoreEnd
            ) {
                $this->log("Execute SQL $id successful.");
                $this->setSqlStatus($id, 1);
                $this->lastIdDone = $id;

            } else {
                $this->log("Execute SQL $id failed.");
                $this->log($this->getDbError());

                $this->setSqlStatus($id, -1);
                $this->log("Execute abort.");
                return;
            }

            $cntDone ++;
            $rs->MoveNext();
        }

        if (0 == $cntDone) {
            $this->log('No un-done SQL to do.');
        } else {
            $this->log("Total $cntDone SQL executed successful.");
        }
    }


    /**
     * Get friendly db error msg
     *
     * @return  string
     */
    protected function getDbError()
    {
        return 'Error ' . $this->db->errorNo() . ': '  . $this->db->errorMsg();
    }


    /**
     * Get id of last SQL, ignore their execute status
     *
     * @return  int
     */
    public function getLastId()
    {
        $sql = "SELECT id FROM $this->logTable ORDER BY id DESC";
        $rs = $this->db->SelectLimit($sql, 1);

        if ($rs->EOF) {
            $id = -1;
        } else {
            $id = $rs->fields['id'];
        }

        $this->lastId = $id;
        return $id;
    }


    /**
     * Get id of last successful executed sql
     *
     * @return  int
     */
    public function getLastIdDone()
    {
        $sql = "SELECT id FROM $this->logTable WHERE done=1 ORDER BY id DESC";
        $rs = $this->db->SelectLimit($sql, 1);

        if ($rs->EOF) {
            $id = -1;
        } else {
            $id = $rs->fields['id'];
        }

        $this->lastIdDone = $id;
        return $id;
    }


    /**
     * Print log message
     *
     * @param   string  $msg
     * @param   boolean $newline
     */
    public function log($msg = '', $newline = true)
    {
        if ($newline) {
            $msg = Env::ecl($msg, true);
        }

        echo $msg;
    }


    /**
     * Write sql to log table, without execute
     *
     * This method will call directly in schema SQL define file, one call for
     * one SQL, so it's hard to use db prepare for speed optimize, and it's a
     * little over design too.
     *
     * @param   int $id
     * @param   string  $sqltext
     */
    public function setSql($id, $sqltext)
    {
        if (-1 == $this->lastId) {
            $this->getLastId();
        }

        // Will not overwrite exists id.
        if ($id > $this->lastId) {
            $sqltext = addslashes($sqltext);
            $sqltext = $this->db->convertEncodingSql($sqltext);

            $sql = "INSERT INTO $this->logTable (id, sqltext)
VALUES ($id, '$sqltext')";
            $this->db->Execute($sql);

            if (0 != $this->db->errorNo()) {
                // Should not occur
                // @codeCoverageIgnoreStart
                $this->log($this->getDbError());
                $this->log('Store SQL failed.');
                exit;
                // @codeCoverageIgnoreEnd

            } else {
                $this->lastId = $id;
            }
        }
    }


    /**
     * Set status of a SQL stored in log table
     *
     * @param   int     $id
     * @param   int     $status {0: not_executed, 1: execute_ok, -1: execute_fail}
     */
    protected function setSqlStatus($id, $status)
    {
        $sql = "UPDATE $this->logTable SET done=$status WHERE id=$id";
        $this->db->Execute($sql);
    }
}
