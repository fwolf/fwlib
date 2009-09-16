<?php
/**
 * 字符串函数集
 * @package     fwolflib
 * @subpackage	func
 * @copyright   Copyright 2004-2009, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib.func@gmail.com>
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
	if (empty($str))
	    return('');

	$ar = array(
		'&'		=> '&amp;',
		'<'		=> '&lt;',
		'>'		=> '&gt;',
		chr(9)	=> '　　',
		chr(13)	=> '<br />',
		chr(34)	=> '&quot;',
		'  '	=> '&nbsp; ',
		' '		=> '&nbsp;',
		'&nbsp;&nbsp;'	=> '&nbsp; ',
	);
	$ar_search = array_keys($ar);
	$ar_replace = array_values($ar);

	$outstr = str_replace($ar_search, $ar_replace, $str);
	return $outstr;
} // end of func HtmlEncode


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
} // end of func IsGbChar


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
} // end of func RandomString


/**
 * Decode a string which is MIME encoding
 *
 * @link	http://www.faqs.org/rfcs/rfc2047
 * @link	http://www.php.net/imap_utf8
 * @param	string	$str
 * @param	string	$encoding	Encoding of output string.
 * @return	string
 */
function Rfc2047Decode($str, $encoding = 'utf-8')
{
	// Find string encoding
	$ar = array();
	//preg_match_all('/=\?(.{3,13})\?[B|Q]\?([\/\d\w\=]*)\?\=/i', $str, $ar);
	preg_match_all('/=\?(.{3,13})\?([B|Q])\?([^\?]*)\?\=/i', $str, $ar);
	// 0 is all-string pattern, 1 is encoding, 2 is string to base64_decode
	$i = count($ar[0]);
	//var_dump($ar);
	if (0 < $i)
	{
		// Got match, process
		for ($j = 0; $j < count($i); $j++)
		{
			$s = '';
			if ('B' == strtoupper($ar[2][$j])) {
				// Decode base64 first
				$s = base64_decode($ar[3][$j]);
			}
			elseif ('Q' == strtoupper($ar[2][$j])) {
				// quoted-printable encoding ? its format like '=0D=0A'
				$s = quoted_printable_decode($ar[3][$j]);
			}

			// Then convert string to charset ordered
			if ($encoding != strtolower($ar[1][$j]))
				$s = mb_convert_encoding($s, $encoding, $ar[1][$j]);

			// Then replace into original string
			if (!empty($s))
				$str = str_replace($ar[0][$j], $s, $str);
		}
		//echo "$str \n";
		return $str;
	}
	else
	{
		// No match, return original string
		return $str;
	}
}


/**
 * Encode a string using MIME encoding method
 *
 * Usually used in mail header, attachment name etc.
 *
 * No break in string(B encoding mode instead of Q, see
 * phpmailer::EncodeHeader, line 1156), because that possible
 * break chinese chars.
 * @link	http://www.faqs.org/rfcs/rfc2047
 * @param	string	$str
 * @param	string	$encoding	Encoding of $str
 * @return	string
 */
function Rfc2047Encode($str, $encoding = 'utf-8')
{
	return "=?" . $encoding . "?B?" . base64_encode($str) . "?=";
} // end of func Rfc2047Encode


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
} // end of func StrlenGb


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
} // end of func StrReForm


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
 * @param	boolean	$minus	Treat minus sign as splitter also.
 * @return	string
 */
function StrUnderline2Ucfirst($str, $minus = false)
{
	if ($minus)
		$str = str_replace('-', '_', $str);
	$ar = explode('_', $str);
	$s = '';
	if (empty($ar))
		$s = ucfirst($str);
	else
		foreach ($ar as $s1) {
			if ('' != $s1)
				$s .= ucfirst($s1);
		}
	return $s;
} // end of func StrUnderline2Ucfirst


/**
 * 截取子字符串，中文按长度1计算
 * 在计算截取起始位置和截取长度时，中文也是按长度1计算的
 * 比如$str='大中小'，SubstrGb($str, 1, 1) = '中';
 *
 * Obsolete, see: http://www.fwolf.com/blog/post/133
 * @link http://www.fwolf.com/blog/post/133
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
} // end of func SubstrGb


?>
