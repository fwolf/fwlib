<?php
namespace Fwlib\Db\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Config\GlobalConfig;
use Fwlib\Db\Test\AbstractDbClientDummy;
use Fwlib\Test\ServiceContainerTest as ServiceContainerTest;

/**
 * Test for Fwlib\Db\AbstractDbClient
 *
 * @package     Fwlib\Db\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-10-08
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
        $this->assertFalse(isset($o->db));
        $o->db;
        $this->assertTrue(isset($o->db));

        // With Dependency Inject
        $o = new AbstractDbClientDummy($sc->get('Db'));
        $this->assertTrue(isset($o->db));
    }
}
