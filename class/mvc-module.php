<?php
require_once(dirname(__FILE__) . '/fwolflib.php');
require_once(dirname(__FILE__) . '/adodb.php');
require_once(dirname(__FILE__) . '/cache/cache.php');
require_once(dirname(__FILE__) . '/../func/request.php');
require_once(dirname(__FILE__) . '/../func/string.php');


/**
 * Module in MVC
 *
 * Do data compute or database relate operate.
 * Usually only carry data, leave data format job to View.
 *
 * @package		fwolflib
 * @subpackage	class.mvc
 * @copyright	Copyright 2008-2012, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class.mvc@gmail.com>
 * @since		2008-04-06
 * @see			Controler
 * @see			View
 */
abstract class Module extends Fwolflib {

	/**
	 * Cache object
	 * @var	object
	 */
	public $oCache = NULL;

	/**
	 * Use cache or not
	 * @var	boolean
	 */
	public $bCacheOn = FALSE;

	/**
	 * Database object
	 * @var object
	 */
	public $oDb = NULL;

	/**
	 * Number of items in list
	 *
	 * In simple idea, this should set in view,
	 * but pagesize is impleted as limit in select,
	 * so when generate sql you need to use it.
	 * @var int
	 */
	public $iPageSize = 10;

	/**
	 * Caller: view object
	 * @var object
	 */
	public $oView = NULL;


	// Get db connection, because unknown db & dblib,
	//	implete it in application module class.
	// Also can extend db connect class easily.
	abstract protected function DbConn ($dbprofile);


	/**
	 * construct
	 * @param object	$view	Caller view object
	 */
	public function __construct ($view = null) {
		// Unset for auto new
		unset($this->oCache);
		unset($this->oDb);

		parent::__construct();

		if (!is_null($view))
			$this->oView = &$view;

		// Temp cache switch
		if ('0' == GetGet('cache'))
			$this->bCacheOn = FALSE;
	} // end of func __construct


	/**
	 * Overload __call
	 * Auto call CacheXxx func.
	 *
	 * @param	string	$name			Method name
	 * @param	array	$arg			Method argument
	 * @return	mixed
	 */
	public function __call ($name, $arg) {
		// Func name start from Cache
		if ('Cache' == substr($name, 0, 5)) {
			$method = substr($name, 5);
			if (method_exists($this, $method)) {
				// Cache key prefix
				$key = get_class($this) . '/' . $method . '/';
				// Get cache lifetime by key prefix
				$lifetime = $this->CacheLifetime($key);
				// Real cache key need arg added
				if (!empty($arg))
					foreach ($arg as $k => $v)
						$key .= $k . '/' . $v . '/';

				$val = NULL;
				// Try read from cache if cache on
				if ($this->bCacheOn)
					$val = $this->oCache->Get($key, $lifetime);

				// Read cache fail, call real method
				if (is_null($val)) {
					$val = call_user_func_array(array($this, $method), $arg);
					// Update cache
					$this->oCache->Set($key, $val, $lifetime);
				}

				return $val;
			}
		}

		// Method not exists, trigger error.
		//$this->Log('Undefined method: ' . $name . '()', 5);
		trigger_error('Undefined method: ' . $name . '()', E_USER_ERROR);
		return NULL;
	} // end of func __call


	/**
	 * Get cache lifetime
	 * Subclass should rewrite this method,
	 * to get lifetime by assigned key.
	 *
	 * @param	string	$key			Key, or key prefix
	 * @return	int						Lifetime in seconds
	 */
	public function CacheLifetime ($key) {
		// Use Cache obj default
		return NULL;
	} // end of func CacheLifetime


