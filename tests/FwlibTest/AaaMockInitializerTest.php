<?php
namespace FwlibTest;

use Fwlib\Test\Mock\ExtensionLoadedMockTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * Initializer of all PHP system function being mocked
 *
 * Put in root of test case namespace with name start with 'Aaa' to make sure
 * PHPUnit run it first.
 *
 * The trait need use in every test case want to mock system function. I tried
 * make a container to store them for reuse, not work.
 *
 * The PHPUnit will scan and execute constructor of every test case before run
 * them(include this one), so code in constructor may use system function first
 * and make system function mock not work.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AaaMockInitializerTest extends PHPUnitTestCase
{
    use ExtensionLoadedMockTrait;


    /**
     * Register PHP native function mock here (with define)
     *
     * If all test case constructor are empty, this can be deleted.
     */
    public function testMockRegister()
    {
        $this->buildExtensionLoadedMock('Fwlib\Util');

        $this->buildSessionStatusMock('Fwlib\Util');
        $this->buildSessionStartMock('Fwlib\Util');
        $this->buildSessionDestroyMock('Fwlib\Util');

        $this->assertTrue(true);
    }


    public function testMockSuccessful()
    {
        $extensionLoadedMock = $this->buildExtensionLoadedMock('FwlibTest');

        $extensionLoadedMock->enable();

        self::$extensionLoaded = false;
        $this->assertFalse(extension_loaded('pcre'));

        self::$extensionLoaded = true;
        $this->assertTrue(extension_loaded('pcre'));

        $extensionLoadedMock->disable();
    }
}
