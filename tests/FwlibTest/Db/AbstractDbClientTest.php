<?php
namespace FwlibTest\Db;

use Fwlib\Config\GlobalConfig;
use FwlibTest\Aide\TestServiceContainer;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractDbClientTest extends PHPUnitTestCase
{
    public function testNewDb()
    {
        $dbProfile = GlobalConfig::getInstance()->get('dbserver.default');
        if (empty($dbProfile['host'])) {
            $this->markTestSkipped();
        }

        $sc = TestServiceContainer::getInstance();

        // With ServiceContainer
        $o = new AbstractDbClientDummy();
        $o->setServiceContainer($sc);
        $this->reflectionCall($o, 'getDb');
        $this->assertNotNull($this->reflectionGet($o, 'db'));

        // With Dependency Inject
        $o = new AbstractDbClientDummy($sc->get('Db'));
        $this->assertNotNull($this->reflectionGet($o, 'db'));
    }
}
