<?php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2003-2008, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-class@gmail.com>
 * @since		2003-08-25
 * @version		$Id$
 */



/**
 * SQL语句生成类
 * 虽然用ADOdb能够完成大部分工作，但这个类是作为生成SQL的辅助工具用的
 *
 * Test and demo code:
 * <code>
 * <?php
 * require_once('fwolflib/func/ecl.php');
 * require_once('fwolflib/class/adodb.php');
 * require_once('adodb/tohtml.inc.php');
 * header('Content-Type: text/html; charset=utf-8');
 * 
 * $db = new Adodb(array(
 * 	'type' => 'mysqli',
 * 	'host' => '192.168.0.5',
 * 	'name' => '2008-zbb',
 * 	'user' => '2008-zbb',
 * 	'pass' => '2008-moo',
 * 	'lang' => 'utf8',
 * 	));
 * 
 * $db->Connect();
 * 
 * 
 * // Test INSERT, normal mode
 * $ar = array(
 * 	'INSERT' => 'bagy_cc_anc',
 * 	'VALUES' => ' (authcode, region) VALUES ("a\"t\a\'c", "130100")'
 * 	);
 * $sql = $db->GenSql($ar);
 * TestSql($sql);
 * 
 * 
 * // Test INSERT, special mode
 * $ar['VALUES'] = array(
 * 	'authcode' => 'v_authcode',
 * 	'region' => '130100',
 * 	'proj_title' => '项目名称',
 * 	'anc_st' => date('Y-m-d H:i:s'),
 * 	'aprv_by' => 10,
 * 	'flag' => 2
 * 	);
 * $sql = $db->GenSql($ar);
 * TestSql($sql);
 * 
 * 
 * // Test Update, normal mode
 * $ar = array('UPDATE', 'SET', 'WHERE', 'ORDERBY', 'LIMIT');
 * $ar = array(
 * 	'UPDATE' => 'bagy_cc_anc',
 * 	'SET' => 'email = "a@b.com"',
 * 	'WHERE' => 'id > 70',
 * 	'ORDERBY' => 'id desc',
 * 	'LIMIT' => 1,
 * 	);
 * $sql = $db->GenSql($ar);
 * TestSql($sql);
 * 
 * 
 * // Test Update, special mode
 * $ar['SET'] = array(
 * 	'email' => 'b@a.com',
 * 	'authcode' => '12345678',
 * 	);
 * $ar['WHERE'] = array(
 * 	'id > 70',
 * 	'1 = (id % 2)',	// 单数
 * 	);
 * $ar['ORDERBY'] = array(
 * 	'id desc', 'aprv_by asc'
 * 	);
 * //$ar['LIMIT'] = array(2, 1);	// Update can only limit [roucount]
 * $sql = $db->GenSql($ar);
 * TestSql($sql);
 * 
 * 
 * // Test DELETE, normal mode
 * $ar = array('DELETE', 'WHERE', 'ORDERBY', 'LIMIT');
 * $ar = array(
 * 	'DELETE' => 'bagy_cc_anc',
 * 	'WHERE' => 'flag = 0',
 * 	'ORDERBY' => 'id desc',
 * 	'LIMIT' => 1		// Delete can only limit [roucount]
 * 	);
 * $sql = $db->GenSql($ar);
 * TestSql($sql);
 * 
 * 
 * // Delete special mode is obmitted
 * 
 * 
 * // Test SELECT, normal mode
 * $ar = array('SELECT', 'FROM', 'WHERE', 'GROUPBY', 'HAVING',
 * 				'ORDERBY', 'LIMIT');
 * $ar = array(
 * 	'SELECT' => 'id, email, a.authcode, proj_title, anc_st, aprv_by, a.flag, b.title',
 * 	'FROM' => 'bagy_cc_anc a, region b',
 * 	'WHERE' => 'a.region = b.code',
 * 	//'GROUPBY' => 'b.code',		// Tested ok
 * 	'HAVING' => 'a.id > 1',
 * 	'ORDERBY' => 'a.id desc',
 * 	'LIMIT' => 3,
 * 	);
 * $sql = $db->GenSql($ar);
 * TestSql($sql);
 * 
 * 
 * // Test SELECT, special mode
 * $ar['SELECT'] = array(
 * 	'a.id', 'c.email', 'a.authcode', 'bagy_cc_anc.proj_title', 'b.title'
 * 	);
 * $ar['FROM'] = array(
 * 	'a' => 'bagy_cc_anc',
 * 	'b' => 'region',
 * 	'bagy_cc_anc',		// :NOTICE: mixed with-alias and without-alias
 * 	'c' => 'bagy_cc_anc',
 * 	);
 * $ar['WHERE'] = array(
 * 	'a.region = b.code',
 * 	'a.id = bagy_cc_anc.id',
 * 	'a.id = c.id',
 * 	);
 * $ar['LIMIT'] = array(1, 3);
 * $sql = $db->GenSql($ar);
 * TestSql($sql);
 * 
 * 
 * function TestSql($sql)
 * {
 * 	global $db;
 * 	ecl($sql);
 * 	$rs = $db->Execute($sql);
 * 	if (!empty($rs))
 * 		ecl(rs2html($rs));
 * 	else 
 * 		ecl($db->ErrorNo() . ' : ' . $db->ErrorMsg());
 * 	ecl('<hr />');
 * } // end of func TestSql
 * ?>
 * </code>
 * 
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2003-2008, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-class@gmail.com>
 * @since		2003-08-25 09:48:31
 * @version		$Id$
 */
