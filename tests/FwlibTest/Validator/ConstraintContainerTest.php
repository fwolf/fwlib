<?php
namespace FwlibTest\Validator;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\UtilContainer;
use Fwlib\Validator\ConstraintContainer;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
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
