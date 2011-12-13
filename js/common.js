/**
 * Js functions for common usage.
 *
 * Using jQuery now, many are useless now.
 *
 * @package		fwolflib
 * @subpackage	js
 * @copyright	Copyright © 2011, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.js@gmail.com>
 * @since		2011-07-26
 */


/**
 * 取得某一对象制定部分的值
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
 * 解决 js 兼容性的函数，取得某一控件的句柄
 * 在调用的时候把 document 对象传进去就OK了
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