	/**
	 * Compare new data with data from db, get diff array
	 *
	 * New/old array are all assoc, index by table column.
	 * PK column must exists in new array, value can be NULL.
	 *
	 * In new array, empty PK means INSERT.
	 * In old array, empty or not exists PK means DELETE.
	 *
	 * Multi table and row supported, new/old array must match.
	 *
	 * After modify db according diff, 'flag' should be set,
	 * 'code, msg' can also used to store db op message.
	 * When db query success, 'code' is count for rows changed,
	 * when db query fail, 'code' is error no and 'msg' is error msg.
	 *
	 * New/old array structure:
	 * array(
	 * 	[tbl] => array(
	 * 		array(						// Single row can admit this level
	 * 									// DbDiffRow() use this as param
	 * 			[col] => [val],
	 * 			...
	 * 		),
	 * 		...
	 * 	),
	 * 	...
	 * )
	 *
	 * Result is array, structure:
	 * array(
	 * 	code: >= 0 means success, < 0 means error
	 * 	msg: Error msg
	 * 	flag: 0=default, 100=committed, -100=rollbacked
	 * 	diff = array(
	 * 		[tbl] = array(
	 * 			array(					// DbDiffRow() return this array
	 * 				mode: INSERT | DELETE | UPDATE
	 *				// PK new/old is same in UPDATE mode
	 * 				pk: array(
	 * 					[pk1] = array(
	 * 						old => [val],
	 * 						new => [val],
	 * 					),
	 * 					...
	 * 				),
	 *				// Other cols change
	 * 				col: array(
	 * 					[col] = array(
	 * 						old => [val],
	 * 						new => [val],
	 * 					),
	 * 					...
	 * 				),
	 * 			),
	 * 			...
	 * 		),
	 * 		...
	 * 	)
	 * )
	 * @param	array	$ar_new			New data array
	 * @param	Adodb	$db				Db object, need to be connected,
	 * 									NULL to use $this->oDb.
	 * @param	array	$ar_old			Old data array, NULL to read from db
	 * @return	array
	 */
	public function DbDiff (array $ar_new, Adodb $db = NULL, array $ar_old = NULL) {
		// Param check
		if (empty($ar_new))
			return array(
				'code'	=> -1,
				'msg'	=> 'New array empty.',
			);
		if (is_null($db))
			$db = $this->oDb;

		// Loop for table
		$ar_diff = array();
		foreach ($ar_new as $tbl => $ar_rows) {
			// Convert single array to 2-dim array
			if (!isset($ar_rows[0]))
				$ar_rows = array($ar_rows);
			if (!empty($ar_old[$tbl]) && !isset($ar_old[$tbl][0]))
				$ar_old[$tbl] = array($ar_old[$tbl]);

			$ar_col_pk = $db->GetMetaPrimaryKey($tbl);
			if (!is_array($ar_col_pk))
				$ar_col_pk = array($ar_col_pk);

			// Loop for rows
			foreach ($ar_rows as $i_row => $row) {
				// Check all PK exist in row data
				foreach ($ar_col_pk as $s_pk) {
					// isset($v) assume $v = NULL is false
					if (!array_key_exists($s_pk, $row))
						return array(
							'code'	=> -2,
							'msg'	=> 'Table ' . $tbl . ', PK ' . $s_pk
								. ' not assigned in new array, index: '
								. $i_row . '.',
						);
				}

				$ar_col = array_keys($row);
				$ar_val_pk = array_intersect_key($row
					, array_fill_keys($ar_col_pk, NULL));

				// Got old data array
				if (!isset($ar_old[$tbl][$i_row])) {
					// Query from db by PK from new array
					$rs = $db->GetDataByPk($tbl, $ar_val_pk, $ar_col
						, $ar_col_pk);
					if (is_null($rs))
						$rs = array();
					if (!is_array($rs)) {
						// Row only have one column, convert back to array
						$rs = array($ar_col[0] => $rs);
					}
					$ar_old[$tbl][$i_row] = $rs;
				}

				// Do diff for this row
				$ar = $this->DbDiffRow($row, $ar_old[$tbl][$i_row]
					, $ar_col_pk);
				if (!empty($ar))
					$ar_diff['diff'][$tbl][] = $ar;
			}
		}

		$ar_diff['code'] = 0;
		$ar_diff['msg'] = 'Successful.';
		$ar_diff['flag'] = 0;
		return $ar_diff;
	} // end of func DbDiff