class SqlGenerator
{

    /**
	 * From part user set, used in SELECT only
	 * @var mixed
	 */
    protected $mFrom = '';

    /**
	 * Group by part user set.
	 * @var mixed
	 */
    protected $mGroupby = '';

    /**
	 * Having part user set.
	 * @var mixed
	 */
    protected $mHaving = '';

    /**
	 * Limit part user set.
	 * @var mixed
	 */
    protected $mLimit = '';

    /**
	 * Order by part user set.
	 * @var mixed
	 */
    protected $mOrderby = '';

    /**
	 * Select (column list) part user set.
	 * @var mixed
	 */
	protected $mSelect = '';

    /**
	 * Set part user set, in UPDATE only.
	 * @var mixed
	 */
    protected $mSet = '';

    /**
     * Values part user set.
     * @var mixed
     */
    protected $mValues = '';
    
    /**
	 * Where part user set.
	 * @var mixed
	 */
    protected $mWhere = '';
    
    /**
     * Db object who call $this
     * @var object
     */
    protected $oDb;

    /**
	 * Delete part(table name, 1 table only) user set.
	 * @var string
	 */
    protected $sDelete = '';

    /**
	 * Insert part(table name, 1 table only) user set.
	 * @var string
	 */
    protected $sInsert = '';
    
    /**
     * Delete sql part generated
     * @var	string
     */
    protected $sSqlDelect = '';

    /**
     * From sql part generated
     * @var	string
     */
    protected $sSqlFrom = '';

    /**
     * Group by sql part generated
     * @var	string
     */
    protected $sSqlGroupby = '';

    /**
     * Having sql part generated
     * @var	string
     */
    protected $sSqlHaving = '';

    /**
     * Insert sql part generated
     * @var	string
     */
    protected $sSqlInsert = '';

    /**
     * Limit sql part generated
     * @var	string
     */
    protected $sSqlLimit = '';

    /**
     * Order by sql part generated
     * @var	string
     */
    protected $sSqlOrderby = '';

    /**
     * Select sql part generated
     * @var	string
     */
    protected $sSqlSelect = '';

    /**
     * Set sql part generated, for UPDATE only.
     * @var	string
     */
    protected $sSqlSet = '';

    /**
     * Update sql part generated
     * @var	string
     */
    protected $sSqlUpdate = '';

    /**
     * Values sql part generated, for INSERT only.
     * @var	string
     */
    protected $sSqlValues = '';

    /**
     * Where sql part generated
     * @var	string
     */
    protected $sSqlWhere = '';

    /**
	 * Update part(table name, 1 table only) user set.
	 * @var string
	 */
	protected $sUpdate = '';


