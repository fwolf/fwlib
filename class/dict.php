<?php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2011, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2011-07-15
 */


require_once(dirname(__FILE__) . '/fwolflib.php');


/**
 * Manipulate dict data, eg db code-name table.
 *
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2011, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2011-07-15
 */
class Dict extends Fwolflib {


	/**
	 * Dict data array
	 * @var	array
	 */
	public $aData = array();


	/**
	 * Constructor
	 *
	 * @param	array	$ar_cfg
	 */
	public function __construct ($ar_cfg = array()) {
		parent::__construct($ar_cfg);
	} // end of func __construct


	/**
	 * Get relate value for given pk
	 *
	 * @param	mixed	$ar_pk	Array or string of pk.
	 * @param	mixed	$col	Array or string of cols for return.
	 * @return	mixed
	 */
	public function Get ($ar_pk, $col = '') {
		if (empty($ar_pk))
			return null;

		if (!is_array($ar_pk))
			$ar_pk = array($ar_pk);

		$ar_col = $this->GetCol($col);

		$ar = array();
		foreach ($ar_pk as $pk) {
			if (isset($this->aData[$pk]))
				$ar[$pk] = $this->GetColData($this->aData[$pk], $ar_col);
			else
				$ar[$pk] = null;
		}

		if (1 == count($ar))
			return array_shift($ar);
		else
			return $ar;
	} // end of func Get


	/**
	 * Get cols you want to query.
	 *
	 * If $col not assigned, assign as first col which is not pk.
	 * '*' means all cols.
	 * @param	mixed	$col	Array or string of cols.
	 * @return	mixed
	 */
	protected function GetCol ($col = '') {
		// Got currect cols
		$ar_col = array();
		if ('*' == $col)
			$ar_col = $this->aCfg['dict-cols'];
		elseif (empty($col)) {
			// Assign first col not pk
			$i = 0;
			while ($this->aCfg['dict-cols'][$i]
					== $this->aCfg['dict-cols-pk']
				&& $i < count($this->aCfg['dict-cols'])) {
				$i++;
			}
			$ar_col = array($this->aCfg['dict-cols'][$i]);
		} else {
			// Find valid cols
			if (is_string($col)) {
				$col = explode(',', $col);
				array_walk($col, 'trim');
			}
			foreach ($col as $v)
				if (in_array($v, $this->aCfg['dict-cols']))
					$ar_col[] = $v;
		}

		if (1 == count($ar_col))
			return array_shift($ar_col);
		else
			return $ar_col;
	} // end of func GetCol


	/**
	 * Get data from array by assigned cols
	 *
	 * @param	array	$ar_data
	 * @param	mixed	$col
	 * @return	mixed
	 */
	protected function GetColData ($ar_data, $col) {
		if (empty($ar_data) || empty($col))
			return null;
		if (!is_array($col))
			$col = array($col);

		$ar = array();
		foreach ($col as $v) {
			if (isset($ar_data[$v]))
				$ar[$v] = $ar_data[$v];
		}

		if (1 == count($ar))
			return array_shift($ar);
		else
			return $ar;
	} // end of func GetColData


	/**
	 * Get data fit given condition
	 *
	 * In condition, use {col} and native php syntax.
	 * Delimiter can change in SetStruct().
	 * @param	string	$s_cond
	 * @param	string	$col		Wanted cols.
	 * @return	array	2-dim array of result.
	 * @see		SetStruct()
	 */
	public function GetList ($s_cond = '', $col = '*') {
		if (empty($s_cond))
			return $this->aData;
		if (empty($this->aData))
			return array();
		$col = $this->GetCol($col);

		$ar_cols = array();
		foreach ($this->aCfg['dict-cols'] as $v)
			$ar_cols[] = $this->aCfg['dict-list-cond-delimiter-left']
				. $v
				. $this->aCfg['dict-list-cond-delimiter-right'];

		// Loop check
		$ar_rs = array();
		$s_cond = '$b = (' . $s_cond . ');';
		foreach ($this->aData as $k => &$data) {
			$s_cond_t = str_replace($ar_cols, $data, $s_cond);
			eval($s_cond_t);
			if ($b)
				$ar_rs[$k] = $this->GetColData($data, $col);
		}
		return $ar_rs;
	} // end of func GetList


