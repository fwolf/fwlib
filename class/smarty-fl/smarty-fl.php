<?php
//require_once('smarty/Smarty.class.php');

/**
 * Extended Smarty class
 *
 * @package		fwolflib
 * @subpackage	class.smarty-fl
 * @copyright	Copyright 2013, Fwolf
 * @author		Fwolf <fwolf.aide+class.smarty-fl@gmail.com>
 * @license		http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since		2013-01-18
 */
class SmartyFl extends Smarty {


	/**
	 * constructor
	 */
	public function __construct () {
		parent::__construct();

        // Delimiter
		$this->left_delimiter = '{';
		$this->right_delimiter = '}';

		$this->use_sub_dirs = true;

	} // end of func __construct


	/**
	 * Prepend to config_dir array
	 *
	 * @param	string|array	$config_dir
	 * @param	string			$key
	 * @return	$this
	 */
	public function addConfigDirPrepend ($config_dir, $key = '') {
		$ar_tpl = $this->getConfigDir();
		if (is_string($ar_tpl))
			$ar_tpl = array($ar_tpl);

		if (is_string($config_dir)) {
			$config_dir = array($key => $config_dir);
		}

		$ar_tpl = array_merge($config_dir, $ar_tpl);
		$this->setConfigDir($ar_tpl);

		return $this;
	} // end of func addConfigDirPrepend


	/**
	 * Prepend to plugins_dir array
	 *
	 * @param	string|array	$plugins_dir
	 * @param	string			$key
	 * @return	$this
	 */
	public function addPluginsDirPrepend ($plugins_dir, $key = '') {
		$ar_tpl = $this->getPluginsDir();
		if (is_string($ar_tpl))
			$ar_tpl = array($ar_tpl);

		if (is_string($plugins_dir)) {
			$plugins_dir = array($key => $plugins_dir);
		}

		$ar_tpl = array_merge($plugins_dir, $ar_tpl);
		$this->setPluginsDir($ar_tpl);

		return $this;
	} // end of func addPluginsDirPrepend


	/**
	 * Prepend to template_dir array
	 *
	 * @param	string|array	$template_dir
	 * @param	string			$key
	 * @return	$this
	 */
	public function addTemplateDirPrepend ($template_dir, $key = '') {
		$ar_tpl = $this->getTemplateDir();
		if (is_string($ar_tpl))
			$ar_tpl = array($ar_tpl);

		if (is_string($template_dir)) {
			$template_dir = array($key => $template_dir);
		}

		$ar_tpl = array_merge($template_dir, $ar_tpl);
		$this->setTemplateDir($ar_tpl);

		return $this;
	} // end of func addTemplateDirPrepend


} // end of class SmartyFl
?>
