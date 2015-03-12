<?php
namespace Fwlib\Util;

/**
 * Trait for using {@see filter_input()} and {@see filter_input_array()}
 *
 * @SuppressWarnings(PHPMD.Superglobals)
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait FilterInputTrait
{
    /**
     * Get input via filter_input() function
     *
     * Additional to original function:
     *  - default value
     *
     * @param   int       $type
     * @param   string    $name
     * @param   mixed     $default Default value if name is not found in input
     * @param   int       $filter
     * @param   int|array $options
     * @return  string|int
     */
    public function filterInput(
        $type,
        $name,
        $default = null,
        $filter = FILTER_DEFAULT,
        $options = null
    ) {
        $result = filter_input($type, $name, $filter, $options);

        if (is_null($result)) {
            $result = $default;
        }

        return $result;
    }


    /**
     * Get input array via filter_input_array() function
     *
     * Additional to original function:
     *  - added filter replication
     *  - return empty array if nothing found
     *
     * @param   int       $type
     * @param   array|int $definition
     * @param   bool      $addEmpty
     * @return  array
     */
    public function filterInputArray($type, $definition, $addEmpty = true)
    {
        $map = [
            INPUT_GET    => $_GET,
            INPUT_POST   => $_POST,
            INPUT_COOKIE => $_COOKIE,
            INPUT_SERVER => $_SERVER,
            INPUT_ENV    => $_ENV,
        ];
        if (!is_array($definition)) {
            $keys = array_keys($map[$type]);

            if (!empty($keys)) {
                $definition = array_fill_keys($keys, $definition);
            }
        }

        $result = filter_input_array($type, $definition, $addEmpty);

        $result = is_null($result) ? [] : $result;

        return $result;
    }
}
