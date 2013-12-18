<?php
namespace Fwlib\Util;

use Fwlib\Base\AbstractServiceContainer;

/**
 * Util class container
 *
 * @package     Fwlib\Base
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-18
 */
class UtilContainer extends AbstractServiceContainer
{
    /**
     * {@inheritdoc}
     *
     * @var array
     */
    protected $serviceClass = array(
        'Array'     => 'Fwlib\Util\ArrayUtil',
        'ArrayUtil' => 'Fwlib\Util\ArrayUtil',
    );
}
