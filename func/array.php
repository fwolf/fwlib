<?php
/**
 * Funcs about array
 *
 * @package		fwolflib
 * @subpackage	func
 * @copyright   Copyright 2010, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib.func@gmail.com>
 * @since		2010-01-25
 */


/**
 * Read value from array.
 *
 * @param	array	$ar
 * @param	mixed	$key
 * @param	mixed	$val_default
 * @return	mixed
 */
function ArrayRead($ar, $key, $val_default = null) {
	if (isset($ar[$key]))
		$val_return = $ar[$key];
	elseif (!is_null($val_default))
		$val_return = $val_default;
	else
		$val_return = null;

    return $val_return;
} // end of func ArrayRead


?>
