<?php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2008-2009, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2008-05-20
 */


require_once(dirname(__FILE__) . '/fwolflib.php');
require_once(FWOLFLIB . 'class/adodb.php');
require_once(FWOLFLIB . 'func/ecl.php');
require_once(FWOLFLIB . 'func/string.php');
require_once(FWOLFLIB . 'func/uuid.php');


/**
 * Sync data between 2 database source
 *
 * At now, only from 1 to another,
 * cannot do two-side sync yet.
 *
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2008, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-class@gmail.com>
 * @since		2008-05-20
 * @version		$Id$
 * @see			AdoDb
 */
class SyncDbData extends Fwolflib {

	/**
	 * Oneway sync config
	 *
	 * If 1 tbl from source need write to 2 tbl to destination
	 * set destination tbl in queue an array.
	 *
	 * 1 source tbl can only accur once in queue array index,
	 * because timestamp is record by it.
	 *
	 * <code>
	 * srce		Source db profile(array)
	 * dest		Destination db profile(array)
	 * queue = array(
	 * 		tbl_srce => tbl_dest,
	 * 		tbl_srce => array(tbl_dest1, tbl_dest2),
	 * )
	 * </code>
	 *
	 * Will auto call data parse function, if not exist, use original data.
	 * eg: DataConvertTblSrce
	 * This function accept data array retrieve from source,
	 * and return data array will write to destination.
	 *
	 * When destination write done, update timestamp in record tbl.
	 *
	 * Change to assign through function param, here is just a sample.
	 * @var	array
	 */
	//public $aConfigOneway = array();

	/**
	 * Log message
	 * @var	array
	 */
	protected $aLog = array();

	/**
	 * Number of rows have processed
	 * @var	integer
	 */
	protected $iBatchDone = 0;

	/**
	 * Process N rows per run
	 * @var	integer
	 */
	public $iBatchSize = 100;

	/**
	 * Source db profile name
	 * A join of db type, host, name in db config array, '-' splitted.
	 * @var	string
	 */
	protected $sDbProfSrce = '';

	/**
	 * Lock file to avoid run duplicate.
	 *
	 * @var	string
	 */
	public $sLockFile = '/tmp/sync-db-data.lock';

	/**
	 * Name of record table
	 *
	 * Create in destination db.
	 * @var	string
	 */
	public $sTblRecord = 'sync_db_data_record';

	/**
	 * Db object - source
	 * @var object
	 */
	protected $oDbSrce = null;

	/**
	 * Db object - destination
	 * @var object
	 */
	protected $oDbDest = null;


	/**
	 * construct
	 */
	public function __construct() {
		$this->Log('========  ' . date('Y-m-d H:i:s') . '  ========');

		// Check if lockfile exists
		if (file_exists($this->sLockFile)) {
			Ecl("\n========  " . date('Y-m-d H:i:s') . '  ========');
			die("Lock file exists, previous run not end, quit.\n");
		} else
			$this->LockFileCreate();

		// Do check after we know target db
		//$this->ChkTblRecord();

	} // end of func __construct


	/**
	 * destruct, output log message, only when there is some sync happen.
	 */
	public function __destruct() {
		if (0 != $this->iBatchDone)
			foreach ($this->aLog as &$log)
				Ecl($log);

		$this->LockFileDelete();
	} // end of func destruct


	/**
	 * Check and create db connection
	 * @param	array	&$config
	 */
	protected function ChkDbConn(&$config) {
		// Check and connection db
		if (!empty($config['srce'])) {
			$db_srce = $this->DbConn($config['srce']);
			$this->sDbProfSrce = $config['srce']['type']
				. '-' . $config['srce']['host']
				. '-' . $config['srce']['name'];
			$this->oDbSrce = &$db_srce;
		}

		if (!empty($config['dest'])) {
			$db_dest = $this->DbConn($config['dest']);
			$this->oDbDest = &$db_dest;
			// Record tbl was create in destination db
			$this->ChkTblRecord($db_dest);
		}
	} // end of func ChkDbConn


