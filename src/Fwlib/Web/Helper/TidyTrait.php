<?php
namespace Fwlib\Web\Helper;

use Fwlib\Base\Exception\ExtensionNotLoadedException;
use tidy;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait TidyTrait
{
    /**
     * Format html with tidy extension
     *
     * @param   string  $html
     * @return  string
     */
    protected function tidy($html)
    {
        if (!class_exists('tidy')) {
            throw (new ExtensionNotLoadedException)->setExtension('tidy');

        } else {
            $config = [
                'doctype'       => 'strict',
                'indent'        => true,
                'indent-spaces' => 2,
                'output-xhtml'  => true,
                'wrap'          => 200
            ];

            $tidy = new tidy;
            $tidy->parseString($html, $config, 'utf8');
            $tidy->cleanRepair();

            return tidy_get_output($tidy);
        }
    }
}
