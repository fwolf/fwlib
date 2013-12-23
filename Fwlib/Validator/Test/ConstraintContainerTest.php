<?php
namespace Fwlib\Validator\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\UtilContainer;
use Fwlib\Validator\ConstraintContainer;

/**
 * Test for Fwlib\Base\ReturnValue
 *
 * @package     Fwlib\Base\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-05-03
 */
class ConstraintContainerTest extends PHPunitTestCase
{
    protected $constraintContainer;
    protected $utilContainer;


    public function __construct()
    {
        $this->constraintContainer = ConstraintContainer::getInstance();
        $this->utilContainer = UtilContainer::getInstance();
    }


    public function testGet()
    {
        $urlConstraint = $this->constraintContainer->get('Url');
        $this->assertInstanceOf(
            'Fwlib\Validator\Constraint\Url',
            $urlConstraint
        );
        $this->assertInstanceOf(
            'Fwlib\Util\UtilContainer',
            $this->reflectionGet($urlConstraint, 'utilContainer')
        );
    }


    public function testSetUtilContainer()
    {
        $this->constraintContainer->setUtilContainer(null);
        $this->assertInstanceOf(
            'Fwlib\Util\UtilContainer',
            $this->reflectionGet($this->constraintContainer, 'utilContainer')
        );

        $this->constraintContainer->setUtilContainer($this->utilContainer);
        $this->assertInstanceOf(
            'Fwlib\Util\UtilContainer',
            $this->reflectionGet($this->constraintContainer, 'utilContainer')
        );
    }
}
