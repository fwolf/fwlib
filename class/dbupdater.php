<?php
/**
* @package      fwolflib
* @copyright    Copyright 2006, Fwolf
* @author       Fwolf <fwolf.aide@gmail.com>
*/

require_once('adodb/adodb.inc.php');

//// From fwolflib r12, Don't modify it outside fwolflib ! ////

/**
* Database mantance & update tools
* Used in develop or sync multi databases' structure
*
* It use a table to 'remember' every update done or to be done to db
* and, all update to db here is defined to 'sql' lang format,
* I hope this can meets all my needs.
*
* All updates MUST be done by step order, so when a last-update-id
* is set, it's consided all updates before last-update-id is done.
*
* @package    fwolflib
* @copyright  Copyright 2006, Fwolf
* @author     Fwolf <fwolf.aide@gmail.com>
* @since      2006-12-10
* @access     public
* @version    $Id$
*/
class DbUpdater
{
	/**
	 * Charset of database
	 * If charset of db diff from os, do convert when execute sql.
	 * @var	string
	 * @access	public
	 */
	var $mCharsetDb = '';

	/**
	 * Charset of operation system
	 * @var string
	 * @access	public
	 * @see	$mCharsetDb
	 */
	var $mCharsetOs = '';
	
	/**
	 * Db connection object
	 * @var object
	 * @access	private
	 */
	var $mDb;

	/**
	 * Last update id
	 * @var int
	 * @access	public
	 */
	var $mLastId = 0;

	/**
	 * Last done update id
	 * @var	int
	 * @access	public
	 */
	var $mLastDoneId = 0;

	/**
	 * Db server information array
	 * 	Array item: host, user, pass, name, type.
	 * @var	array
	 * @access	private
	 */
	var $mServer = array();

	/**
	 * Summary text
	 * @var string
	 * @access public
	 */
	var $mSummary = '';

	/**
	 * Table to save modifications and logs
	 * @var	string
	 * @access	public
	 */
	var $mTblLog = 'dbupdater_log';


	/**
	 * Construct function
	 * @access public
	 * @param	array	$server	Db server information
	 */
	function __construct($server=array())
	{
		if (!empty($server))
			$this->SetDatabase($server);
		
		// Check and install log table
		$this->CheckLogTbl();
	} // end of func construct


	/**
	 * Check and install log table if not exists
	 * @access	public
	 */
	public function CheckLogTbl()
	{
		$logtbl_not_exists = false;
		if ('sybase' == $this->mServer['type'] || 'sybase_ase' == $this->mServer['type'])
		{
			$sql = "select count(1) as c from sysobjects where name = '$this->mTblLog' and type = 'U'";
			$rs = $this->mDb->Execute($sql);
			if (0 == $rs->fields['c'])
				$logtbl_not_exists = true;
		}
		else
		{
			$sql = "select 1 from $this->mTblLog";
			$rs = $this->mDb->Execute($sql);
			if (0 == $this->mDb->ErrorNo())
				$logtbl_not_exists = true;
		}
		
		if (true == $logtbl_not_exists)
		{
			// Table doesn't exist, create it
			// 'sql' is a reserved word, so sqtext is used.
			// SQL for Create table diffs from several db
			if ('sybase' == $this->mServer['type'] || 'sybase_ase' == $this->mServer['type'])
				$sql = "
CREATE TABLE $this->mTblLog (
	id		numeric(8) NOT NULL,
	comment	varchar(200) NOT NULL,
	done	int default 0,	-- 0:not do, -1:error, 1:done ok
	sqltext	text,
	ts		timestamp NOT NULL,
	constraint PK_$this->mTblLog PRIMARY KEY (id)
)
					";
			else
				$sql = "
CREATE TABLE $this->mTblLog (
	id		int(8) NOT NULL auto_increment,
	comment	varchar(200) NOT NULL,
	done	tinyint(1) default 0,	-- 0:not do, -1:error, 1:done ok
	sqltext	text,
	ts		timestamp NOT NULL default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (id)
) ENGINE=MyISAM AUTO_INCREMENT=1
					";
			$this->mDb->Execute($sql);
			if (0 < $this->mDb->ErrorNo())
			{
				echo $this->mDb->ErrorNo() . ' - '  . $this->mDb->ErrorMsg() . "\n";
				die("Log table $this->mTblLog doesn't exist and create failed.\n");
			}

			// Log table create information
			$this->Log("Log table $this->mTblLog doesn't exist, create it, done.\n");
		}
		else
		{
			// Log table exist information
			$this->Log("Log table $this->mTblLog already exists.\n");
		}

		// Get last-done-id for later usage
		$this->GetLastDoneId();
	} // end of func CheckLogTbl
	  

