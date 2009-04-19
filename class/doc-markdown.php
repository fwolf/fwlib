<?php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2009, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-class@gmail.com>
 * @since		2009-04-12
 * @version		$Id$
 */

/**
 * Display text document writing by MarkDown markup language.
 * 
 * Need jQuery locate at /js/jquery.js
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2009, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-class@gmail.com>
 * @since		2009-04-12
 * @version		$Id$
 */
class DocMarkdown
{
	/**
	 * Stored css styles
	 * @var array
	 */
	protected $aCss = array();
	
	/**
	 * Stored css styles for print
	 * @var array
	 */
	protected $aCssPrint = array();
	
	/**
	 * Footer text
	 * @var array
	 */
	protected $aFooter = array();
	
	/**
	 * Header text
	 * @var array
	 */
	protected $aHeader = array();
	
	/**
	 * Document infomation
	 * 
	 * Will display out by sequence of their index,
	 * Do NOT change there sequence.
	 * @var array
	 */
	public $aInfo = array(
		'title'			=> "Document title(aInfo['title'])",
		'author'		=> "Document author(aInfo['author'])",
		'authormail'	=> "Document author's mail(aInfo['authormail'])",
		'keywords'		=> "Keywords used in html <meta>(aInfo['keywords'])",
		'description'	=> "Description used in html <meta>(aInfo['description'])",

		// Above part can be set (aBody[]) in doc script.
		// Below 2 parts can be rewritten in __construct of subclass
		 
		// Verinfo part
		'package'		=> "Phpdoc style package(aInfo['package'])",
		'subpackage'	=> "Phpdoc style subpackage(aInfo['subpackage'])",
		'copyright1'	=> "Copyright show in verinfo(aInfo['copyright1'])",
		'since'			=> "When is this file born(aInfo['since'])",
		'version'		=> "\$Id\$",
		// Footer part
		'copyright2'	=> array(
			"Copyright show in footer(aInfo['copyright2'])",
			"Will display in footer by list style.",
		),
	);
	
	/**
	 * Body text
	 * 
	 * Will display out by sequence of their index if is array,
	 * or easy output if is string.
	 * @var mixed
	 */
	public $aBody = array();

	/**
	 * Document styles profiles
	 * 
	 * Combined css, header, footer profile together
	 * @var array
	 */
	protected $aStyle = array(
		'default'	=> array(
			'css'		=> 'default',
			'cssprint'	=> 'default',
			'header'	=> 'default',
			'footer'	=> 'default',
		),
	);
	
