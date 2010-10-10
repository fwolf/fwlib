<?php
/**
 * Function about ini file read, write and modify.
 *
 * @package		fwolflib
 * @subpackage	func
 * @copyright	Copyright 2008-2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-func@gmail.com>
 * @since		2008-04-23
 * @link		http://www.php.net/manual/en/function.parse-ini-file.php
 */


require_once(dirname(__FILE__) . '/../fwolflib.php');


/**
 * Read ini file
 *
 * To retrieve global value, set $section to ' ' instead of ''.
 * @param	string	$filepath
 * @param	string	$section	If obmit, return array while each index is a section name.
 * @param	string	$item		If obmit, return array include all items.
 * @return	mixed
 */
function IniGet($filepath, $section = '', $item = '')
{
	$ini = file($filepath);
	if (empty($ini))
		return '';

	/*
		Result array should like this:
		array(
			global,
			section => array(
				item => value
				)
			)
	*/
	$rs = array();

    $i = 0;
    $s_section = '';		// Current scaning section name
    foreach ($ini as $line)
    {
    	$line = trim($line);
        // Empyt line
        if (empty($line)) continue;
        // Comments
        if ((';' == $line{0}) || ('#' == $line{0}))
        	continue;

        // Section
        if ('[' == $line{0})
        {
        	$s_section = substr($line, 1, -1);
            $i++;
            continue;
        }

        // Key-value pair
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (0 == $i)
        {
            // Global value
            if ('[]' == substr($line, -1, 2))
            	// Array value in ini file ?
                $rs[' '][$key][] = $value;
            else
                $rs[' '][$key] = $value;
        }
        else
        {
            // Section value
            if ('[]' == substr($line, -1, 2))
            	$rs[$s_section][$key][] = $value;
            else
                $rs[$s_section][$key] = $value;
        }
    }

    // Return values by param
    if (empty($section))
    	return $rs;
    elseif (empty($item))
    {
    	if (isset($rs[$section]))
    		return $rs[$section];
    	else
    		return '';
    }
    else
    {
    	if (isset($rs[$section][$item]))
    		return $rs[$section][$item];
    	else
    		return '';
    }

} // end of func IniGet

?>