	/**
	 * Execute DbDiff()'s result to modify db
	 *
	 * @param	array	$ar_diff		Same with DbDiff()'s result
	 * @param	Adodb	$db
	 * @return	int						Rows modified, < 0 when error.
	 * @see DbDiff()
	 */
	public function DbDiffCommit (array &$ar_diff, Adodb $db = NULL) {
		// Condition check
		if (empty($ar_diff) || empty($ar_diff['diff']))
			// No diff data
			return -2;
		if (0 > $ar_diff['code'])
			// Previous op not successful
			return -3;
		if (100 == $ar_diff['flag'])
			// Already committed
			return -4;

		if (is_null($db))
			$db = $this->oDb;

		// Generate sql
		$ar_sql_all = array();
		foreach ($ar_diff['diff'] as $tbl => $ar_rows) {
			if (empty($ar_rows))
				continue;
			foreach ($ar_rows as $i_row => $row) {
				$ar_sql = array();
				switch ($row['mode']) {
					case 'INSERT':
						$ar_sql['INSERT'] = $tbl;
						$ar_col = $row['pk'] + $row['col'];	// Sure not empty
						foreach ($ar_col as $k => $v)
							$ar_sql['VALUES'][$k] = $v['new'];
						break;
					case 'DELETE':
						$ar_sql['DELETE'] = $tbl;
						foreach ($row['pk'] as $k => $v)
							$ar_sql['WHERE'][] = $k . ' = '
								. $db->QuoteValue($tbl, $k, $v['old']);
						// Limit rowcount to 1 for safety
						$ar_sql['LIMIT'] = 1;
						break;
					case 'UPDATE':
						$ar_sql['UPDATE'] = $tbl;
						foreach ($row['col'] as $k => $v)
							$ar_sql['SET'][$k] = $v['new'];
						foreach ($row['pk'] as $k => $v)
							$ar_sql['WHERE'][] = $k . ' = '
								. $db->QuoteValue($tbl, $k, $v['new']);
						// Limit rowcount to 1 for safety
						$ar_sql['LIMIT'] = 1;
						break;
				}

				if (!empty($ar_sql)) {
					$ar_sql_all[] = $db->GenSql($ar_sql);
				}
			}
		}

		// Execute sql
		$i_cnt = 0;
		if (!empty($ar_sql_all)) {
			$b_error = false;
			$db->BeginTrans();
			while (!$b_error && !empty($ar_sql_all)) {
				$db->Execute(array_shift($ar_sql_all));
				if (0 != $db->ErrorNo()) {
					$b_error = true;
					$this->Log('DbDiffCommit error ' . $db->ErrorNo()
						. ' : ' . $db->ErrorMsg());
				}
				else
					$i_cnt += $db->Affected_Rows();
			}

			if ($b_error) {
				$ar_diff['code'] = abs($db->ErrorNo()) * -1;
				$ar_diff['msg'] = $db->ErrorMsg();
				$db->RollbackTrans();
				return -1;
			}
			else {
				$db->CommitTrans();
				// Modify diff info
				$ar_diff['code'] = $i_cnt;
				$ar_diff['flag'] = 100;
				return $i_cnt;
			}
		}
		return $i_cnt;
	} // end of func DbDiffCommit


	/**
	 * Do DbDiff() and commit diff result
	 *
	 * Param and result same with DbDiff()
	 *
	 * @param	array	$ar_new
	 * @param	Adodb	$db
	 * @param	array	$ar_old
	 * @return	array
	 * @see DbDiff()
	 */
	public function DbDiffExec (array $ar_new, Adodb $db = NULL, array $ar_old = NULL) {
		$ar_diff = $this->DbDiff($ar_new, $db, $ar_old);
		if (0 == $ar_diff['code']) {
			$i = $this->DbDiffCommit($ar_diff);
			if (0 > $i)
				$this->Log('DbDiffExec error: ' . $i, 4);
		}

		return $ar_diff;
	} // end of func DbDiffExec


