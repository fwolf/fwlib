<?php
require_once(dirname(__FILE__) . '/fwolflib.php');
require_once(FWOLFLIB . 'func/string.php');


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
		unset($this->oDb);

		parent::__construct();

		if (!is_null($view))
			$this->oView = &$view;
	} // end of func __construct


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