	/**
	 * Which style profile to use
	 * @var string
	 */
	public $sStyle = 'default';
	
	
	/**
	 * construct
	 * 
	 * @var param	string	$path_markdown	Include path of MarkDown(Extra) lib
	 */
	public function __construct($path_markdown = 'markdown.php') {
		// Include Markdown lib
		if (empty($path_markdown))
			$path_markdown = 'adodb/adodb.inc.php';
		require_once($path_markdown);
		
		// Do data initialize
		$this->SetCss();
		$this->SetCssPrint();
		$this->SetHeader();
		$this->SetFooter();
		
		$this->SetInfoFwolflib();
	} // end of class __construct
	
	
	/**
	 * Echo $this->GetOutput()
	 */
	public function Display() {
		echo $this->GetOutput();
	} // end of func Display
	
	
	/**
	 * Generate output html
	 * @return string
	 */
	public function GenOutputHtml() {
		$s = '';
		$s .= $this->GenOutputHtmlHeader();
		$s .= $this->GenOutputHtmlBody();
		$s .= $this->GenOutputHtmlFooter();
		return $s;
	} // end of func GenOutputHtml
	
	
	/**
	 * Generate output html body part
	 * @return string
	 */
	protected function GenOutputHtmlBody() {
		if (empty($this->aBody)) {
			return '';
		}
		elseif (is_string($this->aBody)) {
			return Markdown($this->aBody);
		} 
		else {
			// Disp array by sequence of their index
			ksort($this->aBody);
			$s = '';
			foreach ($this->aBody as $k => $v) {
				$s .= Markdown($v);
			}
			return $s;
		}
	} // end of func GenOutputHtmlBody
	
	
	/**
	 * Generate output html header part
	 * @return string
	 */
	protected function GenOutputHtmlFooter() {
		$s = $this->aFooter[$this->aStyle[$this->sStyle]['footer']];
		// Footer copyright
		$ar = $this->aInfo['copyright2'];
		$s_copyright2 = '';
		if (!empty($ar)) {
			foreach ($ar as $v) {
				$s_t = Markdown($v);
				if ('<p>' == substr($s_t, 0, 3))
					$s_t = trim(substr($s_t, 3, strlen($s_t) - 8));
				$s_copyright2 .= "<li>" . $s_t . "</li>\n";
			}
		}
		$s = str_replace('{copyright2}', $s_copyright2, $s);
		return $s;
		// Apply aInfo in
		
	} // end of func GenOutputHtmlFooter
	
	
	/**
	 * Generate output html header part
	 * @return string
	 */
	protected function GenOutputHtmlHeader() {
		// Apply aInfo in
		$s = $this->aHeader[$this->aStyle[$this->sStyle]['header']];
		$ar_keys = array(
			'{info.title}',
			'{info.author}',
			'{info.authormail}',
			'{info.keywords}',
			'{info.description}',
			'{info.package}',
			'{info.subpackage}',
			'{info.copyright1}',
			'{info.since}',
			'{info.version}',
			);
		$s = str_replace($ar_keys, $this->aInfo, $s);
		// Css
		$s = str_replace('{css}', $this->aCss[$this->aStyle[$this->sStyle]['css']], $s);
		$s = str_replace('{cssprint}', $this->aCssPrint[$this->aStyle[$this->sStyle]['cssprint']], $s);
		return $s;
	} // end of func GenOutputHtmlHeader
	
	
	/**
	 * Gen soucecode output
	 * @return string
	 */
	protected function GenOutputSourcecode() {
		// Add <pre>
		$s = "<pre>\n";
		// Add title
		$s .= "{$this->aInfo['title']}\n====================\n\n";
		
		// Verinfo, grab from $this->aHeader
		// identify by <div id="verinfo"><pre>
		$s_header = $this->GenOutputHtmlHeader();
		$i = preg_match('/<div id="verinfo"><pre>(.*)<\/pre><\/div>/s',
			$s_header, $ar);
		if (0 < $i) {
			$s_verinfo = $ar[1];
			$s_verinfo = strip_tags($s_verinfo);
			$s_verinfo = str_replace('	', ' ', $s_verinfo);
			$s .= $s_verinfo . "\n\n";
		}
		
		// Output body
		asort($this->aBody);
		foreach ($this->aBody as $k => $v) {
			$s .= htmlspecialchars($v) . "\n";
		}
		
		// Add </pre>
		$s .= "</pre>\n";
		return $s;
	} // end of func GenOutputSourcecode
	
	
	/**
	 * Get output content
	 * @return string
	 */
	public function GetOutput() {
		// Sourcecode output
		if (isset($_GET['view']) && 'sourcecode' == $_GET['view'])
			return $this->GenOutputSourcecode();
		else 
			return $this->GenOutputHtml();
	} // end of func GetOutput
	
	
	/**
	 * Store css data to array
	 */
	protected function SetCss() {
		$this->aCss['default'] = '
html, body, div, span, applet,
    object, iframe, h1, h2, h3, h4, h5, h6,
    p, blockquote, pre, a, abbr, acronym,
    address, big, cite, code, del, dfn, em,
    font, img, ins, kbd, q, s, samp, small, strike,
    strong, sub, sup, tt, var, dd, dl, dt,
    li, ol, ul, fieldset, form, label, legend,
    table, caption, tbody, tfoot, thead,
    tr, th, td {
      margin: 0;
      padding: 0;
      border: 0;
      font-weight: inherit;
      font-style: inherit;
      font-size: 100%;
      line-height: 1.2em;
      font-family: inherit;
      text-align: left;
      vertical-align: baseline;
    }

    a img, :link img, :visited img {
    	border: 0;
    }

    table {
    	border-collapse: collapse;
    	border-spacing: 0;
    }

    ol, ul {
    	list-style: none;
    }

    q:before, q:after,
    blockquote:before, blockquote:after {
    	content: "";
    }
/*
    注意两点，这里定义了背景色和前景色，这是标准要求的，是网页可用性的一个基本方面，大家可以执行修改。

    第二点，就是font-size的问题，为了让网页更好的支持网页缩放功能，应该使用em来替换px，这样会让ie6等上古浏览器也能良好的支持网页缩放。浏览器的默认字体高都是16px，所以未经调整的浏览器在显示1em=16px。换算过来的话也就是说1px=0.0625em，也就是12px =0.75em, 10px=0.625em，通过1px=0.0625em大家可以在CSS编写时通过px转换成em。
*/
    body {background-color: #FFFFFF;}
    body, p, td, th, li
    {
     font-family: "宋体", verdana, helvetica, sans-serif;
     font-size: 0.875em;
     line-height: 1.5em;
     color: #000000;
    }
/*
	-------- Styles come from other framework
	My change
*/
	h1, h2, h3, h4, h5, h6 {
		font-weight: bold;
		font-family: "黑体", inherit;
	}
	h1 {
		font-size: 2em;
		line-height: 3em;
	}
	h2 {
		font-size: 1.5em;
		line-height: 2em;
		margin-left: 1em;
	}
	h3 {
		font-size: 1.3em;
		line-height: 2em;
		margin-left: 1.154em;
	}
	h4, h5, h6 {
		font-size: 1.1em;
		line-height: 2em;
		margin-left: 1.364em;
	}
	p, td, th, li {
		font-size: 1em;
		line-height: 2em;
		margin-left: 3em;
	}
    pre {
    	margin-left: 5em;
    }
    th {
    	font-weight: bold;
    	text-align: center;
    }
	th, td {
		padding-left: 0.5em;
		padding-right: 0.5em;
	}
/*
	-------- Styles come from other framework
	End
*/
	

body {
	background-color: rgb(204, 232, 207);
}
code {
	color: #900;
	font-size: 1em;
}
abbr, acronym {
	cursor: help;
}

/* Title and list */
h1 {
	text-align:center;
}
div.list {
	line-height: 1.2em;
}
/* general unorder list */
ul.gen li {
	list-style-type: disc;
}

/* link color method 1
	a:hover, span.showhide_link:hover {background-color: #bcf; color: #f34;} */
a {
	text-decoration: none;
}
a:hover {
	background-color: #b50394;
	color: #fff; 
}

#footer li{
	line-height: 1.5em;
	margin-right: 1em;
	text-align: center;
}

/* Version info */
#verinfo {
	display: none;
	line-height: 1.2em;
	background-color: #ded9af;
	width: 50em;	/* 需要根据内容大小适当调整 */
	border: solid black 1px;
	position: absolute;
	overflow: hidden;
	z-index: 100;
	margin: -1em 0em 0em 1em;
}
#verinfo pre{
	/*margin: 0em 0em 0em -2em;*/
	margin: 0em 0em 0em -2.5em;
}
#showhide_verinfo {
	position: absolute;
	top: 0.5em; left: 0.5em;
}