	/**
	 * Rollback committed DbDiff() result
	 *
	 * @param	array	$ar_diff		Same with DbDiff()'s result
	 * @param	Adodb	$db
	 * @return	int						Rows modified, < 0 when error.
	 * @see DbDiff()
	 */
	public function DbDiffRollback (array &$ar_diff, Adodb $db = NULL) {
		// Condition check
		if (empty($ar_diff) || empty($ar_diff['diff']))
			// No diff data
			return -2;
		if (0 > $ar_diff['code'])
			// Previous op not successful
			return -3;
		if (100 != $ar_diff['flag'])
			// Not committed
			return -4;

		if (is_null($db))
			$db = $this->oDb;

		// Generate sql
		$ar_sql_all = array();
		foreach ($ar_diff['diff'] as $tbl => $ar_rows) {
			if (empty($ar_rows))
				continue;
			foreach ($ar_rows as $i_row => $row) {
				$ar_sql = array();
				switch ($row['mode']) {
					case 'INSERT':
						$ar_sql['DELETE'] = $tbl;
						foreach ($row['pk'] as $k => $v)
							$ar_sql['WHERE'][] = $k . ' = '
								. $db->QuoteValue($tbl, $k, $v['new']);
						// Limit rowcount to 1 for safety
						$ar_sql['LIMIT'] = 1;
						break;
					case 'DELETE':
						$ar_sql['INSERT'] = $tbl;
						$ar_col = $row['pk'] + $row['col'];	// Sure not empty
						foreach ($ar_col as $k => $v)
							$ar_sql['VALUES'][$k] = $v['old'];
						break;
					case 'UPDATE':
						$ar_sql['UPDATE'] = $tbl;
						foreach ($row['col'] as $k => $v)
							$ar_sql['SET'][$k] = $v['old'];
						foreach ($row['pk'] as $k => $v)
							$ar_sql['WHERE'][] = $k . ' = '
								. $db->QuoteValue($tbl, $k, $v['old']);
						// Limit rowcount to 1 for safety
						$ar_sql['LIMIT'] = 1;
						break;
				}

				if (!empty($ar_sql)) {
					$ar_sql_all[] = $db->GenSql($ar_sql);
				}
			}
		}

		// Execute sql
		$i_cnt = 0;
		if (!empty($ar_sql_all)) {
			$b_error = false;
			$db->BeginTrans();
			while (!$b_error && !empty($ar_sql_all)) {
				$db->Execute(array_shift($ar_sql_all));
				if (0 != $db->ErrorNo()) {
					$b_error = true;
					$this->Log('DbDiffRollback error ' . $db->ErrorNo()
						. ' : ' . $db->ErrorMsg());
				}
				else
					$i_cnt += $db->Affected_Rows();
			}

			if ($b_error) {
				$ar_diff['code'] = abs($db->ErrorNo()) * -1;
				$ar_diff['msg'] = $db->ErrorMsg();
				$db->RollbackTrans();
				return -1;
			}
			else {
				$db->CommitTrans();
				// Modify diff info
				$ar_diff['code'] = $i_cnt;
				$ar_diff['flag'] = -100;
				return $i_cnt;
			}
		}
		return $i_cnt;
	} // end of func DbDiffRollback


	/**
	 * Compare a row's for DbDiff()
	 *
	 * Param and result structure, see DbDiff()
	 *
	 * $ar_new MUST contain all PK columns.
	 *
	 * @param	array	$ar_new
	 * @param	array	$ar_old
	 * @param	array	$ar_pk			NULL to use first col in $ar_new
	 * @return	array
	 * @see DbDiff()
	 */
	public function DbDiffRow (array $ar_new, array $ar_old = NULL
		, array $ar_pk = NULL) {
		// Check param
		if (is_null($ar_pk)) {
			$ar_pk = array_keys($ar_new);
			$ar_pk = array($ar_pk[0]);
		}

		$ar_diff = array();

		// Detect mode: INSERT/UPDATE/DELETE
		$b_new_null = false;
		$b_old_null = false;
		foreach ($ar_pk as $s_pk) {
			if (is_null($ar_new[$s_pk]))
				$b_new_null = true;
			elseif ($b_new_null)
				// Mixed NULL with non-NULL PK value
				$this->Log('New array PK ' . $s_pk . ' got value and mixed'
					. ' with other NULL PK.', 4);

			if (!isset($ar_old[$s_pk]) || is_null($ar_old[$s_pk]))
				$b_old_null = true;
			elseif ($b_old_null)
				// Mixed NULL with non-NULL PK value
				$this->Log('Old array PK ' . $s_pk . ' got value and mixed'
					. ' with other NULL PK.', 4);
		}
		if (true == $b_new_null && false == $b_old_null) {
			$ar_diff['mode'] = 'DELETE';
		}
		elseif (false == $b_new_null && true == $b_old_null) {
			$ar_diff['mode'] = 'INSERT';
		}
		elseif (false == $b_new_null && false == $b_old_null) {
			$ar_diff['mode'] = 'UPDATE';
		}
		else {
			// New, old are all NULL PK, should not occur
			$this->Log('New and old array\'s PK are all NULL, nothing to do.', 4);
			return array();
		}

		// PK, include even new same with old
		foreach ($ar_pk as $s_pk) {
			$ar_diff['pk'][$s_pk] = array(
				'new'	=> $ar_new[$s_pk],
				'old'	=> isset($ar_old[$s_pk]) ? $ar_old[$s_pk] : NULL,
			);
			// Remove it in normal column
			unset($ar_new[$s_pk]);
			unset($ar_old[$s_pk]);
		}

		// Other column, skip same value
		$ar_diff['col'] = array();
		$ar_col = array();
		if (!empty($ar_new))
			$ar_col += array_keys($ar_new);
		if (!empty($ar_old))
			$ar_col += array_keys($ar_old);
		if (!empty($ar_col)) {
			foreach ($ar_col as $col) {
				$v_new = isset($ar_new[$col]) ? $ar_new[$col] : NULL;
				$v_old = isset($ar_old[$col]) ? $ar_old[$col] : NULL;

				// Manual set useless column data to NULL, avoid necessary
				// column been skipped by equal check later, to keep diff
				// result include necessary column, they maybe used in
				// rollback.
				// Force new value of DELETE mode to NULL
				if ('DELETE' == $ar_diff['mode'])
					$v_new = NULL;
				// Force old value of INSERT mode to NULL
				if ('INSERT' == $ar_diff['mode'])
					$v_old = NULL;

				if (is_null($v_new) && is_null($v_old))
					continue;
				if (!is_null($v_new) && !is_null($v_old) && $v_new == $v_old)
					continue;

				$ar_diff['col'][$col] = array(
					'new'	=> $v_new,
					'old'	=> $v_old,
				);
			}
		}

		// Skip UPDATE with no col change
		if ('UPDATE' == $ar_diff['mode'] && empty($ar_diff['col']))
			$ar_diff = array();

		return $ar_diff;
	} // end of func DbDiffRow