	/**
	 * 获得数据库连接
	 * @access	private
	 * @param	array	$server
	 * @return object
	 */
	private function &DbConn($server)
	{
		global $ADODB_FETCH_MODE;
		
		//ADODB设定
		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		
		try
		{
			$conn = &ADONewConnection($server['type']);
			//$conn->debug = true;
			$conn->Connect($server['host'],
				$server['user'],
				$server['pass'],
				$server['name']);
			//针对mysql 4.1以上，UTF8编码的数据库，需要在连接后指定编码
			//$conn->Execute('set names "utf8"');
			if ('mysql' == $server['type']) $conn->Execute('set names "utf8"');
		}
		catch (Exception $e)
		{
			//var_dump($e);
			adodb_backtrace($e->getTrace());
			//echo $e;
			exit();
		}
		return $conn;
	} // end of func DbConn


	/**
	 * Do updates according to update-records in log table
	 * @access	public
	 */
	public function DoUpdate()
	{
		$sql = "SELECT id, sqltext FROM $this->mTblLog where done<>1 order by id asc";
		$rs = $this->mDb->Execute($sql);
		if ($rs->EOF)
		{
			$this->Log("No un-done update to do.\n");
		}
		else
		{
			// Do these updates one by one
			$ar = $rs->GetArray();
			$i_total = count($ar);
			for ($i=0; $i<$i_total; $i++)
			{
				$id = $ar[$i]['id'];
				$sqltext = stripslashes($ar[$i]['sqltext']);
				// Do on update
				// Cancel transaction because some ddl sql can't use in trans.
				//$this->mDb->StartTrans();
				$this->mDb->Execute($sqltext);
				
				// Bad sybase support, select db will got errormsg
				// Avoid sybase errormsg like: Changed database context to 'jygl'
				if ((0 == strlen($this->mDb->ErrorMsg()) && 0 == $this->mDb->ErrorNo()) || ('Changed database context t' == substr($this->mDb->ErrorMsg(), 0, 26)))
				{
					$this->Log("Update id $id done successful.\n");
					$this->SetUpdateDone($id, 1);
					//$this->mDb->CompleteTrans();
				}
				else
				{
					$this->Log("Update id $id done failed.\n");
					$this->Log($this->mDb->ErrorNo() . '-' . $this->mDb->ErrorMsg() . "\n");
					//$this->mDb->CompleteTrans();
					$this->SetUpdateDone($id, -1);
					$this->Summary();
					die("Doing update aborted because of failed.\n");
				}
			}
			// Log
			$this->Log("Total $i/$i_total updates done.\n");
		}
		return true;
	} // end of func DoUpdate


	/**
	 * Get last update id, whether done or not
	 * @access	public
	 * @return	int
	 */
	public function GetLastId()
	{
		$sql = "select id from $this->mTblLog order by id desc";
		$rs = $this->mDb->SelectLimit($sql, 1);
		if ($rs->EOF)
			$id = 0;
		else
			$id = $rs->fields['id'];
		$this->mLastId = $id;
		return $id;
	} // end of func GetLastId


	/**
	 * Get last done update id
	 * @access	public
	 * @return	int
	 */
	public function GetLastDoneId()
	{
		$sql = "select id from $this->mTblLog where done=1 order by id desc";
		$rs = $this->mDb->SelectLimit($sql, 1);
		if ($rs->EOF)
		{
			$id = 0;
		}
		else
		{
			$id = $rs->fields['id'];
		}
		$this->mLastDoneId = $id;
		return $id;
	} // end of func GetLastDoneId


