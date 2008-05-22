<?php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2006-2008, Fwolf
 * @author		Fwolf <fwolf.aide-fwolflib-class@gmail.com>
 */

require_once('fwolflib/class/adodb.php');
require_once('fwolflib/func/env.php');

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
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2006-2008, Fwolf
 * @author		Fwolf <fwolf.aide-fwolflib-class@gmail.com>
 * @since		2006-12-10
 * @version		$Id$
 */
class DbUpdater
{
	/**
	 * Db server information array
	 * 	Array item: host, user, pass, name, type.
	 * @var	array
	 */
	private $aServer = array();

	/**
	 * Last update id
	 * @var int
	 */
	public $iLastId = 0;

	/**
	 * Last done update id
	 * @var	int
	 */
	public $iLastDoneId = 0;

	/**
	 * Charset of database
	 * If charset of db diff from os, do convert when execute sql.
	 * @var	string
	 */
	public $sCharsetDb = '';

	/**
	 * Charset of operation system
	 * @var string
	 * @see	$sCharsetDb
	 */
	public $sCharsetOs = '';
	
	/**
	 * Summary text
	 * @var string
	 */
	public $sSummary = '';

	/**
	 * Table to save modifications and logs
	 * 
	 * If u want to change log table name in use(had log some sql already)
	 * remember to rename table in database also.
	 * @var	string
	 */
	public $sTblLog = 'dbupdater_log';


	/**
	 * Db connection object
	 * @var object
	 */
	public $oDb;

	/**
	 * Construct function
	 * @access public
	 * @param	array	$server	Db server information
	 */
	function __construct($dbserver=array())
	{
		if (!empty($dbserver))
			$this->SetDatabase($dbserver);
		
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
		if ('sybase' == $this->aServer['type'] || 
			'sybase_ase' == $this->aServer['type'])
		{
			$sql = "select count(1) as c from sysobjects where name = '$this->sTblLog' and type = 'U'";
			$rs = $this->oDb->Execute($sql);
			if (0 == $rs->fields['c'])
				$logtbl_not_exists = true;
		}
		elseif ('mysql' == $this->aServer['type'] || 
				'mysqli' == $this->aServer['type'])
		{
			$sql = "SHOW TABLES LIKE '$this->sTblLog'";
			$rs = $this->oDb->Execute($sql);
			if (0 == $rs->RowCount())
				$logtbl_not_exists = true;
		}
		else
		{
			$sql = "select 1 from $this->sTblLog";
			$rs = $this->oDb->Execute($sql);
			if (0 == $this->oDb->ErrorNo())
				$logtbl_not_exists = true;
		}
		
		if (true == $logtbl_not_exists)
		{
			// Table doesn't exist, create it
			// 'sql' is a reserved word, so sqtext is used.
			// SQL for Create table diffs from several db
			if ('sybase' == $this->aServer['type'] || 'sybase_ase' == $this->aServer['type'])
				$sql = "
CREATE TABLE $this->sTblLog (
	id		numeric(8) NOT NULL,
	comment	varchar(200) NOT NULL,
	done	int default 0,	-- 0:not do, -1:error, 1:done ok
	sqltext	text,
	ts		timestamp NOT NULL,
	constraint PK_$this->sTblLog PRIMARY KEY (id)
)
					";
			else
				// :DELETED: ) ENGINE=MyISAM AUTO_INCREMENT=1
				$sql = "
CREATE TABLE $this->sTblLog (
	id		int(8) NOT NULL auto_increment,
	comment	varchar(200) NOT NULL,
	done	tinyint(1) default 0,	-- 0:not do, -1:error, 1:done ok
	sqltext	text,
	ts		timestamp NOT NULL default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (id)
) AUTO_INCREMENT=1
					";
			$this->oDb->Execute($sql);
			if (0 < $this->oDb->ErrorNo())
			{
				echo $this->oDb->ErrorNo() . ' - '  . $this->oDb->ErrorMsg() . "\n";
				die("Log table $this->sTblLog doesn't exists and create fail.\n");
			}

			// Log table create information
			$this->Log("Log table $this->sTblLog doesn't exists, create it, done.\n");
		}
		else
		{
			// Log table exist information
			$this->Log("Log table $this->sTblLog already exists.\n");
		}

		// Get last-done-id for later usage
		$this->GetLastDoneId();
	} // end of func CheckLogTbl
	  

	/**
	 * 获得数据库连接
	 * @param	array	$server
	 * @return object
	 */
	private function &DbConn($server)
	{
		$conn = &new Adodb($server);
		$conn->Connect();
		return $conn;
	} // end of func DbConn


