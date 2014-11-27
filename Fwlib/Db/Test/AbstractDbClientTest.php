<?php
namespace Fwlib\Db\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Config\GlobalConfig;
use Fwlib\Db\Test\AbstractDbClientDummy;
use Fwlib\Test\ServiceContainerTest as ServiceContainerTest;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 */
class AbstractDbClientTest extends PHPunitTestCase
{
    public function testNewDb()
    {
        $dbProfile = GlobalConfig::getInstance()->get('dbserver.default');
        if (empty($dbProfile['host'])) {
            $this->markTestSkipped();
        }

        $sc = ServiceContainerTest::getInstance();

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
