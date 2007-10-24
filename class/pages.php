<?php
/**
 * @package      fwolflib
 * @copyright    Copyright 2004-2007, Fwolf
 * @author       Fwolf <fwolf.aide@gmail.com>
 */

/**
 * Generate web pages
 *
 * @package    fwolflib
 * @copyright  Copyright 2004-2007, Fwolf
 * @author     Fwolf <fwolf.aide@gmail.com>
 * @since      2004-4-13 22:50:52
 * @access     public
 * @version    $Id$
 */
class Pages
{
	/**
	 * Debug mode
	 * @access	protected
	 * @var	boolean
	 */
	protected var $mDebug = false;

	/**
	 * Parts of html heads, this is NOT html code
	 * Sample value: 'meta'=>array('..', '...'), 'title'='...'
	 * @access	protected
	 * @var	array
	 */
	protected var $mHead = array();

	/**
	 * Parts of html code before <head>, this is NOT html code
	 * Sample value: 'xml'=>array('..', '...'), 'doctype'=>'...'
	 * @access	protected
	 * @var	array
	 */
	protected var $mHeadahead = array();

	/**
	 * Result html content
	 * $mHtml = $mHtmlHeadahead + $mHtmlHead + $mHtmlBody
	 * And + some <marks> between these parts.
	 * @access	protected
	 * @var	string
	 */
	protected var $mHtml = '';

	/**
	 * Html content part of body
	 * Code of '<body>' part.
	 * @access	protected
	 * @var	string
	 */
	protected var $mHtmlBody = '';

	/**
	 * Html content part of head
	 * Code of '<head>' part.
	 * @access	protected
	 * @var	string
	 */
	protected var $mHtmlHead = '';

	/**
	 * Html content part of html_head
	 * Code ahead of '<head>', xml, doctype & html marks.
	 * @access	protected
	 * @var	string
	 */
	protected var $mHtmlHeadahead = '';

	
    /**
     * 构造函数
     */
    function __construct()
	{
		//开始输出缓冲区
		ob_start();

		//开始SESSION
		//调试程序的时候因为程序经常改动，所以不使用CACHE
		if (true == $this->mDebug)
			session_cache_limiter('private,must-revalidate');
		session_start();
    } // end of func __construct

	
	/**
	 * 显示网页正式内容
	 * @param	boolean	$return	Return contents instead of print out.
	 * @access	public
	 */
	public function Display($return=false)
	{
		if (empty($this->mHtml))
			$this->GenHtml();
		if (true == $return)
			return $this->mHtml;
		else{
			echo $this->mHtml;
			//结束输出缓冲区，输出内容
			ob_end_flush();
		}
	} // end of func Display


	/**
	 * Generate body part of html code
	 * @access	protected
	 * @return	string
	 */
	abstract protected function GenBody();


	/**
	 * Generate head part of html code
	 * @access	proteced
	 * @return	string
	 */
	protected function GenHead()
	{
		$this->mHtmlHeadahead = '';
		// Xml format declaration
		if (array_key_exists('xml', $this->mHeadahead))
			$this->mHtmlHeadahead .= $this->GenHeadXml() . "\n";
		// Doctype

	} // end of func GenHead


	/**
	 * Generate xml declaration part of head_ahead
	 * $this->mHeadahead['xml'] = array('version'=>'1.0', 'encoding'='utf-8')
	 * @access	protected
	 * @return	string
	 */
	protected function GenHeadXml()
	{
		if (array_key_exists('xml', $this->mHeadahead)) {
			$ar = $this->mHeadahead['xml'];
			return "<?xml version=\"{$ar['version']}\" encoding=\"{$ar['encoding']}\"?>";
		}
		else
			return '';
	} // end of func GenHeadXml


	/**
	 * Generate html code from joining several parts
	 * @access	protected
	 * @return	string
	 */
	protected function GenHtml()
	{
		$this->mHtml = '';
		$this->mHtml .= $this->GenHead;
		$this->mHtml .= $this->GenBody;
		$this->mHtml .= "</body>\n</html>";
		return $this->mHtml;
	} // end of func GenHtml


	/**
	 * Set xml part of head_ahead
	 * @access	public
	 * @param	string	$version
	 * @param	string	$encoding
	 */
	public function SetHeadXml($version = '1.0', $encoding='utf-8') {
		$this->mHeadahead['xml']['version'] = $version;
		$this->mHeadahead['xml']['encoding'] = $encoding;
	} // end of func SetHeadXml


} // end of class Pages

?>
