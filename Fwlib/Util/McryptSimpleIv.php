<?php
namespace Fwlib\Util;


/**
 * Simple mcrypt
 *
 * IV(initialization vector) is computed from secret key, so the decrypt
 * operate need same secret key.
 *
 * Get mcrypt supported algorithms:
 * $algorithms = mcrypt_list_algorithms("/usr/local/lib/libmcrypt");
 * foreach ($algorithms as $cipher) {
 *     echo "$cipher\n";
 * }
 *
 * Result:
 * cast-128
 * gost
 * rijndael-128
 * twofish
 * arcfour
 * cast-256
 * loki97
 * rijndael-192
 * saferplus
 * wake
 * blowfish-compat
 * des
 * rijndael-256
 * serpent
 * xtea
 * blowfish
 * enigma
 * rc2
 * tripledes
 *
 * @codeCoverageIgnore
 *
 * @copyright   Copyright 2009-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 */
class McryptSimpleIv
{
    /**
     * Check if mcrypt extension is loaded
     *
     * @param   boolean $exit
     * @return  boolean
     */
    public function checkExtension($exit = false)
    {
        if (!extension_loaded('mcrypt')) {
            // @codeCoverageIgnoreStart

            if ($exit) {
                $trace = debug_backtrace();
                trigger_error(
                    'Extension mcrypt is not loaded,' .
                    ' in ' . $trace[0]['file'] .
                    ' on line ' . $trace[0]['line'],
                    E_USER_ERROR
                );
                exit();
            } else {
                return false;
            }

            // @codeCoverageIgnoreEnd
        }

        return true;
    }


    /**
     * Do decrypt
     *
     * @param   string  $srce                   Source data
     * @param   string  $key                    Secret key
     * @param   string  $algorithm              Same as mcrypt_module_open()
     * @param   string  $algorithmDirectory     Same as mcrypt_module_open()
     * @param   string  $mode                   Same as mcrypt_module_open()
     * @param   string  $modeDirectory          Same as mcrypt_module_open()
     * @return  string
    */
    public function decrypt(
        $srce,
        $key,
        $algorithm,
        $algorithmDirectory = '',
        $mode = 'cfb',
        $modeDirectory = ''
    ) {
        return self::process(
            1,
            $srce,
            $key,
            $algorithm,
            $algorithmDirectory,
            $mode,
            $modeDirectory
        );
    }


    /**
     * Do encrypt
     *
     * @param   string  $srce                   Source data
     * @param   string  $key                    Secret key
     * @param   string  $algorithm              Same as mcrypt_module_open()
     * @param   string  $algorithmDirectory     Same as mcrypt_module_open()
     * @param   string  $mode                   Same as mcrypt_module_open()
     * @param   string  $modeDirectory          Same as mcrypt_module_open()
     * @return  string
    */
    public function encrypt(
        $srce,
        $key,
        $algorithm,
        $algorithmDirectory = '',
        $mode = 'cfb',
        $modeDirectory = ''
    ) {
        return self::process(
            0,
            $srce,
            $key,
            $algorithm,
            $algorithmDirectory,
            $mode,
            $modeDirectory
        );
    }


    /**
     * Do real process
     *
     * @param   int     $action                 0=encrypt, 1|else=decrypt
     * @param   string  $srce                   Source data
     * @param   string  $key                    Secret key
     * @param   string  $algorithm              Same as mcrypt_module_open()
     * @param   string  $algorithmDirectory     Same as mcrypt_module_open()
     * @param   string  $mode                   Same as mcrypt_module_open()
     * @param   string  $modeDirectory          Same as mcrypt_module_open()
     * @return  string
    */
    public function process(
        $action,
        $srce,
        $key,
        $algorithm,
        $algorithmDirectory = '',
        $mode = 'cfb',
        $modeDirectory = ''
    ) {
        self::checkExtension(true);


        // Open the cipher
        $td = mcrypt_module_open(
            $algorithm,
            $algorithmDirectory,
            $mode,
            $modeDirectory
        );

        $ks = mcrypt_enc_get_key_size($td);

        // Create key
        $key = substr(sha1($key), 0, $ks);


        /**
         *
         * The IV must be unique and must be the same when decrypting/encrypting.
         *
         * But encrypt/decrypt are executed on 2 machine,
         * randon IV will cause decrypt wrong result
         *
         * So now, I use part/duplicate sha1 value of key as IV
         *
         * Bad offical example, all put encrypt/decrypt together !
         */


        $iv = sha1($key);   // Sha1 again :-)
        $lenSha1 = strlen($iv);
        $lenIv = mcrypt_enc_get_iv_size($td);
        // @codeCoverageIgnoreStart
        if ($lenSha1 < $lenIv) {
            // Duplicate sha1 value to generate IV
            $iv = str_repeat($iv, round($lenIv / $lenSha1) + 1);
        }
        // @codeCoverageIgnoreEnd
        $iv = substr($iv, 0, $lenIv);

        // Intialize encryption
        mcrypt_generic_init($td, $key, $iv);

        if (0 == $action) {
            // Encrypt data
            $encrypted = mcrypt_generic($td, $srce);
        } else {
            // Decrypt encrypted string
            $encrypted = mdecrypt_generic($td, $srce);
        }

        // Terminate decryption handle and close module
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return($encrypted);
    }
}
