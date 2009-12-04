<?php
/**
* @package     MaGod
* @copyright   Copyright 2003, Fwolf
* @author      Fwolf <fwolf001@163.net>
* @version    $Id$
*/

/**
* 增加或设置/更改URL参数
* @access   public
* @see      UnsetUrlParam()
* @param    string  $urlStr     要进行处理的URL地址
* @param    string  $strName    要添加的参数等号左边，参数名
* @param    string  $strValue   要添加的参数等号右边，参数值
* @return   string
*/
function SetUrlParam($urlStr, $strName, $strValue = '')
{
    if (empty($strName) && empty($strValue))
    {
        return($urlStr);
    }
    $ar = UrlToArray($urlStr);
    $i = 1;
    $is_found = 0;
    while (count($ar) > $i)
    {
        if ($strName == $ar[$i][0])
        {
            //已经有同名的参数了
            $ar[$i][1] = $strValue;
            $is_found ++;
        }
        $i++;
    }
    if (1 > $is_found)
    {
        //没有找到同名的参数
        array_push($ar, array($strName, $strValue));
    }
    return(ArrayToUrl($ar));
} // end function SetUrlParam

/**
* 去掉URL参数
* @access   public
* @see      SetUrlParam()
* @param    string  $urlStr     要进行处理的URL地址
* @param    string  $strName    要删除的参数名
* @return   string
*/
function UnsetUrlParam($urlStr, $strName)
{
    if (empty($strName))
    {
        return($urlStr);
    }
    $ar = UrlToArray($urlStr);
    $ar2 = array();
    foreach ($ar as $key=>$val)
    {
        if ($strName == $val[0])
        {
            //找到指定的参数了，因为要删除他，所有就不复制，什么都不作
        }
        else
        {
            array_push($ar2, $val);
        }
    }
    return(ArrayToUrl($ar2));
} // end function UnsetUrlParam

/**
* 将URL地址转换为数组
*
* {@source 4 21}
* @access   public
* @see      ArrayToUrl()
* @param    string  $urlStr URL地址
* @return   array
*/
function UrlToArray($urlStr)
{
    /*
    示例：转换 'http://localhost/index.php?a=1&b=&c=d.php?e=5&f=6'的结果为
    Array(
        [0] => Array(
                [0] => http://localhost/working/hebca/source/test/index.php
                [1] =>)
        [1] => Array(
                [0] => a
                [1] => 1)
        [2] => Array(
                [0] => b
                [1] =>)
        [3] => Array(
                [0] => c
                [1] => d.php?e
                [2] => 5)
        [4] => Array(
                [0] => f
                [1] => 6) )
    */
    $ar = array();
    $str = $urlStr;
    $i = 0;
    //先寻找“?”
    $i = strpos($str, '?');
    if (1 > $i)
    {
        //URL中没有?，说明其没有参数
        array_push($ar, array($str, ''));
    }
    else
    {
        array_push($ar, array(substr($str, 0, $i), ''));
        $str = substr($str, $i + 1) . '&';
        //解析用&间隔的参数
        while (!empty($str))
        {
            $i = strpos($str, '&');
            if (0 < $i)
            {
                $sub_str = substr($str, 0, $i);
                //分析$sub_str这个等式
                array_push($ar, split('[=]', $sub_str));
                $str = substr($str, $i + 1);
            }
            else
            {
                //剩下的不可识别字符
                array_push($ar, array(substr($str, 0, 1), ''));
                $str = substr($str, 1);
            }
        }
    }
    return($ar);
} // end function UrlToArray

/**
* 将数组转换为URL地址
*
* 要进行转换的源数组必须是{@link UrlToArray()}结果的格式，即数组的第一个元素为文件地址，其余为各参数
* @access   public
* @see      UrlToArray()
* @param    array   $ar 数组
* @return   string
*/
function ArrayToUrl(&$ar)
{
    $i = count($ar);
    $s_url = '';
    if (0 < $i)
    {
        $s_url .= $ar[0][0] . '?';
        for ($j = 1; $j < $i; $j++)
        {
            foreach ($ar[$j] as $key=>$val)
            {
                $s_url .= $val . '=';
            }
            $s_url = substr($s_url, 0, strlen($s_url) - 1);
            $s_url .= '&';
        }
        $s_url = substr($s_url, 0, strlen($s_url) - 1);
    }
    //去掉URL尾端的无效字符
    $s_url = str_replace('&=', '', $s_url);
    $s_url = ereg_replace ('[&]+$', '', $s_url);
    return($s_url);
} // end function ArrayToUrl


/**
 * 为指定的文字内容按照指定的规则格式化成一个链接的HTML代码，返回该HTML字符串
 * @access  public
 * @param   string  $str            要进行格式化的内容
 * @param   string  $linkAddress    链接地址
 * @param   string  $targetWindow   链接的目标窗口
 * @param   string  $paramStr       其他参数字符串，按照原样加到链接代码中
 * @return  string
 */
function ToLink($str, $linkAddress, $targetWindow = '', $paramStr = '')
{
    $s_url = '';
    $s_url .= '<a href="' . $linkAddress . '" ';
    if (!empty($targetWindow))
    {
        $s_url .= 'target="' . $targetWindow . '" ';
    }
    if (!empty($paramStr))
    {
        $s_url .= $paramStr;
    }
    $s_url .= '>' . $str . '</a>';
    return($s_url);
}


/**
 * Find url plan from url
 *
 * eg: http://www.google.com/, plan = http
 * @param	string	$url
 * @return	string
 */
function UrlPlan($url) {
	$i = preg_match('/^(\w+):\/\//', $url, $ar);
	if (1 == $i)
		return $ar[1];
	else
		return '';
} // end of func UrlPlan

?>
