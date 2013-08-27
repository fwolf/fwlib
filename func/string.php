<?php
/**
 * Functions about string
 *
 * @package     fwolflib
 * @subpackage	func
 * @copyright   Copyright 2004-2012, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib.func@gmail.com>
 * @since		Before 2008-04-07
 */


require_once(dirname(__FILE__) . '/../fwolflib.php');


/**
 * Addslashes for any data, recursive.
 *
 * @deprecated      Use Fwlib\Util\StringUtil::addSlashesRecursive()
 * @param	mixed	$srce
 * @return	mixed
 */
function AddslashesRecursive ($srce) {
	if (empty($srce))
		return $srce;

	elseif (is_string($srce))
		return addslashes($srce);

	elseif (is_array($srce)) {
		$ar_rs = array();
		foreach ($srce as $k => $v) {
			$ar_rs[addslashes($k)] = AddslashesRecursive($v);
		}
		return $ar_rs;
	}

	else
		// Other data type, return original
		return $srce;
} // end of func AddslashesRecursive


/**
 * 把一个字符串转换为可以用HTML输出的格式
 *
 * @deprecated      Use Fwlib\Util\StringUtil::encodeHtml()
 * @param	string	$str
 * @return	string
*/
function HtmlEncode ($str) {
	if (empty($str))
	    return('');

	$ar = array(
		'&'		=> '&amp;',
		'<'		=> '&lt;',
		'>'		=> '&gt;',
		chr(9)	=> '　　',
		chr(34)	=> '&quot;',
		'  '	=> '&nbsp; ',
		' '		=> '&nbsp;',
		'&nbsp;&nbsp;'	=> '&nbsp; ',
		chr(13)	=> '<br />',
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
function IsGbChar($str = '', $pos = 0) {
    if (empty($str)) {
        return(false);
    }
	else {
		//检查连续的两个字节
	    $s1 = ord(substr($str, $pos, 1));
	    $s2 = ord(substr($str, $pos + 1, 1));
		if ((160 < $s1) && (248 > $s1) && (160 < $s2) && (255 > $s2)) {
		    return(true);
		}
		else {
		    return(false);
		}
	}
} // end of func IsGbChar


/*
 * Json encode with JSON_HEX_(TAG|AMP|APOS|QUOT) options
 *
 * @deprecated      Use Fwlib\Util\Json::encodeHex()
 * @param	mixed	$val
 * @return	string
 */
function JsonEncodeHex ($val) {
	// Check json extension
	if (!extension_loaded('json')) {
		error_log('JsonEncodeHex(): json extension is not loaded.');
		return NULL;
	}

	$s_json = '';
	if (0 <= version_compare(PHP_VERSION, '5.3.0'))
		$s_json =  json_encode($val, JSON_HEX_TAG|JSON_HEX_APOS
			|JSON_HEX_QUOT|JSON_HEX_AMP);
	else {
		// Json treat list/array in different way([] vs {}).
		if (is_array($val) || is_object($val)) {
			$is_list = is_array($val) && (empty($val)
				|| array_keys($val) === range(0, count($val) - 1));

			if ($is_list ) {
				$s_json = '[' . implode(',',
					array_map('JsonEncodeHex', $val)) . ']';
			} else {
				$ar_t = array();
				foreach ($val as $k => $v) {
					$ar_t[] = JsonEncodeHex($k) . ':'
						. JsonEncodeHex($v);
				}
				$s_json = '{' . implode(',', $ar_t) . '}';
			}
		}
		elseif (is_string($val)) {
			// Manual replace chars
			$s_json = json_encode($val);
			$s_json = substr($s_json, 1);
			$s_json = substr($s_json, 0, strlen($s_json) - 1);
			$s_json = str_replace(array(
					'<', '>', "'", '\"', '&'
				), array(
					'\u003C', '\u003E', '\u0027', '\u0022', '\u0026'
				), $s_json);
			$s_json = '"' . $s_json . '"';
		}
		else {
			// Int, floats, bools, null
			$s_json = '"' . json_encode($val) . '"';
		}
	}
	return $s_json;
} // end of func JsonEncodeHex


/**
 * Json encode, simulate JSON_UNESCAPED_UNICODE option is on.
 *
 * @deprecated      Use Fwlib\Util\Json::encodeUnicode()
 * @param	mixed	$val
 * @param	int		$option			Other original json_encode option
 * @return	string
 */
function JsonEncodeUnicode ($val, $option = 0) {
	if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
		return json_encode($val, JSON_UNESCAPED_UNICODE | $option);
	}
	else {
		$val = json_encode($val, $option);
		$val = preg_replace('/\\\u([0-9a-f]{4})/ie'
			, "mb_convert_encoding(pack('H4', '\\1'), 'UTF-8', 'UCS-2BE')"
			, $val);
		return $val;

		/*
		 * Another way is use urlencode before json_encode,
		 * and use urldecode after it.
		 * But this way can't deal with array recursive,
		 * and array have chinese char in array key.
		 *
		 * 3rd way:
		 * mb_convert_encoding('&#37257;&#29233;', 'UTF-8', 'HTML-ENTITIES');
		 * Need convert \uxxxx to &#xxxxx first.
		 */
	}
} // end of func JsonEncodeUnicode


/**
 * Match a string with rule including wildcard.
 *
 * Eg: 'abcd' match rule '*c?'
 *
 * @deprecated      Use Fwlib\Util\StringUtil::matchWildcard()
 * @param	string	$str
 * @param	string	$rule
 * @return	boolean
 */
function MatchWildcard ($str, $rule) {
	// Convert wildcard rule to regex
	$rule = str_replace('*', '.+', $rule);
	$rule = str_replace('?', '.{1}', $rule);
	$rule = '/' . $rule . '/';

	if (1 == preg_match($rule, $str, $ar_match))
		// Must match whole string, same length
		if (strlen($ar_match[0]) == strlen($str))
			return true;

	return false;
} // end of func MatchWildcard


/**
 * Generate org code according to GB 11714-1997.
 *
 * @deprecated      Use Fwlib\Mis\OrgCode::gen()
 * @link	http://zh.wikisource.org/zh/GB_11714-1997_全国组织机构代码编制规则
 * @param	string	$code_base	8-digit base code
 * @return	string
 */
function OrgCodeGen ($code_base = '') {
	$code_base = strtoupper($code_base);
	// Gen random
	if (empty($code_base))
		$code_base = RandomString(8, '0A');
	// Length check
	else if (8 != strlen($code_base))
		return '';
	// Only 0-9 A-Z allowed
	else if ('' != preg_replace('/[0-9A-Z]/', '', $code_base))
		return '';


	// Prepare value table
	$ar_val = array();
	// 0-9 to 0-9
	for ($i = 48; $i < 58; $i ++)
		$ar_val[chr($i)] = $i - 48;
	// A-Z to 10-35
	for ($i = 65; $i < 91; $i ++)
		$ar_val[chr($i)] = $i - 55;

	// Weight table
	$ar_weight = array(3, 7, 9, 10, 5, 8, 4, 2);

	// Add each digit value after plus it's weight
	$j = 0;
	for ($i = 0; $i <8; $i ++)
		$j += $ar_val[$code_base{$i}] * $ar_weight[$i];

	// Mod by 11
	$j = $j % 11;

	// Minus by 11
	$j = 11 - $j;

	// Return result
	if (10 == $j)
		return $code_base . '-X';
	else if (11 == $j)
		return $code_base . '-0';
	else
		return $code_base . '-' . strval($j);
} // end of func OrgCodeGen


/**
 * Convert 15-digi pin to 18-digi
 *
 * @deprecated      Use Fwlib\Mis\CinCode::to18()
 * @param	string	$pin
 * @return	string
 */
function Pin15To18($pin) {
	if (15 != strlen($pin))
		// Error, which value should I return ?
		return $pin;

	$s = substr($pin, 0, 6) . '19' . substr($pin, 6);

	$n = 0;
	for ($i = 17; 0 < $i; $i --) {
		$n += (pow(2, $i) % 11) * intval($s{17 - $i});
	}
	$n = $n % 11;
	switch ($n) {
		case	0:
			$s_last = '1';
			break;
		case	1:
			$s_last = '0';
			break;
		case	2:
			$s_last = 'X';
			break;
		default:
			$s_last = strval(12 - $n);
			break;
	}

	return $s . $s_last;
} // end of func Pin15To18


/**
 * 生成随机字符串
 * a表示包含小写字符，A表示包含大写字符，0表示包含数字
 *
 * @deprecated      Use Fwlib\Util\StringUtil::random()
 * @param	int		$len	字符串长度
 * @param	string	$mode	模式
 * @return	string
 */
function RandomString ($len, $mode = 'a0') {
	$str = '';
	if (preg_match('/[a]/', $mode)) {
		$str .= 'abcdefghijklmnopqrstuvwxyz';
	}
	if (preg_match('/[A]/', $mode)) {
		$str .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	}
	if (preg_match('/[0]/', $mode)) {
	    $str .= '0123456789';
	}
	$result = '';
	$str_len = strlen($str);
	$max = 1000;
	for($i = 0; $i < $len; $i ++) {
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
 *
 * @deprecated      Use Fwlib\Util\Rfc2047::decode()
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
 *
 * @deprecated      Use Fwlib\Util\Rfc2047::encode()
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
 *
 * Check also: mb_strwidth()
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
 *
 * :THINK: Useless ? Just replace connector with new char.
 *
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
 * Convert string to array by splitter
 *
 * @deprecated      Use Fwlib\Util\StringUtil::toArray()
 * @param	string	$s_srce
 * @param	string	$s_splitter
 * @param	boolean	$b_trim
 * @param	boolean	$b_remove_empty
 * @return	array
 */
function StrToArray ($s_srce, $s_splitter = ',', $b_trim = true
	, $b_remove_empty = true) {
	if (!is_string($s_srce))
		$s_srce = strval($s_srce);

	if (false === strpos($s_srce, $s_splitter)) {
		$ar_rs = array($s_srce);
	}
	else {
		$ar_rs = explode($s_splitter, $s_srce);
	}

	if ($b_trim) {
		foreach ($ar_rs as &$v)
			$v = trim($v);
		unset($v);
	}

	if ($b_remove_empty) {
		foreach ($ar_rs as $k => $v)
			if (empty($v))
				unset($ar_rs[$k]);
		// Re generate array index
		$ar_rs = array_merge($ar_rs, array());
	}

	return $ar_rs;
} // end of func StrToArray


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


/**
 * Get substr by display width, and ignore html tag's length
 *
 * Using mb_strimwidth()
 *
 * Attention: No consider of html complement.
 * @link http://www.fwolf.com/blog/post/133
 * @param	string	$str	Source string
 * @param	int		$len	Length
 * @param	string	$marker	If str length exceed, cut & fill with this
 * @param	int		$start	Start position
 * @param	string	$encoding	Default is utf-8
 * @return	string
 */
function SubstrIgnHtml($str, $len, $marker = '...', $start = 0, $encoding = 'utf-8') {
	$i = preg_match_all('/<[^>]*>/i', $str, $ar);
	if (0 == $i) {
		// No html in $str
		$str = htmlspecialchars_decode($str);
		$str = mb_strimwidth($str, $start, $len, $marker, $encoding);
		$str = htmlspecialchars($str);
		return $str;
	} else {
		// Have html tags, need split str into parts by html
		$ar = $ar[0];
		$ar_s = array();
		for ($i = 0; $i < count($ar); $i ++) {
			// Find sub str
			$j = strpos($str, $ar[$i]);
			// Add to new ar: before, tag
			if (0 != $j)
				$ar_s[] = substr($str, 0, $j);
			$ar_s[] = $ar[$i];
			// Trim origin str, so we start from 0 again next loop
			$str = substr($str, $j + strlen($ar[$i]));
		}
		// Tail of $str, which after html tags
		$ar_s[] = $str;

		// Loop to cut needed length
		$s_result = '';
		$i_length = $len - mb_strwidth($marker, $encoding);
		$f_tag = 0;		// In html tag ?
		$i = 0;
		while ($i < count($ar_s)) {
			$s = $ar_s[$i];
			$i ++;

			// Is it self-end html tag ?
			if (0 < preg_match('/\/\s*>/', $s)) {
				$s_result .= $s;
			} elseif (0 < preg_match('/<\s*\//', $s)) {
				// End of html tag ?
				// When len exceed, only end tag allowed
				if (0 < $f_tag) {
					$s_result .= $s;
					$f_tag --;
				}
			} elseif (0 < strpos($s, '>')) {
				// Begin of html tag ?
				// When len exceed, no start tag allowed
				if (0 < $i_length) {
					$s_result .= $s;
					$f_tag ++;
				}
			} else {
				// Real string
				$s = htmlspecialchars_decode($s);
				if (0 == $i_length) {
					// Already got length
					continue;
				} elseif (mb_strwidth($s, $encoding) < $i_length) {
					// Can add to rs completely
					$i_length -= mb_strwidth($s, $encoding);
					$s_result .= htmlspecialchars($s);
				} else {
					// Need cut then add to rs
					$s_result .= htmlspecialchars(mb_strimwidth($s, 0, $i_length, '', $encoding)) . $marker;
					$i_length = 0;
				}
			}
		}

		return $s_result;
	}
	return '';
} // end of func SubstrIgnHtml


?>