/* To solve problem: img tag in <a> have bgcolor. */
a.img {
	background-color: transparent !important;
	background-color: #000;
}

/* Footer icon and copyright text. */
div#footer img, div#footer span.spacer {
	float: left;
	margin-top: 0.2em;
}
div#footer ul#copyright {
	float: right;
	margin-right: 0%;
	margin-top: 0%;
	text-align: center;
}
hr {
	border: 0px;
	height: 1px;
	color: #B0C4DE;
	background-color: #B0C4DE;	 /* LightSteelBlue = #B0C4DE */
}
#ads_bottom {
	text-align: center;
}


/* Auto numbered list(article), usually used in doc */
.article {counter-reset: c-level1} /* top c_lever need not reset */
.article h2 {counter-reset: c-level2}
.article h3 {counter-reset: c-level3}
.article h4 {counter-reset: c-level4}
.article h2:before {
	/*display: marker; */
	content: "§ " counter(c-level1, decimal) "、"; 
	counter-increment: c-level1 1
}
.article h3:before {
	/*display: marker; */
	content: "§ " counter(c-level1) "." counter(c-level2, decimal) "、"; 
	counter-increment: c-level2 1
}
.article h4:before {
	/*display: marker; */
	content: "§ " counter(c-level1) "." counter(c-level2, decimal) "." counter(c-level3, decimal) "、"; 
	counter-increment: c-level3 1
}
.article h5:before {
	/*display: marker; */
	content: "§ " counter(c-level1) "." counter(c-level2, decimal) "." counter(c-level3, decimal) "." counter(c-level4, decimal) "、"; 
	counter-increment: c-level4 1
}
.article li {
	list-style-type: disc;
	margin-left: 6em;
}
.article table {
	margin-left: 4em;
}
.article p {
	text-indent: 2em;
}
/* p in li need not margin/indent, it will display as same as li */
.article li p {
	margin-left: 0em;
	text-indent: 0em;
}


