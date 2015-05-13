<?php
namespace Fwlib\Config;

use Fwlib\Base\Exception\InvalidFormatException;

/**
 * StringOptions
 *
 * Special config with feature:
 *  - Get of un-defined option will return false
 *  - Initialize from string
 *
 * Example of option string:
 *  - "singleValue", will parse to bool value
 *  - "foo, bar=42", multiple value split by ',' or other characters
 *  - "foo=42, bar = 24", assignment value, will parse to {key: value}
 *  - "foo = h e l l o", assignment value can contain whitespace
 *  - " foo = a b ", assignment key and value will all be trimmed, {foo: 'a b'}
 *  - "foo = false", explicit boolean type value
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class StringOptions extends Config
{
    /**
     * Separator between key and value, eg: 'foo=bar' or 'foo: bar'
     */
    const KV_SEPARATOR = '=';

    /**
     * The param $separator is NOT same with {@see Config::$separator}. It is
     * used between key or key-value pairs in rule string.
     */
    const OPTION_SEPARATOR = ',';


    /**
     * @param   string  $optionString
     * @param   string  $optionSeparator
     * @param   string  $kvSeparator
     */
    public function __construct(
        $optionString = '',
        $optionSeparator = self::OPTION_SEPARATOR,
        $kvSeparator = self::KV_SEPARATOR
    ) {
        if (!empty($optionString)) {
            $this->import($optionString, $optionSeparator, $kvSeparator);
        }
    }


    /**
     * Export back to string
     *
     * Will add space between separators.
     *
     * @param   string  $optionSeparator
     * @param   string  $kvSeparator
     * @return  string
     */
    public function export(
        $optionSeparator = self::OPTION_SEPARATOR,
        $kvSeparator = self::KV_SEPARATOR
    ) {
        $sections = [];

        foreach ($this->getAll() as $key => $value) {
            if (true === $value) {
                $value = 'true';
            }
            if (false === $value) {
                $value = 'false';
            }

            $sections[] = "{$key}{$kvSeparator}{$value}";
        }

        return implode($optionSeparator, $sections);
    }


    /**
     * Initialize from rule string
     *
     * Will clear present values.
     *
     * @param   string  $optionString
     * @param   string  $optionSeparator
     * @param   string  $kvSeparator
     * @return  static
     * @throws  \Fwlib\Base\Exception\InvalidFormatException
     */
    public function import(
        $optionString = '',
        $optionSeparator = self::OPTION_SEPARATOR,
        $kvSeparator = self::KV_SEPARATOR
    ) {
        $this->configs = [];

        $sections = explode($optionSeparator, $optionString);
        foreach ($sections as $section) {
            $kvPair = explode($kvSeparator, $section);

            if (1 == count($kvPair)) {
                $value = trim(current($kvPair));

                if ('' === $value) {
                    // Multiple option separator position together
                    continue;
                }

                // No value part
                $this->set($value, true);

            } elseif (2 == count($kvPair)) {
                // Normal key-value pair
                $key = trim(array_shift($kvPair));
                $value = trim(array_shift($kvPair));

                $lowerValue = strtolower($value);
                if ('true' == substr($lowerValue, 0, 4)) {
                    $value = true;
                }
                if ('false' == substr($lowerValue, 0, 5)) {
                    $value = false;
                }

                $this->set($key, $value);

            } else {
                throw new InvalidFormatException(
                    'Format error: more than one key-value separator'
                );
            }
        }

        return $this;
    }
}
