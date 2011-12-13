/**
 * Cookie操作JS函数集
 *
 * @package    MaGod
 * @copyright  Copyright 2003-2004, Fwolf
 * @author     Fwolf <fwolf001@tom.com>
 * @since      2004-3-1 17:08:59
 * @access     public
 * @version    $Id$
 */


/**
 * 取得指定Cookie的值
 * @param   string  cookiename
 * @access  public
 * @return  string
 */
function GetCookie(name)
{
    var arg = name + "=";
    var alen = arg.length;
    var clen = document.cookie.length;
    var i = 0;
    while (i < clen)
    {
        var j = i + alen;
        if (document.cookie.substring(i, j) == arg) return GetCookieVal(j);
        i = document.cookie.indexOf(" ", i) + 1;
        if (i == 0) break;
    }
    return null;
} /* end of function GetCookie */


/**
 * 得到具体的Cookie值，被{@see GetCookie}调用，不直接在外部使用
 * @param   int offset
 * @access  private
 * @return  string
 */
function GetCookieVal(offset)
{
    var endstr = document.cookie.indexOf(";", offset);
    if (endstr == -1) endstr = document.cookie.length;
    return unescape(document.cookie.substring(offset, endstr));
} /* end of function GetCookieVal */


/**
 * 设置一个具体的COOKIE值
 * @param   string  name
 * @param   string  value
 * @param   int     second  COOKIE保存的时间，单位是秒
 * @access  public
 */
function SetCookie(name, value, second)
{
    var exp = new Date();
    if (undefined == second)
    {
        /* COOKIE立刻超期，其实无用 */
        i_second = 0;
    }
    else
    {
        i_second = second;
    }
    exp.setTime(exp.getTime() + i_second * 1000);
    document.cookie = name + '=' + value + '; expires=' + exp.toGMTString();
} /* end of function SetCookie */