	/**
	 * Construct
	 * @param	object	&$db	Db object
	 */
	public function __construct(&$db)
	{
		if (!empty($db))
			$this->oDb = $db;
	} // end of func __construct
	
	
	/**
	 * 重置已经设定的参数, or part of them
	 *
	 * @param	string	$part	重设哪一部分
	 * @see		gsql()
	 */
	public function Clear($part = '')
	{
		// order by => ORDERBY
		$part = str_replace(' ', '', (strtolower($part)));
		
		// Reset-able part
		$ar_part = array(
			'SELECT',
			'UPDATE',
			'INSERT',
			'DELETE',
			'VALUES',
			'SET',
			'FROM',
			'WHERE',
			'GROUPBY',
			'HAVING',
			'ORDERBY',
			'LIMIT',
			);
		
	    if (empty($part) || 'all' == $part)
	    {
	    	// Reset all
	    	foreach ($ar_part as $s)
	    	{
	    		$s = ucfirst(strtolower($s));
	    		$this->${"m$s"} = '';
	    		$this->${"sSql$s"} = '';
	    	}
	    }
	    else 
	    {
	    	// Reset 1 part
	    	$s = ucfirst($part);
	    	$this->${"m$s"} = '';
	    	$this->${"sSql$s"} = '';
	    }
	} // end of function Clear
	
	
	/**
	 * Generate an DELETE sql
	 * @param	array	$ar_config
	 * @return	string
	 */
	protected function GenDelete($ar_config = array())
	{
		if (is_array($ar_config) && !empty($ar_config))
		{
			// Using parts in $ar_config, not all parts
			// config value has been set already, here only use it's 'name'
			$ar = &$ar_config;
		}
		else 
		{
			// Using all parts, by this sequence
			// http://dev.mysql.com/doc/refman/5.0/en/delete.html
			$ar = array('DELETE', 'WHERE', 'ORDERBY', 'LIMIT');
		}
		$sql = '';
		foreach ($ar as $part => $param)
		{
			$part = ucfirst(strtolower($part));
			$sql .= $this->{"sSql$part"};
		}
		return $sql;
	} // end of func GenDelete
	
	
	/**
	 * Generate an INSERT sql
	 * @param	array	$ar_config
	 * @return	string
	 */
	protected function GenInsert($ar_config = array())
	{
		if (is_array($ar_config) && !empty($ar_config))
		{
			// Using parts in $ar_config, not all parts
			// config value has been set already, here only use it's 'name'
			$ar = &$ar_config;
		}
		else 
		{
			// Using all parts, by this sequence
			// http://dev.mysql.com/doc/refman/5.0/en/insert.html
			$ar = array('INSERT', 'VALUES');
		}
		$sql = '';
		foreach ($ar as $part => $param)
		{
			$part = ucfirst(strtolower($part));
			$sql .= $this->{"sSql$part"};
		}
		return $sql;
	} // end of func GenInsert
	
	
	/**
	 * Generate an SELECT sql
	 * @param	array	$ar_config
	 * @return	string
	 */
	protected function GenSelect($ar_config = array())
	{
		if (is_array($ar_config) && !empty($ar_config))
		{
			// Using parts in $ar_config, not all parts
			// config value has been set already, here only use it's 'name'
			$ar = &$ar_config;
		}
		else 
		{
			// Using all parts, by this sequence
			// http://dev.mysql.com/doc/refman/5.0/en/select.html
			$ar = array('SELECT', 'FROM', 'WHERE', 'GROUPBY', 'HAVING',
				'ORDERBY', 'LIMIT');
		}
		$sql = '';
		foreach ($ar as $part => $param)
		{
			$part = ucfirst(strtolower($part));
			$sql .= $this->{"sSql$part"};
		}
		return $sql;
	} // end of func GenSelect
	
	
	/**
	 * Generate SQL part, which param is array and need to list out in plain format.
	 * 
	 * @param mixed		$param
	 * @param string	$s_split	String used between parts.
	 * @return string
	 */
	protected function GenSqlArray($param, $s_split = ', ')
	{
		$sql = '';
		if (is_array($param) && !empty($param))
			// Because of plain format, so $k is useless
			foreach ($param as $k=>$v)
			{
				/*
				if (is_int($k))
					$sql .= ", $v";
				else 
					$sql .= ", $k $v";
				*/
				$sql .= "$s_split $v";
			}
		else 
			$sql .= "$s_split $param";
		$sql = substr($sql, strlen($s_split));
		
		return $sql;
	} // end of func GenSqlArray
	
	
	/**
	 * Generate SQL part, which param is array and need use AS in it.
	 * @link http://dev.mysql.com/doc/refman/5.0/en/select.html
	 * @param mixed	$param	Items in SQL SELECT part, Array or string.
	 * 						Array($k=>$v) means '$k AS $v' in sql,
	 * 						but when $k is int, means '$v AS $v' in sql.
	 * @param boolean	$use_as	Sybase table alias can't use AS
	 * @param boolean	$quote	AS column alias, need to be quoted(true),
	 * 							AS table alias, need not to be quoted(false).
	 * @param boolean	$tas	In table alias, array($k=>$v) means
	 * 							'FROM $v AS $k', notice it's reverse order:
	 * 							$v => $k, because 1 table can have mutl alias
	 * @return string
	 */
	protected function GenSqlArrayAs($param, $use_as = true, $quote = false, $tas = false)
	{
		$sql = '';
		if (is_array($param) && !empty($param))
			foreach ($param as $k=>$v)
			{
				// If there are space in $v, it need to be quoted
				// so always quote it.
				if (is_int($k))
				{
					$sql .= ", $v";
				}
				else 
				{
					// table AS a
					// tabel AS 'a'
					$s_split = ($quote) ? "'" : '';
					$s_as = ($use_as) ? 'AS' : '';
					if ($tas)
						$sql .= ", $v $s_as $s_split{$k}$s_split";
					else 
						$sql .= ", $k $s_as $s_split{$v}$s_split";
				}
			}
		else 
			$sql .= ", $param";
		$sql = substr($sql, 2);
		
		return $sql;
	} // end of func GenSqlArrayAs
	
	
	/**
	 * Generate SQL part, SET subparse of UPDATE
	 * @link http://dev.mysql.com/doc/refman/5.0/en/update.html
	 * @param array	$param	Items in SQL UPDATE part,
	 * 						Array only, string will return original value.
	 * 						Array($k=>$v) means 'SET $k = $v, ' in sql,
	 * @return string
	 */
	protected function GenSqlArraySet($param)
	{
		$sql = '';
		if (is_array($param) && !empty($param))
		{
			foreach ($param as $k=>$v)
			{
				$sql .= ", $k = " . $this->GenSqlQuote($this->sUpdate, $k, $v);
			}
			$sql = ' SET ' . substr($sql, 2);
		}
		else 
		{
			// If you fogot 'SET ', I add for you
			if ('SET ' != substr(strtoupper(trim($param)), 0, 4))
				$sql .= ' SET ';
			$sql .= $param;
		}
		
		return $sql;
	} // end of func GenSqlArraySet
	
	
	/**
	 * Generate SQL part, VALUES subparse of INSERT
	 * @link http://dev.mysql.com/doc/refman/5.0/en/insert.html
	 * @param array	$param	Items in SQL INSERT part,
	 * 						Array only, string will return original value.
	 * 						Array($k=>$v) means '($k) VALUES ($v)' in sql,
	 * @return string
	 */
	protected function GenSqlArrayValues($param)
	{
		$sql = ' ( ';
		if (is_array($param) && !empty($param))
		{
			$sql1 = '';
			$sql2 = '';
			foreach ($param as $k=>$v)
			{
				$sql1 .= ', ' . $k;
				$sql2 .= ', ' . $this->GenSqlQuote($this->sInsert, $k, $v);
			}
			$sql1 = substr($sql1, 2);
			$sql2 = substr($sql2, 2);
			$sql .= $sql1 . ' ) VALUES ( ' . $sql2 . ' ) ';
		}
		else 
		{
			$sql = $param;
		}
		
		return $sql;
	} // end of func GenSqlArrayValues
	
	
	/**
	 * Smarty quote string in sql, by check columns type
	 * @param	string	$table
	 * @param	string	$column
	 * @param	mixed	$val
	 * @return	string
	 */
	protected function GenSqlQuote($table, $column, $val)
	{
		$this->oDb->GetMetaColumns($table);
		$type = $this->oDb->aMetaColumns[$table][$column]->type;
		//var_dump($type);
		if (in_array($type, array(
			'bigint',
			'bit',
			'decimal',
			'double',
			'float',
			'int',
			'mediumint',
			'numeric',
			'real',
			'smallint',
			'tinyint',
			)))
			// Need not quote, output directly
			return $val;
		else 
		{
			// Need quote, use db's quote method
			$val = stripslashes($val);
			return $this->oDb->qstr($val, false);
		}
	} // end of func GenSqlQuote

	
	/**
	 * Generate an UPDATE sql
	 * @param	array	$ar_config
	 * @return	string
	 */
	protected function GenUpdate($ar_config = array())
	{
		if (is_array($ar_config) && !empty($ar_config))
		{
			// Using parts in $ar_config, not all parts
			// config value has been set already, here only use it's 'name'
			$ar = &$ar_config;
		}
		else 
		{
			// Using all parts, by this sequence
			// http://dev.mysql.com/doc/refman/5.0/en/update.html
			$ar = array('UPDATE', 'SET', 'WHERE', 'ORDERBY', 'LIMIT');
		}
		$sql = '';
		foreach ($ar as $part => $param)
		{
			$part = ucfirst(strtolower($part));
			$sql .= $this->{"sSql$part"};
		}
		return $sql;
	} // end of func GenUpdate
	
	
	/**
	 * Get DELETE sql only
	 * @param	array	$ar_config
	 * @return	string
	 */
	public function GetDelete($ar_config = array())
	{
		return $this->GetSql($ar_config, 'DELETE');
	} // end of func GetDelete

	
	/**
	 * Get INSERT sql only
	 * @param	array	$ar_config
	 * @return	string
	 */
	public function GetInsert($ar_config = array())
	{
		return $this->GetSql($ar_config, 'INSERT');
	} // end of func GetInsert

	
	/**
	 * Get SELECT sql only
	 * @param	array	$ar_config
	 * @return	string
	 */
	public function GetSelect($ar_config = array())
	{
		return $this->GetSql($ar_config, 'SELECT');
	} // end of func GetSelect

	
	/**
	 * Get SQL statement
	 * 
	 * If use SELECT, UPDATE, INSERT, DELETE simultaneously,
	 * System will select the first on occurs by this order.
	 * @param	array	$ar_config	Array(SELECT=>..., FROM=>...)
	 * 								If obmit, use rememberd value.
	 * @param	string	$action		SELECT/UPDATE ... etc
	 * @return	string
	 */
	public function GetSql($ar_config = array(), $action = '')
	{
		$action = strtoupper($action);
		$this->Set($ar_config);
		
		// Got real action
		if (is_array($ar_config) && !empty($ar_config))
			foreach ($ar_config as $part => $param)
			{
				// SELECT/UPDATE/INSERT/DELETE ? Use the 1st occur guy.
				$part = strtoupper($part);
				if (empty($action) &&
					in_array($part,
						array('SELECT', 'UPDATE', 'INSERT', 'DELETE'))
					)
					$action = $part;
			}
		
		// Call seperate func to generate sql
		$action = ucfirst(strtolower($action));
		$sql = $this->{"Gen$action"}($ar_config);
		
		return $sql;
	} // end of func GetSql
	
	
	/**
	 * Get UPDATE sql only
	 * @param	array	$ar_config
	 * @return	string
	 */
	public function GetUpdate($ar_config = array())
	{
		return $this->GetSql($ar_config, 'UPDATE');
	} // end of func GetUpdate

	
	/**
	 * Set value in array to property
	 * @param	array	&$ar_config
	 * @return	string
	 */
	public function Set(&$ar_config)
	{
		if (is_array($ar_config) && !empty($ar_config)) {
			// Re-order sql part
			$ar = array();
			if (isset($ar_config['SELECT']))
				$ar['SELECT'] = $ar_config['SELECT'];
			if (isset($ar_config['DELETE']))
				$ar['DELETE'] = $ar_config['DELETE'];
			if (isset($ar_config['INSERT']))
				$ar['INSERT'] = $ar_config['INSERT'];
			if (isset($ar_config['UPDATE']))
				$ar['UPDATE'] = $ar_config['UPDATE'];
			if (isset($ar_config['VALUES']))
				$ar['VALUES'] = $ar_config['VALUES'];
			if (isset($ar_config['FROM']))
				$ar['FROM'] = $ar_config['FROM'];
			if (isset($ar_config['SET']))
				$ar['SET'] = $ar_config['SET'];
			if (isset($ar_config['WHERE']))
				$ar['WHERE'] = $ar_config['WHERE'];
			if (isset($ar_config['GROUPBY']))
				$ar['GROUPBY'] = $ar_config['GROUPBY'];
			if (isset($ar_config['HAVING']))
				$ar['HAVING'] = $ar_config['HAVING'];
			if (isset($ar_config['ORDERBY']))
				$ar['ORDERBY'] = $ar_config['ORDERBY'];
			if (isset($ar_config['LIMIT']))
				$ar['LIMIT'] = $ar_config['LIMIT'];
			$ar_config = $ar;
			
			foreach ($ar_config as $part => $param)
			{
				// Write config to property
				$part = ucfirst(strtolower($part));
				$this->{"Set$part"}($param);
			}
		}
	} // end of function
	
	
    /**
	 * Set Delete
	 * @param	mixed	$param
	 * @return	string
	 */
	public function SetDelete($param)
	{
		$this->sDelete = $param;
		$this->sSqlDelete = ' DELETE FROM ' . $param;

		// Retrieve table schema, so VALUES/SET can detimine how to quote
		$this->oDb->GetMetaColumns($param);
		
		return $this->sSqlDelete;
	} // end of func SetDelete