	/**
	 * Check and install record table if not exists
	 * @param	object	$db		Db connection
	 * @param	string	$tbl	Name of record tbl, if empty, use $this->sTblRecord
	 */
	protected function ChkTblRecord($db, $tbl = '')
	{
		if (empty($tbl))
			$tbl = $this->sTblRecord;

		if (!$db->TblExists($tbl)) {
			// Table doesn't exist, create it
			// SQL for Create table diffs from several db
			if ($db->IsDbSybase())
				// Sybase's index was created seperated, not implement now.
				$sql = "
					CREATE TABLE $tbl (
						uuid		CHAR(36)	NOT NULL,
						db_prof		VARCHAR(50) NOT NULL,
						tbl_title	VARCHAR(50) NOT NULL,
						-- Timestamp remembered, for next round
						last_ts		VARCHAR(50)	NOT NULL,
						-- Timestamp for this table
						-- In sybase 'timestamp' must be lower cased
						ts			timestamp	NOT NULL,
						constraint PK_$tbl PRIMARY KEY (uuid)
					)
					";
			elseif ($db->IsDbMysql())
				// ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
				$sql = "
					CREATE TABLE $tbl (
						uuid		CHAR(36)	NOT NULL,
						db_prof		VARCHAR(50) NOT NULL,
						tbl_title	VARCHAR(50) NOT NULL,
						-- Timestamp remembered, for next round
						last_ts		VARCHAR(50)	NOT NULL,
						-- Timestamp for this table
						ts			TIMESTAMP	NOT NULL
							DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
						PRIMARY KEY (uuid),
						INDEX idx_{$tbl}_1 (db_prof, tbl_title)
					);
					";
			else {
				$this->Log('Table record create syntax not implement.');
				$this->Quit();
			}

			// Execute create table sql
			$db->Execute($sql);
			if (0 < $db->ErrorNo())
			{
				$this->Log($db->ErrorNo() . ' - '  . $db->ErrorMsg());
				$this->Log("Log table $tbl doesn't exists and create fail.");
				$this->Quit();
			}
			else {
				// Sybase - create index
				$db->Execute("CREATE INDEX idx_{$tbl}_1 ON
					$tbl(db_prof, tbl_title)
					");

				// Log table create information
				$this->Log("Log table $tbl doesn't exist, create it, done.");
			}
		}
		else
		{
			// Log table exist information
			$this->Log("Log table $tbl already exists.");
		}
	} // end of func ChkTblRecord


	/**
	 * Connect to db, using func defined in Adodb, check error here.
	 *
	 * <code>
	 * $s = array(type, host, user, pass, name, lang);
	 * type is mysql/sybase_ase etc,
	 * name is dbname to select,
	 * lang is db server charset.
	 * </code>
	 *
	 * Useing my extended ADODB class now, little difference when new object.
	 * @var array	$dbprofile	Server config array
	 * @return object			Db connection object
	 */
	protected function DbConn($dbprofile)
	{
		//$conn = DbConn($s);
		$conn = new Adodb($dbprofile);
		$conn->Connect();
		//var_dump($conn);

		if (0 !=$conn->ErrorNo())
		{
			// Display error
			$s = 'ErrorNo: ' . $conn->ErrorNo() . "\n"
				. "ErrorMsg: " . $conn->ErrorMsg();
			$this->Log($s);
			$this->Quit();
		}
		else
			return $conn;
	} // end of func DbConn


	/**
	 * Get last timestamp remembered
	 * @param	$db_dest	Destination db connection, find here
	 * @param	$tbl_srce	Table name of source table.
	 * @return	string		Return '' if no last_ts remembered.
	 * @see		$sDbProfSrce
	 */
	protected function GetLastTs($db_dest, $tbl_srce) {
		$sql = $db_dest->GenSql(array(
			'SELECT' => 'last_ts',
			'FROM' => $this->sTblRecord,
			'WHERE' => array(
				"db_prof = '{$this->sDbProfSrce}'",
				"tbl_title = '$tbl_srce'",
				),
			'LIMIT' => 1,
			));
		$rs = $db_dest->Execute($sql);
		if (0 < $rs->RowCount()) {
			return $rs->fields['last_ts'];
		}
		else {
			return '';
		}
	} // end of func GetLastTs


	/**
	 * Create lock file.
	 *
	 * @return	$this
	 */
	protected function LockFileCreate() {
		if (is_writable($this->sLockFile))
			file_put_contents($this->sLockFile, '');
		return $this;
	} // end of func LockFileCreate


	/**
	 * Delete lock file.
	 *
	 * @return	$this
	 */
	protected function LockFileDelete() {
		if (is_writable($this->sLockFile))
			unlink($this->sLockFile);
		return $this;
	} // end of func LockFileDelete


	/*
	 * Save or output log message, change to save now, output when destruct.
	 * @param	string	$log
	 */
	protected function Log($log)
	{
		//$this->sSummary .= $log;
		//Ecl($log);
		$this->aLog[] = $log;
	} // end of func Log


	/**
	 * Quit this program when error
	 */
	protected function Quit() {
		$this->LockFileDelete();
		die();
	} // end of func Quit


	/**
	 * Set last timestamp, for next round
	 * @param	$db_dest	Destination db connection, write here
	 * @param	$tbl_srce	Table name of source table.
	 * @param	$last_ts	Last timestamp value.
	 * @param	boolean		Operate true or false
	 */
	protected function SetLastTs($db_dest, $tbl_srce, $last_ts) {
		// Find if position exists first, and set gen sql config
		if ('' == $this->GetLastTs($db_dest, $tbl_srce)) {
			// Insert mode
			$ar_conf = array(
				'INSERT' => $this->sTblRecord,
				'VALUES' => array(
					'uuid' => $this->Uuid(),
					'db_prof' => $this->sDbProfSrce,
					'tbl_title' => $tbl_srce,
					'last_ts' => $last_ts
					),
				);
		}
		else {
			// Update mode
			$ar_conf = array(
				'UPDATE' => $this->sTblRecord,
				'SET' => array('last_ts' => $last_ts),
				'WHERE' => array(
					"db_prof = '$this->sDbProfSrce'",
					"tbl_title = '$tbl_srce'",
					),
				'LIMIT' => 1,
				);
		}
		// Execute sql
		$sql = $db_dest->GenSql($ar_conf);
		$rs = $db_dest->Execute($sql);
		if (0 == $db_dest->ErrorNo()) {
			return true;
		}
		else {
			return false;
		}
	} // end of func SetLastTs


	/**
	 * Check if data had been deleted from srce
	 * Caution if dest table's data is ONLY from srce table,
	 * 	if not, you shouldn't use this func.
	 * @param	array	&$config
	 */
	public function SyncChkDel(&$config) {
		if ($this->iBatchDone >= $this->iBatchSize) {
			$this->Log('Data sync not complete, delete check will not run.');
			return;
		}

		$this->ChkDbConn($config);

		// Doing queue
		$i_batch_done = $this->iBatchDone;	// Temp save
		$this->iBatchDone = 0;
		if (!empty($config['queue']) && is_array($config['queue'])) {
			foreach ($config['queue'] as $tbl_srce => $tbl_dest)
				if ($this->iBatchDone < $this->iBatchSize)
					// Notice, $tbl_dest maybe an array
					$this->iBatchDone += $this->SyncChkDelTbl(
						$this->oDbSrce, $this->oDbDest, $tbl_srce, $tbl_dest);
		}

		// Output message
		global $i_db_query_times;
		$this->Log("SyncChkDel done, total {$this->iBatchDone} rows deleted,"
			. " db query(s) $i_db_query_times times.\n");

		// Reset stat data for other operation
		$i_db_query_times = 0;
		$this->iBatchDone += $i_batch_done;	// Temp restore
	} // end of func SyncChkDel


	/**
	 * Check if data had been deleted from srce on a single table
	 * @param	object	$db_srce	Source db connection
	 * @param	object	$db_dest	Destination db connection
	 * @param	string	$tbl_srce	Source table
	 * @param	mixed	$tbl_dest	Destination table(name or array of name)
	 * @return	integer		Number of rows deleted on destination db.
	 */
	public function SyncChkDelTbl($db_srce, $db_dest, $tbl_srce, $tbl_dest) {
		if (is_array($tbl_dest)) {
			$i = 0;
			foreach ($tbl_dest as $dest)
				$i += $this->SyncChkDelTbl($db_srce, $db_dest
					, $tbl_srce, $dest);
			return $i;
		}
		// In below, $tbl_dest is STRING now

		// Get row count from each side
		$i_srce = $db_srce->GetRowCount($tbl_srce);
		$i_dest = $db_dest->GetRowCount($tbl_dest);

		// Need ? compare row count.
		if ($i_srce < $i_dest) {
			// Log check begin
			$s_log = "Delete check: $tbl_srce($i_srce) <- ";
			$s_log .= $tbl_dest . '(' . $i_dest . ')';
			//$this->Log($s_log . ' .');

			// Find unnecessary PK in dest(srce To dest)
			// DataCompare func return PK of rows need to del
			$s_func = 'DataCompare' . StrUnderline2Ucfirst($tbl_srce)
				. 'To' . StrUnderline2Ucfirst($tbl_dest);
			if (method_exists($this, $s_func)) {
				// Got the rows
				$ar_todel = $this->$s_func();
				if (!empty($ar_todel)) {
					// Do del
					$ar_conf = array(
						'DELETE' => $tbl_dest,
						'LIMIT' => 1,
					);

					// Apply PK to WHERE clause
					// Notice: order of pk must same with $ar_todel
					$pk = $this->oDbDest->GetMetaPrimaryKey($tbl_dest);
					if (!is_array($pk))
						$pk = array(0 => $pk);
					foreach ($pk as $key) {
						$ar_conf['WHERE'][] = "$key = "
							. $this->oDbDest->Param($key);
					}
					// Prepare sql
					$sql = $this->oDbDest->GenSqlPrepare($ar_conf);
					$stmt = $this->oDbDest->Prepare($sql);

					// Execute sql
					$this->oDbDest->EncodingConvert($ar_todel);
					try {
						$this->oDbDest->Execute($stmt, $ar_todel);
					}
					catch (Exception $e) {
						// Show error message ?
						$this->oDbDest->RollbackTrans();
						$s_log .= ' fail(1) .';
						$this->Log($s_log);
						$this->Log("\tError when execute del sql on $tbl_dest .");
						return -100;
					}
					// Any error ?
					if (0 != $this->oDbDest->ErrorNo()) {
						$s_log .= ' fail(2) .';
						$this->Log($s_log);
						// Log to error log file
						$this->Log('	ErrorNo: ' . $this->oDbDest->ErrorNo()
							. "\n\tErrorMsg: " . $this->oDbDest->ErrorMsg()
							);
						$this->oDbDest->RollbackTrans();
						return -100;
					}
					else {
						$this->oDbDest->CommitTrans();
						// Ok, count affected rows
						// But Affectd_Rows can't use with prepare
						$i = count($ar_todel);
						$s_log .= ", $i rows deleted.";
						$this->Log($s_log);
						return $i;
					}
					// Sql execute completed
				}

				// :DEBUG: Make msg always printed
				//$this->iBatchDone ++;
			}
			else {
				// Need compare func
				$s_log .= ' fail .';
				$this->Log($s_log);
				$this->Log("	Compare func needed: $tbl_srce To $tbl_dest .");
				return 0;
			}
		}
	} // end of func SyncChkDelTbl


	/**
	 * Do oneway sync
	 * @param	array	&$config
	 */
	public function SyncOneway(&$config) {
		$this->ChkDbConn($config);

		// Doing queue
		$i_batch_done = $this->iBatchDone;	// Temp save
		$this->iBatchDone = 0;
		if (!empty($config['queue']) && is_array($config['queue'])) {
			foreach ($config['queue'] as $tbl_srce => $tbl_dest)
				if ($this->iBatchDone < $this->iBatchSize)
					// Notice, $tbl_dest maybe an array
					$this->iBatchDone += $this->SyncOnewayTbl(
						$this->oDbSrce, $this->oDbDest, $tbl_srce, $tbl_dest);
		}
		// Output message
		global $i_db_query_times;
		$this->Log("SyncOneway done, total {$this->iBatchDone} rows wrote,"
			. " db query(s) $i_db_query_times times.\n");

		// Reset stat data for other operation
		$i_db_query_times = 0;
		$this->iBatchDone += $i_batch_done;	// Temp restore
	} // end of func SyncOneway


	/**
	 * Do oneway sync on a single table
	 * @param	object	$db_srce	Source db connection
	 * @param	object	$db_dest	Destination db connection
	 * @param	string	$tbl_srce	Source table
	 * @param	mixed	$tbl_dest	Destination table(name or array of name)
	 * 								Main dest tbl should define before others.
	 * @return	integer		Number of rows write to destination db.
	 */
	public function SyncOnewayTbl($db_srce, $db_dest, $tbl_srce, $tbl_dest) {
		// Prepare
		$last_ts = $this->GetLastTs($db_dest, $tbl_srce);
		$col_ts = $db_srce->FindColTs($tbl_srce);
		if (empty($col_ts)) {
			$this->Log("Table $tbl_srce in source db hasn't timestamp column.");
			$this->Quit();
		}

		// Retrieve data from source db
		$ar_conf = array(
			'SELECT' => '*',
			'FROM' => $tbl_srce,
			//'LIMIT' => $this->iBatchSize,
			'ORDERBY' => "$col_ts asc",
			);
		if (!empty($last_ts)) {
			$last_ts = $db_srce->QuoteValue($tbl_srce, $col_ts, $last_ts);
			// Some db's timestamp have duplicate value, use '>=' to avoid some rows been skipped.
			// :NOTICE: If N rows have same ts, and N > $this->iBatchSize, it will endless loop.
			// So use '>' when possible.
			if ($db_srce->IsTsUnique())
				$ar_conf['WHERE'] = "$col_ts > $last_ts";
			else
				$ar_conf['WHERE'] = "$col_ts >= $last_ts";
		}
		$sql = $db_srce->GenSql($ar_conf);
		$rs = $db_srce->SelectLimit($sql, $this->iBatchSize - $this->iBatchDone);

		if (!empty($rs) && 0 < $rs->RowCount()) {
			// Got data, prepare write to destination db
			// Multi-rows write mode
			$ar_rows = array();
			$last_ts = '';	// Last ts to be remembered
			while (!$rs->EOF) {
				// Get one data row, and convert it to dest format
				$ar = $rs->FetchRow();

                // Php-sybase in ubuntu intrepid use mssql wrongly, so read timestamp
                // error way, need to correct, and before encoding convert.
                if (16 != strlen($ar[$col_ts]))
                        $ar[$col_ts] = bin2hex($ar[$col_ts]);
                // Remember timestamp, the last one will write to record table below
                $last_ts = strval($ar[$col_ts]);

				$ar = $db_srce->EncodingConvert($ar);

				// Add data from source db to queue, will convert later
				if (!empty($ar))
					$ar_rows[] = $ar;
			}
			// Maybe any reason cause no data in $ar_rows
			if (empty($ar_rows))
				return 0;

			// Write data rows to db
			//print_r($ar_rows);
			// If $tbl_dest is string, convert to array
			if (!is_array($tbl_dest))
				$tbl_dest = array($tbl_dest);
			$i_batch_done = 0;
			// Loop as if $tbl_dest is multi table
			foreach ($tbl_dest as &$tbl_dest_single) {
				$i_batch_done_single = 0;
				// Important: call data convert function
				$s_func = 'DataConvert' . StrUnderline2Ucfirst($tbl_srce)
					. 'To' . StrUnderline2Ucfirst($tbl_dest_single);
				$ar_dest = array();
				if (method_exists($this, $s_func)) {
					// Convert data from source db to data for destination db
					foreach ($ar_rows as &$ar_row) {
						$ar = $this->$s_func($ar_row);
						if (!empty($ar))
							$ar_dest[] = $ar;
					}
				}
				else
					// No data convert needed
					$ar_dest = &$ar_rows;

				// If got final data, write to db
				if (!empty($ar_dest)) {
					// Must loop manually, because each row's update/insert is difference
					foreach ($ar_dest as &$ar_dest_row) {
						$j = $db_dest->Write($tbl_dest_single, $ar_dest_row);
						if (0 < $j) {
							$i_batch_done += $j;
							$i_batch_done_single += $j;
						}
					}
					//$db_dest->Write($tbl_dest, $ar_rows);
				}

				// Log single table sync message
				if (0 < $i_batch_done_single)
					$this->Log("SyncOnewayTbl $tbl_srce -> $tbl_dest_single, "
						. "$i_batch_done_single rows wrote.");
			}

			// Notice, if a table need to write to 2 table in dest,
			// and 1 table write successful and another fail, it will still set last ts.
			if (0 <= $i_batch_done)
				$this->SetLastTs($db_dest, $tbl_srce, $last_ts);
			return $i_batch_done;
		}
		else
			return 0;
	} // end of func SyncOnewayTbl


	/**
	 * Trim a string, used in array_walk, so param need to be reference
	 * @param	string	&$str
	 * @return	string
	 */
	public function Trim(&$str)
	{
		$str = trim($str);
		return $str;
	} // end of func Trim


	/**
	 * Generate an UUID, can be re-write by sub class
	 * @return	string
	 */
	protected function Uuid() {
		return Uuid();
	} // end of func Uuid


} // end of class SyncDbData
?>