/* Temportary debug can apply this class */
.debug {
	border: 2px solid red;
}


/* Single line table in div, printable */
.single_line table, .single_line td, .single_line th {
	border: 1px solid black;
	border-collapse: collapse;
}
		';
	} // end of func SetCss
	
	
	/**
	 * Store css for print data to array
	 */
	protected function SetCssPrint() {
		$this->aCssPrint['default'] = '
#header, #footer, #navigation, #menu, #right_sidebar, #left_sidebar
{display:none;}
			';
	} // end of func SetCssPrint
	
	
	/**
	 * Store footer data to array
	 */
	protected function SetFooter() {
		$this->aFooter['default'] = "
</div>
	<script type=\"text/javascript\">
	/**
	 * Set Display/Hide action to special verinfo div
	 */
	function SetSvninfo()
	{
		var obj = $('#showhide_verinfo').children(':first-child');
		//$('#showhide_verinfo').mouseover(function(e) {
		obj.mouseover(function(e) {
			SwitchDisplay('#verinfo', 'block', e);
			// Fix position
			if ('static' != $('#verinfo').css('position'))
				$('#verinfo').css('margin', '0em 0em 0em 0em');
		});
		//$('#showhide_verinfo').mouseout(function(e) {
		obj.mouseout(function(e) {
			SwitchDisplay('#verinfo', 'none', e);
		});
		//$('#showhide_verinfo').children(':first-child').click(function() {
		//$('#showhide_verinfo a').click(function() {
		obj.click(function() {
			SwitchPosition('#verinfo');
			// And reset position when static position
			// Style same with define in default.css
			if ('static' == $('#verinfo').css('position'))
				$('#verinfo').css('margin', '-1em 0em 0em 1em');
			return false;
		});
	} // end of func SetSvninfo
	
	
	/**
	 * 显示/隐藏指定的对象(style.display方式)
	 * @param	string	id		对象selector, jQuery format
	 * @param	string	value	指定block/none，如省略则自动切换
	 * @param	object	e		事件，用于捕捉鼠标位置等，可省略
	 */
	function SwitchDisplay(id, value, e)
	{
		var obj = $(id);
		//如果对象定位方式是static，就是已经采用非浮动方式显示，则跳过处理
		if ('static' == obj.css('position')) return null;
		
		// Reference: http://www.quirksmode.org/js/events_properties.html
		if (!e) var e = window.event;
		// 定位，可选
		if (e.pageX || e.pageY) {
			posx = e.pageX;
			posy = e.pageY;
		}
		else if (e.clientX || e.clientY) {
			posx = e.clientX + document.body.scrollLeft
				+ document.documentElement.scrollLeft;
			posy = e.clienty + document.body.scrollTop
				+ document.documentElement.scrollTop;
		}
		//obj.css('top', e.clientX);
		//obj.css('left', e.clientY);
		obj.css('left', posx);
		obj.css('top', posy);
		
		//显示/隐藏
		if (('' == obj.css('display'))
			|| ('block' == value)
			|| ('none' == obj.css('display'))
			//|| (undefined == obj.css('display'))
			)
			obj.css({display: 'block'})
		else if (('block' == obj.css('display')) || ('none' == value))
			obj.css({display: 'none'});
	} // end of function SwitchDisplay
	
	
	/**
	 * 切换对象的定位方式
	 * @param	string	id		对象selector, jQuery format
	 * @param	string	value	指定static/absolute，如省略则自动切换
	 */
	function SwitchPosition(id, value)
	{
		var obj = $(id);
		if (('' == obj.css('position'))
			|| ('static' == value)
			|| ('absolute' == obj.css('position')))
			obj.css('position', 'static');
		else if (('static' == obj.css('position'))
			|| ('absolute' == value))
			obj.css('position', 'absolute');
	} // end of function SwitchPosition
	
	
	// Auto set property
	SetSvninfo();

	</script>

	<div id=\"footer\">
		<hr />
		<ul id=\"copyright\">
			{copyright2}
		</ul>
	</div>

</body>
</html>
		";
	} // end of func SetFooter
	
	
	/**
	 * Store header data to array
	 */
	protected function SetHeader() {
		$this->aHeader['default'] = '<?' . 'xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Language" content="utf-8" />
	<meta name="Author" content="{info.author}" />
	<meta name="Keywords" content="{info.keywords}" />
	<meta name="Description" content="{info.description}" />
	
	<style type="text/css" media="screen, print">
	<!--
		{css}
	-->
	</style>
	<style type="text/css" media="print">
	<!--
		{cssprint}
	-->
	</style>
	
	<script type="text/javascript" src="/js/jquery.js"></script>
	
	<title>{info.title}</title>

</head>
<body>
<div class="article">

	<h1>{info.title}</h1>
	
	<div id="showhide_verinfo">
		<a href="">[v]</a>&nbsp; &nbsp; &nbsp; &nbsp; 
		<a href="?view=sourcecode" title="View Sourcecode">[s]</a>
	</div>
	
	<div id="verinfo"><pre>
	/**
	 * {info.title}
	 *
	 * @package     {info.package}
	 * @subpackage  {info.subpackage}
	 * @copyright   {info.copyright1}
	 * @author      {info.author} &lt;<a href="mailto:{info.authormail}" tabindex="1">{info.authormail}</a>&gt;
	 * @since       {info.since}
	 * @version     {info.version}
	 */</pre></div>
		';
	} // end of func SetHeader
	
	
	/**
	 * Set info array
	 */
	public function SetInfoFwolflib() {
		$this->aInfo['package']		= "fwolflib";
		$this->aInfo['subpackage']	= "doc";
		$this->aInfo['copyright1']	= "Copyright &copy; 2009, Fwolf";
		$this->aInfo['since']		= "2009-04-19";
		$this->aInfo['version']		= "\$Id\$";
		$this->aInfo['copyright2']	= array(
			'Copyright &copy; 2009, Fwolf',
			'All Rights Reserved.',
		);
	} // end of func SetInfoFwolflib
} // end of class DocMarkdown
?>