	/**
	 * Set From
	 * @param	mixed	$param
	 * @return	string
	 */
	public function SetFrom($param)
	{
		$this->mFrom = $param;
		// :NOTICE: 'FROM tbl as a', No space allowed in 'a', need not quote.
		$this->sSqlFrom = ' FROM ' . $this->GenSqlArrayAs($param, false, false, true);
		return $this->sSqlFrom;
	} // end of func SetFrom


	/**
	 * Set Group by
	 * @param	mixed	$param
	 * @return	string
	 */
	public function SetGroupby($param)
	{
		$this->mGroupby = $param;
		$this->sSqlGroupby = ' GROUP BY' . $this->GenSqlArray($param);
		return $this->sSqlGroupby;
	} // end of func SetGroupby
	

	/**
	 * Set Having
	 * @param	mixed	$param
	 * @return	string
	 */
	public function SetHaving($param)
	{
		$this->mHaving = $param;
		$this->sSqlHaving = ' HAVING ' . $this->GenSqlArray($param, ' AND ');
		return $this->sSqlHaving;
	} // end of func SetHaving

	
	/**
	 * Set Insert
	 * @param	mixed	$param
	 * @return	string
	 */
	public function SetInsert($param)
	{
		$this->sInsert = $param;
		$this->sSqlInsert = ' INSERT INTO ' . $param;
		
		// Retrieve table schema, so VALUES/SET can detimine how to quote
		$this->oDb->GetMetaColumns($param);
		
		return $this->sSqlInsert;
	} // end of func SetInsert