	/**
	 * Define id relation between db and form - action name
	 *
	 * Key is id from db, value id id from form.
	 * So we can easily turn data between from/post and db.
	 *
	 * If one side is not directly assign from another side,
	 * 	do not define it here,
	 * 	they should be specially treated in other method
	 * 	after use this to treat all other easy ones.
	 *
	 * This is only an example func.
	 * @return	array
	 */
/*
	protected function FormActionNameDef () {
		$ar = array();
		$this->FormDefSameId($ar, 'field_same_id');
		$ar['id_db']		= 'id_form';

		return $ar;
	} // end of func FormActionNameDef
*/


	/**
	 * Define id relation between db and form, the same id ones
	 *
	 * For detail note, see example func FormActionNameDef().
	 * @param	array	&$ar	Config array
	 * @param	string	$id		Field id
	 */
	protected function FormDefSameId (&$ar, $id) {
		$ar[$id] = $id;
	} // end of func FormDefSameId


	/**
	 * Get data from form, according setting in FormActionNameDef()
	 *
	 * Data source is $_POST.
	 * @param	string	$form	Form name
	 * @return	array
	 */
	public function FormGet ($form) {
		$s_form = 'Form' . StrUnderline2Ucfirst($form, true) . 'Def';

		// If define method missing, return empty array
		if (false == method_exists($this, $s_form))
			return array();

		// Do data convert

		$ar_conf = $this->{$s_form}();
		// Let key is id from form
		$ar_conf = array_flip($ar_conf);

		$ar = array();
		if (!empty($ar_conf)) {
			foreach ($ar_conf as $k_form => $k_db) {
				$ar[$k_db] = GetPost($k_form);
			}
		}

		return $ar;
	} // end of func FormGet


	/**
	 * Prepare data from db for form display
	 *
	 * According setting in FormActionNameDef()
	 * @param	string	$form	Form name
	 * @return	array	Can use in Form::AddElementValue()
	 * @see	Form::AddElementValue()
	 */
	public function FormSet ($form) {
		$s_form = 'Form' . StrUnderline2Ucfirst($form, true) . 'Def';

		// If define method missing, return empty array
		if (false == method_exists($this, $s_form))
			return array();

		// Do data convert

		// Key is id from db
		$ar_conf = $this->{$s_form}();

		$ar = array();
		if (!empty($ar_conf)) {
			foreach ($ar_conf as $k_db => $k_form) {
				$ar[$k_form] = HtmlEncode($k_db);
			}
		}

		return $ar;
	} // end of func FormSet


	/**
	 * New Cache instance
	 * Shoud be overwrited by sub class if use cache.
	 *
	 * @return object
	 */
	protected function NewObjCache () {
		return Cache::Create('');
	} // end of func NewObjCache


	/**
	 * New db object
	 *
	 * @return object
	 */
	protected function NewObjDb () {
		return $this->DbConn($this->aCfg['dbprofile']);
	} // end of func NewObjDb


	/**
	 * Set default config.
	 *
	 * @return	object
	 */
	 protected function SetCfgDefault () {
		 parent::SetCfgDefault();

		// Db profile
		$this->aCfg['dbprofile'] = array(
			'type'	=> 'mysql',
			'host'	=> 'localhost',
			'user'	=> 'user',
			'pass'	=> 'pass',
			'name'	=> 'name',
			'lang'	=> 'utf-8',
		);

		return $this;
	 } // end of func SetCfgDefault


} // end of class Module
?>