	/**
	 * Do updates according to update-records in log table
	 */
	public function DoUpdate()
	{
		$sql = "SELECT id, sqltext FROM $this->sTblLog where done<>1 order by id asc";
		$rs = $this->oDb->Execute($sql);
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
				//$this->oDb->StartTrans();
				$this->oDb->Execute($sqltext);
				
				// Bad sybase support, select db will got errormsg
				// Avoid sybase errormsg like: Changed database context to 'jygl'
				if ((0 == strlen($this->oDb->ErrorMsg()) && 0 == $this->oDb->ErrorNo()) || ('Changed database context t' == substr($this->oDb->ErrorMsg(), 0, 26)))
				{
					$this->Log("Update id $id done successful.\n");
					$this->SetUpdateDone($id, 1);
					//$this->oDb->CompleteTrans();
				}
				else
				{
					$this->Log("Update id $id done failed.\n");
					$this->Log($this->oDb->ErrorNo() . '-' . $this->oDb->ErrorMsg() . "\n");
					//$this->oDb->CompleteTrans();
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
		$sql = "select id from $this->sTblLog order by id desc";
		$rs = $this->oDb->SelectLimit($sql, 1);
		if ($rs->EOF)
			$id = 0;
		else
			$id = $rs->fields['id'];
		$this->iLastId = $id;
		return $id;
	} // end of func GetLastId


	/**
	 * Get last done update id
	 * @access	public
	 * @return	int
	 */
	public function GetLastDoneId()
	{
		$sql = "select id from $this->sTblLog where done=1 order by id desc";
		$rs = $this->oDb->SelectLimit($sql, 1);
		if ($rs->EOF)
		{
			$id = 0;
		}
		else
		{
			$id = $rs->fields['id'];
		}
		$this->iLastDoneId = $id;
		return $id;
	} // end of func GetLastDoneId


	/**
	 * Return if an update is already done
	 * Use cached $iLastDoneId if it's non-zero, doesn't retrieve id from db.
	 * @access	public
	 * @param	int	$id
	 * @return	boolean
	 * @see		$iLastDoneId
	 * @see		GetLastDoneId()
	 */
	public function IfDone($id)
	{
		if (0 == $this->iLastDoneId)
			$this->GetLastDoneId();
		return ($id <= $this->iLastDoneId);
	} // end of func IfDone


	/*
	 * Save log
	 * @access	private
	 * @param	string	$log
	 */
	private function Log($log)
	{
		$this->sSummary .= $log;
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
			$this->aServer = $server;
			$this->oDb = &$this->DbConn($this->aServer);
		}
	} // end of func SetDatabase


	/**
	 * Set a update step, but doesn't execute it
	 * Use $iLastId if it's non-zero.
	 * @access	public
	 * @param	int	$id
	 * @param	string	$comment
	 * @param	string	$sqltext
	 * @see		$iLastId
	 */
	public function SetUpdate($id, $comment, $sqltext)
	{
		if (0 == $this->iLastId)
			$this->GetLastId();
		// Update can't be recoverd, so only worked when $id > $iLastId
		// And notice that update id MUST be assigned order ASC.
		if ($id > $this->iLastId)
		{
			$comment = addslashes($comment);
			$sqltext = addslashes($sqltext);
			$sql = "INSERT INTO $this->sTblLog (id, comment, sqltext) VALUES ($id, '$comment', '$sqltext')";
			// Check if iconv for sqltext is needed
			if ($this->sCharsetDb != $this->sCharsetOs)
				$sql = mb_convert_encoding($sql, $this->sCharsetDb, $this->sCharsetOs);
			//
			$this->oDb->Execute($sql);
			if (0 != $this->oDb->ErrorNo())
			{
				echo $this->oDb->ErrorNo() . '-' . $this->oDb->ErrorMsg() . "\n";
				die("Set update failed.\n");
			}
			else
			{
				$this->iLastId ++;
				$this->Log("Update id $id saved.\n");
			}
		}
	} // end of func SetUpdate


	/**
	 * Set a update record's status done or not or failed
	 * Didn't validate $id or $status.
	 * @param	int		$id		Update id
	 * @param	int		$status Update id's status(0/1/-1)
	 */
	private function SetUpdateDone($id, $status)
	{
		if (-1 == $status)
			echo("Error when do update $id, {$this->oDb->ErrorNo()}:{$this->oDb->ErrorMsg()}\n");
		$sql = "UPDATE $this->sTblLog set done=$status where id=$id";
		$this->oDb->Execute($sql);
		//if (0 == $this->oDb->ErrorNo() && 0 == strlen($this->oDb->ErrorMsg()))
		if ((0 == strlen($this->oDb->ErrorMsg()) && 0 == $this->oDb->ErrorNo()) || ('Changed database context t' == substr($this->oDb->ErrorMsg(), 0, 26)))
			$this->Log("Update id $id's done is set to $status.\n");
		else
			die("Failed when set update id $id's done status.({$this->oDb->ErrorNo()}:{$this->oDb->ErrorMsg()})\n");
	} // end of func SetUpdateDone


	/**
	 * Return summary text of the whole backup process
	 * @return string
	 */
	public function Summary()
	{
		$s = '';
		if (true == IsCli())
			$s = $this->sSummary . "\n";
		else 
			$s = nl2br($this->sSummary);
		return $s;
	} // end of func Summary

	
} // end of class DbUpdater
?>