	/**
	 * Set Limit
	 * @param	mixed	$param
	 * @return	string
	 */
	public function SetLimit($param)
	{
		if ('sybase' != substr($this->oDb->aDbProfile['type'], 0, 6))
		{
			$this->mLimit = $param;
			$this->sSqlLimit = ' LIMIT ' . $this->GenSqlArray($param);
		}
		else 
		{
			$this->mLimit = '';
			$this->sSqlLimit = '';
		}
		return $this->sSqlLimit;
	} // end of func SetLimit

	
	/**
	 * Set Order by
	 * @param	mixed	$param
	 * @return	string
	 */
	public function SetOrderby($param)
	{
		$this->mOrderby = $param;
		$this->sSqlOrderby = ' ORDER BY ' . $this->GenSqlArray($param);
		return $this->sSqlOrderby;
	} // end of func SetOrderby


    /**
	 * Set Select
	 * @param	mixed	$param
	 * @return	string
	 */
	public function SetSelect($param)
	{
		$this->mSelect = $param;
		$this->sSqlSelect = ' SELECT ' . $this->GenSqlArrayAs($param, true, true, false);
		return $this->sSqlSelect;
	} // end of func SetSelect
	

	/**
	 * Set Set
	 * @param	mixed	$param
	 * @return	string
	 */
	public function SetSet($param)
	{
		$this->mSetSet = $param;
		// For UPDATE only, INSERT uses VALUES
		// User give param array(col => value)
		$this->sSqlSet = $this->GenSqlArraySet($param);
		return $this->sSqlSet;
	} // end of func SetSet


	/**
	 * Set Update
	 * @param	mixed	$param
	 * @return	string
	 */
	public function SetUpdate($param)
	{
		$this->sUpdate = $param;
		$this->sSqlUpdate = ' UPDATE ' . $param;

		// Retrieve table schema, so VALUES/SET can detimine how to quote
		$this->oDb->GetMetaColumns($param);

		return $this->sSqlUpdate;
	} // end of func SetUpdate
	

	/**
	 * Set Values
	 * @param	mixed	$param
	 * @return	string
	 */
	public function SetValues($param)
	{
		$this->mSetValues = $param;
		// For INSERT only, UPDATE uses SET
		// User give param array(col => value)
		$this->sSqlValues = $this->GenSqlArrayValues($param);
		return $this->sSqlValues;
	} // end of func SetValues


	/**
	 * Set Where
	 * @param	mixed	$param
	 * @return	string
	 */
	public function SetWhere($param)
	{
		$this->mSetWhere = $param;
		// Add '(' to defend sql injection
		$this->sSqlWhere = ' WHERE ( ' . $this->GenSqlArray($param, ') AND (') . ' )';
		return $this->sSqlWhere;
	} // end of func SetWhere

	
} // end of class SqlGenerator
?>