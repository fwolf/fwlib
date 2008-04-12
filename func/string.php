<?php
/**
 * 字符串函数集
 * @package     fwolflib
 * @subpackage	func
 * @copyright   Copyright 2004-2008, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib-func@gmail.com>
 * @since		Before 2008-04-07
 * @version		$Id$
 */


/**
 * 把一个字符串转换为可以用HTML输出的格式
 * @param	string	$str
 * @return	string
*/
function HtmlEncode($str)
{
	if ( empty( $str) )
	{
	    return( '' );
	}
	$outstr=$str;
	$outstr=str_replace('&', '&amp;'  , $outstr);
	$outstr=str_replace('<', '&lt;'  , $outstr);
	$outstr=str_replace('>', '&gt;'  , $outstr);
	$outstr=str_replace(chr(13), '<br>'  , $outstr);
	$outstr=str_replace(chr(34), '&quot;'  , $outstr);
	$outstr=str_replace('  ', '&nbsp; '  , $outstr);
	$outstr=str_replace(' ' , '&nbsp;'  , $outstr);
	$outstr=str_replace(chr(9), '　　'  , $outstr);
	return $outstr;
} // end of function HtmlEncode


/**
 * 判断一个字符的某个位置的字符是否中文
 * 如果从一个中文的第二个字节开始检查，将返回FALSE
 * @param	string	$str
 * @param	int		$pos
 * @return	boolean
 */
function IsGbChar($str = '', $pos = 0)
{
    if (empty($str))
    {
        return(false);
    }
	else
	{
		//检查连续的两个字节
	    $s1 = ord(substr($str, $pos, 1));
	    $s2 = ord(substr($str, $pos + 1, 1));
		if ((160 < $s1) && (248 > $s1) && (160 < $s2) && (255 > $s2))
		{
		    return(true);
		}
		else
		{
		    return(false);
		}
	}
} // end of function IsGbChar


/**
 * 生成随机字符串
 * a表示包含小写字符，A表示包含大写字符，0表示包含数字
 * @param	int		$len	字符串长度
 * @param	string	$mode	模式
 * @return	string
 */
function RandomString($len, $mode)
{
	$str = '';
	if (preg_match('/[a]/', $mode))
	{
		$str .= 'abcdefghijklmnopqrstuvwxyz';
	}
	if (preg_match('/[A]/', $mode))
	{
		$str .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	}
	if (preg_match('/[0]/', $mode))
	{
	    $str .= '0123456789';
	}
	$result = '';
	$str_len = strlen($str);
	$max = 1000;
	for($i = 0;$i < $len;$i++)
	{
		$num = rand(0, $max);
		$num = $num % $str_len;
		$result .= $str[$num];
	}
	return $result;
} // end of function RandomString


/**
 * 返回字符串的长度，一个中文字按一个单位算
 * @param	string	$str
 * @return	int
 */
function StrlenGb($str = '')
{
    $len = strlen($str);
	$j = 0;
	for ($i=0; $i<$len; $i++)
	{
	    if (true == IsGbChar($str, $i))
	    {
	        $i++;
	    }
		$j++;
	}
	return($j);
} // end of function StrlenGb


/**
 * 把一个用字符串2间隔的字符串，用字符串1进行连接
 * @param	string	$str	源字符串
 * @param	string	$s1		用于连接的字符串
 * @param	string	$s2		源字符串本身是用什么连接的，如果为空，则使用$s1的值
 * @param	boolean	$embody	首尾是否加上字符串2
 * @param	boolean	$istrim	是否去除字符串中的特殊字符
 * @return	string
 */
function StrReForm( $str, $s1, $s2 = '', $embody = false, $istrim = true )
{
	$ss2 = empty($s2) ? $s1 : $s2;
	$ar = explode( $ss2, $str );
	if ( true == $istrim )
	{
		for ($i=0; $i<count($ar); $i++)
		{
			$ar[$i] = trim( $ar[$i], ' \t\0\x0B' );
		}
	}
	//去除空串
	$ar1 = array();
	for ($i=0; $i<count($ar); $i++)
	{
		if ( !empty( $ar[$i] ) )
		{
		    array_push( $ar1, $ar[$i] );
		}
	}
	$s = implode( $s1, $ar1 );
	if ( true == $embody )
	{
		$s = $s1 . $s . $s1;
	}
	return( $s );
} // end of function StrReForm


/**
 * 截取子字符串，中文按长度1计算
 * 在计算截取起始位置和截取长度时，中文也是按长度1计算的
 * 比如$str='大中小'，SubstrGb($str, 1, 1) = '中';
 * @param   string  $str
 * @param   int     $start
 * @param   int     $len
 */
function SubstrGb($str = '', $start = 0, $len = 0)
{
    $tmp = '';
	if (empty($str) || $len == 0)
	{
		return false;
	}
    $l = strlen($str);
    $j = 0;
	for ($i = 0; $i < $l; $i++)
	{
		$tmpstr = (ord($str[$i]) >= 161 && ord($str[$i]) <= 247&& ord($str[$i+1]) >= 161 && ord($str[$i+1]) <= 254)?$str[$i].$str[++$i]:$tmpstr = $str[$i];
		if ($j >= $start && $j <= ($start + $len))
		{
			$tmp .= $tmpstr;
		}
        $j++;
        if ($j == ($start + $len))
        {
            break;
        }
	}
	return $tmp;
} // end of function SubstrGb


/**
 * Convert ucfirst format to underline_connect format
 * 
 * If convert fail, return original string.
 * @param	string	$str
 * @return	string
 */
function StrUcfirst2Underline($str)
{
	$s = preg_replace('/([A-Z])/', '_\1', $str);
	$ar = explode('_', $s);
	$s = '';
	if (empty($ar))
		$s = $str;
	else 
	{
		foreach ($ar as $s1)
			if (!empty($s1))
				$s .= '_' . strtolower($s1);
		$s = substr($s, 1);
	}
	return $s;
} // end of func StrUcfirst2Underline


/**
 * Convert underline_connect format to ucfirst format
 * 
 * If convert fail, return ucfirst($str)
 * @param	string	$str
 * @return	string
 */
function StrUnderline2Ucfirst($str)
{
	$ar = explode('_', $str);
	$s = '';
	if (empty($ar))
		$s = ucfirst($str);
	else 
		foreach ($ar as $s1)
			if (!empty($s1))
				$s .= ucfirst($s1);
	return $s;
} // end of func StrUnderline2Ucfirst


?>