	/**
	 * Return if an update is already done
	 * Use cached $mLastDoneId if it's non-zero, doesn't retrieve id from db.
	 * @access	public
	 * @param	int	$id
	 * @return	boolean
	 * @see		$mLastDoneId
	 * @see		GetLastDoneId()
	 */
	public function IfDone($id)
	{
		if (0 == $this->mLastDoneId)
			$this->GetLastDoneId();
		return ($id <= $this->mLastDoneId);
	} // end of func IfDone


	/*
	 * Save log
	 * @access	private
	 * @param	string	$log
	 */
	private function Log($log)
	{
		$this->mSummary .= $log;
	} // end of func Log
	
	
	/**
	 * Accept database information from outside class
	 *	Didnot validate data send in.
	 *	And connect to db after store infomation.
	 * @access	public
	 * @var	array	$server	array items: host, user, pass, name, type
	 */
	public function SetDatabase($server)
	{
		if (!empty($server) && is_array($server))
		{
			$this->mServer = $server;
			$this->mDb = $this->DbConn($this->mServer);
		}
	} // end of func SetDatabase


	/**
	 * Set a update step, but doesn't execute it
	 * Use $mLastId if it's non-zero.
	 * @access	public
	 * @param	int	$id
	 * @param	string	$comment
	 * @param	string	$sqltext
	 * @see		$mLastId
	 */
	public function SetUpdate($id, $comment, $sqltext)
	{
		if (0 == $this->mLastId)
			$this->GetLastId();
		// Update can't be recoverd, so only worked when $id > $mLastId
		// And notice that update id MUST be assigned order ASC.
		if ($id > $this->mLastId)
		{
			$comment = addslashes($comment);
			$sqltext = addslashes($sqltext);
			$sql = "INSERT INTO $this->mTblLog (id, comment, sqltext) VALUES ($id, '$comment', '$sqltext')";
			// Check if iconv for sqltext is needed
			if ($this->mCharsetDb != $this->mCharsetOs)
				$sql = mb_convert_encoding($sql, $this->mCharsetDb, $this->mCharsetOs);
			//
			$this->mDb->Execute($sql);
			if (0 != $this->mDb->ErrorNo())
			{
				echo $this->mDb->ErrorNo() . '-' . $this->mDb->ErrorMsg() . "\n";
				die("Set update failed.\n");
			}
			else
			{
				$this->mLastId ++;
				$this->Log("Update id $id saved.\n");
			}
		}
	} // end of func SetUpdate


	/**
	 * Set a update record's status done or not or failed
	 * Didn't validate $id or $status.
	 * @access	private
	 * @param	int		$id		Update id
	 * @param	int		$status Update id's status(0/1/-1)
	 */
	private function SetUpdateDone($id, $status)
	{
		if (-1 == $status)
			echo("Error when do update $id, {$this->mDb->ErrorNo()}:{$this->mDb->ErrorMsg()}\n");
		$sql = "UPDATE $this->mTblLog set done=$status where id=$id";
		$this->mDb->Execute($sql);
		//if (0 == $this->mDb->ErrorNo() && 0 == strlen($this->mDb->ErrorMsg()))
		if ((0 == strlen($this->mDb->ErrorMsg()) && 0 == $this->mDb->ErrorNo()) || ('Changed database context t' == substr($this->mDb->ErrorMsg(), 0, 26)))
			$this->Log("Update id $id's done is set to $status.\n");
		else
			die("Failed when set update id $id's done status.({$this->mDb->ErrorNo()}:{$this->mDb->ErrorMsg()})\n");
	} // end of func SetUpdateDone


	/**
	 * Print or write summary text of the whole backup process
	 * @access	public
	 */
	public function Summary()
	{
		echo $this->mSummary . "\n";
	} // end of func Summary

	
} // end of class DbUpdater
?>
