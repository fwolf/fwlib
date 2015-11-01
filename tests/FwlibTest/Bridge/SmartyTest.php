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
        $dirCount = count($dir);

        $smarty->addConfigDirPrepend('foo/');

        $dir = $smarty->getConfigDir();
        $this->assertEquals($dirCount + 1, count($dir));
        $this->assertEquals('foo/', $dir['']);


        // Add again to check key overwrite
        $smarty->addConfigDirPrepend('bar/');
        $dir = $smarty->getConfigDir();
        $this->assertEquals('bar/', $dir['']);
    }


    public function testAddPluginsDirPrepend()
    {
        $smarty = $this->buildMock();

        $dir = $smarty->getPluginsDir();
        $dirCount = count($dir);

        $smarty->addPluginsDirPrepend('foo/');

        $dir = $smarty->getPluginsDir();
        $this->assertEquals($dirCount + 1, count($dir));
        $this->assertEquals('foo/', $dir['']);


        // Add again to check key overwrite
        $smarty->addPluginsDirPrepend('bar/');
        $dir = $smarty->getPluginsDir();
        $this->assertEquals('bar/', $dir['']);
    }


    public function testAddTemplateDirPrepend()
    {
        $smarty = $this->buildMock();

        $dir = $smarty->getTemplateDir();
        $dirCount = count($dir);

        $smarty->addTemplateDirPrepend('foo/');

        $dir = $smarty->getTemplateDir();
        $this->assertEquals($dirCount + 1, count($dir));
        $this->assertEquals('foo/', $dir['']);


        // Add again to check key overwrite
        $smarty->addTemplateDirPrepend('bar/');
        $dir = $smarty->getTemplateDir();
        $this->assertEquals('bar/', $dir['']);
    }
}
