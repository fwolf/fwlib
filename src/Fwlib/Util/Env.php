<?php
namespace Fwlib\Util;

/**
 * Runtime or server environment
 *
 * @copyright   Copyright 2006-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Env
{
    use FilterInputTrait;


    /**
     * Smart 'echo line', end with \n or <br /> according to run mod
     *
     * @param   string|array $str     Content to echo
     * @param   boolean      $noPrint Return instead of print
     * @return  string
     */
    public function ecl($str = '', $noPrint = false)
    {
        if ($this->isCli()) {
            $lineEnding = PHP_EOL;
        } else {
            $lineEnding = '<br />' . PHP_EOL;
        }

        if (is_array($str)) {
            $rs = '';
            foreach ($str as $v) {
                $rs .= $this->ecl($v, $noPrint);
            }
            return $rs;
        }

        // Replace line ending in str
        $str = preg_replace('/[\r\n]/', $lineEnding, rtrim($str));

        // Add new line
        $str .= $lineEnding;

        if (!$noPrint) {
            echo $str;
        }

        return $str;
    }


    /**
     * Force page visit through https only
     *
     * Better solution is use rewrite feature of web server.
     */
    public function forceHttps()
    {
        $plan = $this->getServer('HTTPS');

        if (empty($plan) || 'on' != $plan) {
            $url = 'https://' . $this->getServer('HTTP_HOST') .
                $this->getServer('REQUEST_URI');

            header("Location: $url");
        }
    }


    /**
     * @param   string    $name
     * @param   mixed     $default Default value if name is not found in input
     * @param   int       $filter
     * @param   int|array $options
     * @return  string|int
     */
    public function getEnv(
        $name,
        $default = null,
        $filter = FILTER_DEFAULT,
        $options = null
    ) {
        return $this->filterInput(
            INPUT_ENV,
            $name,
            $default,
            $filter,
            $options
        );
    }


    /**
     * @param   array|int $definition
     * @param   bool      $addEmpty
     * @return  array
     */
    public function getEnvs($definition = FILTER_DEFAULT, $addEmpty = true)
    {
        return $this->filterInputArray(INPUT_ENV, $definition, $addEmpty);
    }


    /**
     * @param   string    $name
     * @param   mixed     $default Default value if name is not found in input
     * @param   int       $filter
     * @param   int|array $options
     * @return  string|int
     */
    public function getServer(
        $name,
        $default = null,
        $filter = FILTER_DEFAULT,
        $options = null
    ) {
        return $this->filterInput(
            INPUT_SERVER,
            $name,
            $default,
            $filter,
            $options
        );
    }


    /**
     * @param   array|int $definition
     * @param   bool      $addEmpty
     * @return  array
     */
    public function getServers($definition = FILTER_DEFAULT, $addEmpty = true)
    {
        return $this->filterInputArray(INPUT_SERVER, $definition, $addEmpty);
    }


    /**
     * Check if is running under cli mod
     *
     * @return  boolean
     */
    public function isCli()
    {
        return 'cli' == PHP_SAPI;
    }


    /**
     * Check if is running in *nix host
     *
     * @return boolean
     */
    public function isNixOs()
    {
        return 'Windows' != PHP_OS;
    }
}
