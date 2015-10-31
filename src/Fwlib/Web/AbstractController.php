<?php
namespace Fwlib\Web;

use Fwlib\Web\Helper\GetControllerClassByNamespaceTrait;
use Fwlib\Web\Helper\GetViewClassByNamespaceTrait;

/**
 * Abstract controller
 *
 * Trait of controller and view make this class usable after inherit and
 * overwrite some property, all abstract method are implemented in trait.
 *
 * As empty property will not suit any application, kept this as abstract.
 *
 * @copyright   Copyright 2008-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
abstract class AbstractController implements ControllerInterface
{
    use ControllerTrait;
    use GetControllerClassByNamespaceTrait;
    use GetViewClassByNamespaceTrait;


    /**
     * Search controller class in this namespace, ending \\ included
     *
     * @var string
     */
    protected $controllerNamespace = '';

    /**
     * View to show when empty action given, FQN
     *
     * @var string
     */
    protected $defaultView = '';

    /**
     * Module name
     *
     * If module parsed from user request equals this, will call corresponding
     * View to get output.
     *
     * Root controller can use empty string as module name.
     *
     * @var string
     */
    protected $module = '';

    /**
     * Search view class in this namespace, ending \\ included
     *
     * @var string
     */
    protected $viewNamespace = '';
}
