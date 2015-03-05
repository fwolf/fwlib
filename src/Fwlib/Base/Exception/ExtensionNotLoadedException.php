<?php
namespace Fwlib\Base\Exception;

/**
 * Exception for missing required PHP extension
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ExtensionNotLoadedException extends \Exception
{
    /**
     * Set which extension is missing
     *
     * @param   string  $ext
     * @return  static
     */
    public function setExtension($ext)
    {
        $this->message = "PHP extension $ext is not loaded";

        return $this;
    }
}
