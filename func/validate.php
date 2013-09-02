<?php
/**
 * Functions about validate some data.
 * @package		fwolflib
 * @subpackage	func
 * @copyright	Copyright 2006-2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-func@gmail.com>
 * @since		2006-07-09
 */


require_once(dirname(__FILE__) . '/../fwolflib.php');
require_once(FWOLFLIB . 'func/env.php');


/**
 * Validate an email address.
 *
 * Provide email address (raw input)
 * Returns true if the email address has the email
 * address format and the domain exists.
 *
 * @deprecated      Use Fwlib\Util\Validator::email()
 * @link http://www.linuxjournal.com/article/9585
 * @param	string	$email
 * @return	boolean
 */
function ValidateEmail($email)
{
	$is_valid = true;
	$at_index = strrpos($email, '@');
	if (is_bool($at_index) && !$at_index)
	{
		$is_valid = false;
	}
	else
	{
		$domain = substr($email, $at_index + 1);
		$local = substr($email, 0, $at_index);
		$local_len = strlen($local);
		$domain_len = strlen($domain);
		if ($local_len < 1 || $local_len > 64)
		{
			// local part length exceeded
			$is_valid = false;
		}
		elseif ($domain_len < 1 || $domain_len > 255)
		{
			// domain part length exceeded
			$is_valid = false;
		}
		elseif ($local[0] == '.' || $local[$local_len-1] == '.')
		{
			// local part starts or ends with '.'
			$is_valid = false;
		}
		elseif (preg_match('/\\.\\./', $local))
		{
			// local part has two consecutive dots
			$is_valid = false;
		}
		elseif (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
		{
			// character not valid in domain part
			$is_valid = false;
		}
		elseif (preg_match('/\\.\\./', $domain))
		{
			// domain part has two consecutive dots
			$is_valid = false;
		}
		elseif (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
			str_replace("\\\\", "", $local)))
		{
			// character not valid in local part unless
			// local part is quoted
			if (!preg_match('/^"(\\\\"|[^"])+"$/',
				str_replace("\\\\", "", $local)))
			{
				$is_valid = false;
			}
		}

		// :NOTICE: Some network provider will return fake A record if
		// a dns query return fail, usually disp some ads.
		// So we only check MX record.
		if ($is_valid && NixOs())
			if (false == checkdnsrr($domain, "MX"))
				$is_valid = false;
	}
	return $is_valid;
}


/**
 * If a string is valid ip address.
 *
 * @deprecated      Use Fwlib\Util\Validator::ipv4()
 * @param	string	$ip
 * @return	boolean
 */
function ValidateIp($ip)
{
	 if (!strcmp(long2ip(sprintf("%u", ip2long($ip))), $ip))
		return true;
	 else
		return false;
} // end of function ValidateIp


/**
 * 老版本的检查ip函数, obsolete, leave here as reference.
 * @param	$str	string
 * @return	boolean
 */
function ValidateIpOld($str)
{
	 $ip = explode(".", $str);
	 if (count($ip)<4 || count($ip)>4) return false;
	 foreach($ip as $ip_addr) {
		  if ( !is_numeric($ip_addr) ) return false;
		  if ( $ip_addr<0 || $ip_addr>255 ) return false;
	 }
	 return true;
} // end of function ValidateIpOld

//如果简单的判断格式a.b.c.d而不考虑abcd的值的话：
//return (preg_match("/^([0-9]{1,3}\.){3}[0-9]{1,3}$/is", $str));
//不过如果需要真的ip的时候就不好玩了
?>
