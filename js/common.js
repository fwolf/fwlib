/**
 * Js functions for common usage.
 *
 * Using jQuery now, some are useless now.
 *
 * @package		fwolflib
 * @subpackage	js
 * @copyright	Copyright © 2011-2013, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.js@gmail.com>
 * @license		http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since		2011-07-26
 */


/**
 * 取得某一对象指定部分的值
 *
 * jQuery can do this job better.
 *
 * @param   string  obj     目标对象的名称
 * @param   string  part    取值部分
 * @return  string
 */
function GetData (obj, part) {
    if ('value' == part) {
        return GetElement(document, obj).value;
    }
    if ('innerHTML' == part) {
        return GetElement(document, obj).innerHTML;
    }
	if ('src' == part) {
		return GetElement(document, obj).src;
	}
    if ('text' == part) {
        return GetElement(document, obj).text;
    }
} /* end of func GetData */


/**
 * Get document scrollTop
 *
 * @see		GetWindowHeight()
 * @return	int
 */
function GetScrollTop () {
	return (document.body.scrollTop
		|| document.documentElement.scrollTop);
} /* end of func GetScrollTop */


/**
 * Get window height
 *
 * IE 6 enable quirks mode if html start with <xml, cause jQuery
 * $(window).height() always return 0, so did some other property,
 * use native js method to avoid this.
 *
 * @return	int
 */
function GetWindowHeight () {
	return (window.innerHeight
		|| document.documentElement.offsetHeight);
} /* end of func GetWindowHeight */


/**
 * 解决 js 兼容性的函数，取得某一控件的句柄
 * 在调用的时候把 document 对象传进去就OK了
 *
 * jQuery can do this job better.
 *
 * @param   object  parentobj   父对象
 * @param   string  id          子对象的ID
 * @return  handle
 */
function GetElement (parentobj, id) {
    var obj;
    if (!parentobj) return null;
    if (parentobj.getElementById) {
        obj = parentobj.getElementById(id);
    }
    else if (parentobj.all) {
        obj = parentobj.all[id];
    }
    else if (parentobj.layers) {
        obj = parentobj.layers[id];
    }
    return obj;
} /* end of func GetElement */


/**
 * Simulate object length
 *
 * In js, only Array have length, Object does not.
 *
 * @param	object	obj
 * @return	int
 */
function ObjLen (obj) {
	return Object.keys(obj).length;
} /* end of func ObjLen */


/**
 * Convert Object to json string, like Array
 *
 * @param	object	obj
 * @return	string
 * @link	http://jsfiddle.net/5wmLC/1/
 */
function ObjToStr (obj) {
	return JSON.stringify(obj, null, 2);
} /* end of func ObjToStr */


/**
 * 打开一个新窗口，并返回 false
 *
 * @param   string  url     地址
 * @param   string  winname 窗口名称
 * @param   string  spec    窗口打开参数
 * @param   boolean focus   打开窗口后是否自动显示在前台
 * @return	boolean
 */
function OpenWindow (url, winname, spec, focus) {
/*  var newwin=window.open(url,"select_jsdw","toolbar=no,Location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=400,height=550,left=50,top=50"); */
/*  var newwin=window.open(url,"",""); */
	var win = window.open(url, winname, spec);
	if (true == focus) {
		win.focus();
	}
	return false;
} /* end of func OpenWindow */


/**
 * 接受参数，并将内容写入指定对象的指定区域
 *
 * jQuery do this job better.
 *
 * @param   string  id      目标对象的ID
 * @param   string  part    目标对象的子区域
 * @param   string  data    要写入的数据
 */
function SetData (id, part, data) {
    if ('value' == part) {
        GetElement(document, id).value = data;
    }
    if ('innerHTML' == part) {
        GetElement(document, id).innerHTML = data;
    }
	if ('src' == part) {
		GetElement(document, id).src = data;
	}
    if ('text' == part) {
        GetElement(document, id).text = data;
    }
} /* end of func SetData */
