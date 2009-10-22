<?php
/**
 * Func about encrypt and decrypt.
 * @package     fwolflib
 * @subpackage	func
 * @copyright   Copyright 2009, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib.func@gmail.com>
 * @since		2009-10-22
 */


require_once('fwolflib/func/ecl.php');
require_once('fwolflib/func/env.php');


// Get mcrypt supported algorithms:
/*
$algorithms = mcrypt_list_algorithms("/usr/local/lib/libmcrypt");

foreach ($algorithms as $cipher) {
	echo "$cipher\n";
}
*/
/*
cast-128
gost
rijndael-128
twofish
arcfour
cast-256
loki97
rijndael-192
saferplus
wake
blowfish-compat
des
rijndael-256
serpent
xtea
blowfish
enigma
rc2
tripledes
*/


/*
 * McryptSmplIv
 *
 * Use part of secret key as IV, so need assign it from outside,
 * or save it to use when decrypt.
 */


/**
 * Do decrypt
 *
 * @param	string	$s_data					Source data.
 * @param	string	$s_key					Secret key.
 * @param	string	$algorithm				Same as mcrypt_module_open().
 * @param	string	$algorithm_directory	Same as mcrypt_module_open().
 * @param	string	$mode					Same as mcrypt_module_open().
 * @param	string	$mode_directory			Same as mcrypt_module_open().
 * @return	string
*/
function McryptSmplIvDecrypt($s_data, $s_key, $algorithm, $algorithm_directory = '', $mode = 'cfb', $mode_directory = '') {
	return McryptSmplIvProcess(1, $s_data, $s_key, $algorithm, $algorithm_directory, $mode, $mode_directory);
} // end of func McryptSmplIvDecrypt


/**
 * Do encrypt
 *
 * @param	string	$s_data					Source data.
 * @param	string	$s_key					Secret key.
 * @param	string	$algorithm				Same as mcrypt_module_open().
 * @param	string	$algorithm_directory	Same as mcrypt_module_open().
 * @param	string	$mode					Same as mcrypt_module_open().
 * @param	string	$mode_directory			Same as mcrypt_module_open().
 * @return	string
*/
function McryptSmplIvEncrypt($s_data, $s_key, $algorithm, $algorithm_directory = '', $mode = 'cfb', $mode_directory = '') {
	return McryptSmplIvProcess(0, $s_data, $s_key, $algorithm, $algorithm_directory, $mode, $mode_directory);
} // end of func McryptSmplIvEncrypt


/**
 * Real process func McryptSmplIv
 *
 * @param	int		$i_action				0=encrypt, else=decrypt.
 * @param	string	$s_data					Source data.
 * @param	string	$s_key					Secret key.
 * @param	string	$algorithm				Same as mcrypt_module_open().
 * @param	string	$algorithm_directory	Same as mcrypt_module_open().
 * @param	string	$mode					Same as mcrypt_module_open().
 * @param	string	$mode_directory			Same as mcrypt_module_open().
 * @return	string
*/
function McryptSmplIvProcess($i_action, $s_data, $s_key, $algorithm, $algorithm_directory = '', $mode = 'cfb', $mode_directory = '') {
	/* Open the cipher */
	$td = mcrypt_module_open($algorithm,
		$algorithm_directory,
		$mode,
		$mode_directory);

	$ks = mcrypt_enc_get_key_size($td);

	/* Create key */
	$key = substr(sha1($s_key), 0, $ks);

	// The IV must be unique and must be the same when decrypting/encrypting.

	// But encrypt/decrypt are executed on 2 machine,
	// randon IV will cause decrypt wrong result
	// Bad offical example, all put encrypt/decrypt together !

	/* Create the IV and determine the keysize length, use MCRYPT_RAND
	 * on Windows instead */
/*
	if (true == NixOs())
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_RANDOM);
	else
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
*/

	// So now, I use part/duplicate sha1 value of key as IV
	$iv = sha1($key);	// Sha1 again :-)
	$l_sha1 = strlen($iv);
	$l_iv = mcrypt_enc_get_iv_size($td);
	if ($l_sha1 < $l_iv) {
		// Duplicate sha1 value to generate IV
		$iv = str_repeat($iv, round($l_iv / $l_sha1) + 1);
	}
	$iv = substr($iv, 0, $l_iv);

	/* Intialize encryption */
	mcrypt_generic_init($td, $key, $iv);

	if (0 == $i_action) {
		/* Encrypt data */
		$encrypted = mcrypt_generic($td, $s_data);
	}
	else {
		/* Decrypt encrypted string */
		$encrypted = mdecrypt_generic($td, $s_data);
	}

	/* Terminate decryption handle and close module */
	mcrypt_generic_deinit($td);
	mcrypt_module_close($td);

	return($encrypted);
} // end of func McryptSmplIvProcess

?>
