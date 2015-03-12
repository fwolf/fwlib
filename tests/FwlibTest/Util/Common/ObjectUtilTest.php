<?php
namespace FwlibTest\Util\Common;

use Fwlib\Util\Common\ObjectUtil;
use Fwlib\Util\UtilContainerAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ObjectUtilTest extends PHPUnitTestCase
{
    use UtilContainerAwareTrait;


    /**
     * @return MockObject | ObjectUtil
     */
    protected function buildMock()
    {
        return $this->getUtilContainer()->getObject();
    }


    public function testGetClassName()
    {
        $objectUtil = $this->buildMock();

        $this->assertEquals(
            'ObjectUtil',
            $objectUtil->getClassName(ObjectUtil::class)
        );
    }


    public function testGetNamespace()
    {
        $objectUtil = $this->buildMock();

        $this->assertEquals(
            'Fwlib\Util\Common',
            $objectUtil->getNamespace(ObjectUtil::class)
        );
    }
}