	/**
	 * Get SQL for write dict data to db
	 *
	 * @param	object	$o_db	Adodb conn object.
	 * @return	string
	 * @see		Adodb
	 */
	public function GetSql ($o_db) {
		if (empty($o_db) || !$o_db->IsConnected()) {
			$this->Log('Db empty or not connected.', 4);
			return '';
		}
		if (empty($this->aCfg['dict-table'])) {
			$this->Log('Db dict table not set.', 4);
			return '';
		}

		// TRUNCATE TABLE
		$s_sql = 'TRUNCATE TABLE ' . $this->aCfg['dict-table']
			. $o_db->GetSqlDelimiter();
		// Mysql set names
		if ($o_db->IsDbMysql()) {
			$s_sql .= 'SET NAMES \''
				. str_replace('utf-8', 'utf8', $o_db->aDbProfile['lang'])
				. '\'' . $o_db->GetSqlDelimiter();
		}

		// Data
		if (!empty($this->aData))
			foreach ($this->aData as $k => $ar_row) {
// INSERT INTO code_i4 (code, title) VALUES (10001, 	'一般新闻');
				// Values part
				$ar_val = array();
				foreach ($ar_row as $key => $val)
					$ar_val[] = $o_db->QuoteValue($this->aCfg['dict-table']
						, $key, $val);
				// Join with column and other part
				$s_sql .= 'INSERT INTO ' . $this->aCfg['dict-table']
					. ' (' . implode(', ', $this->aCfg['dict-cols']) . ')'
					. ' VALUES (' . implode(",\t", $ar_val) . ')'
					. $o_db->GetSqlDelimiter();
			}

		return $s_sql;
	} // end of func GetSql


	/**
	 * Init dict content
	 *
	 * @return	object
	 */
	protected function Init () {
		parent::Init();

		$this->SetStruct();
		if (empty($this->aCfg['dict-cols']))
			$this->Log('Dict cols not defined.', 5);

		return $this;
	} // end of func Init


	/**
	 * Insert value to $this->aData
	 *
	 * @param	array	$ar_data	1 or 2-dim data array.
	 * @return	object
	 */
	public function Set ($ar_data) {
		if (empty($ar_data)) {
			$this->Log('Empty data given.', 4);
			return $this;
		}
		// Convert 1-dim to 2-dim
		if (!is_array($ar_data[array_rand($ar_data)]))
			$ar_data = array($ar_data);

		$this->SetData($ar_data);
		return $this;
	} // end of func Set


	/**
	 * Insert value to $this->aData
	 *
	 * @param	array	$ar_data	2-dim data array.
	 * @return	object
	 */
	protected function SetData ($ar_data) {
		if (empty($ar_data)) {
			$this->Log('Empty data given.', 4);
			return $this;
		}
		if (empty($this->aCfg['dict-cols'])) {
			$this->Log('Dict cols not defined', 5);
			return $this;
		}

		foreach ($ar_data as $ar) {
			$ar_t = array();
			foreach ($this->aCfg['dict-cols'] as $col) {
				if (!empty($ar))
					$ar_t[$col] = array_shift($ar);
			}
			// Single pk as array index
			if (!empty($this->aCfg['dict-cols-pk'])
				&& is_string($this->aCfg['dict-cols-pk'])) {
				if (!empty($ar_t[$this->aCfg['dict-cols-pk']]))
					$this->aData[$ar_t[$this->aCfg['dict-cols-pk']]]
						= $ar_t;
				else {
					$this->Log('Dict pk not set in data.', 4);
					$this->aData[] = $ar_t;
				}
			} else
				// Multi pk or no pk
				$this->aData[] = $ar_t;
		}
	} // end of func SetData


	/**
	 * Set data structure, usually override by sub class.
	 *
	 * @return	object
	 */
	public function SetStruct () {
		// Array of string.
		$this->SetCfg('dict-cols', array('code', 'title'));
		// Array for multi and string for single.
		$this->SetCfg('dict-cols-pk', 'code');
		$this->SetCfg('dict-table', 'code_i4');

		// Delimiter in get list condition
		$this->SetCfg('dict-list-cond-delimiter-left', '{');
		$this->SetCfg('dict-list-cond-delimiter-right', '}');
	} // end of func SetStruct


} // end of class Dict
?>
