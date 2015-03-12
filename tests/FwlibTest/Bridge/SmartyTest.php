<?php
namespace FwlibTest\Bridge;

use Fwlib\Bridge\Smarty;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class SmartyTest extends PHPUnitTestCase
{
    /**
     * @return Smarty
     */
    protected function buildMock()
    {
        return new Smarty;
    }


    public function testAddConfigDirPrepend()
    {
        $smarty = $this->buildMock();

        $dir = $smarty->getConfigDir();
        $i = count($dir);

        $smarty->addConfigDirPrepend('foo/');

        $dir = $smarty->getConfigDir();
        $this->assertEquals($i + 1, count($dir));
        $this->assertEquals('foo/', $dir['']);
    }


    public function testAddPluginsDirPrepend()
    {
        $smarty = $this->buildMock();

        $dir = $smarty->getPluginsDir();
        $i = count($dir);

        $smarty->addPluginsDirPrepend('foo/');

        $dir = $smarty->getPluginsDir();
        $this->assertEquals($i + 1, count($dir));
        $this->assertEquals('foo/', $dir['']);
    }


    public function testAddTemplateDirPrepend()
    {
        $smarty = $this->buildMock();

        $dir = $smarty->getTemplateDir();
        $i = count($dir);

        $smarty->addTemplateDirPrepend('foo/');

        $dir = $smarty->getTemplateDir();
        $this->assertEquals($i + 1, count($dir));
        $this->assertEquals('foo/', $dir['']);
    }
}
