<?php
namespace FwlibTest\Web;

use Fwlib\Util\Common\HttpUtil;
use Fwlib\Util\UtilContainer;
use Fwlib\Web\UrlGeneratorTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class UrlGeneratorTraitTest extends PHPUnitTestCase
{
    /**
     * @var HttpUtil
     */
    protected static $httpUtilBackup = null;

    /**
     * @var string[]
     */
    protected static $selfGetParameters = ['foo' => 'bar'];

    /**
     * @var string
     */
    protected static $selfUrl = 'https://domain.tld/index.php?foo=bar';


    /**
     * @return MockObject | UrlGeneratorTrait
     */
    protected function buildMock()
    {
        $urlGenerator = $this->getMockBuilder(UrlGeneratorTrait::class)
            ->getMockForTrait();

        return $urlGenerator;
    }


    public static function setUpBeforeClass()
    {
        $utilContainer = UtilContainer::getInstance();

        self::$httpUtilBackup = $utilContainer->getHttp();

        $testCase = new self;
        $httpUtil = $testCase->getMock(
            HttpUtil::class,
            ['getSelfUrl', 'getGets']
        );
        $httpUtil->expects($testCase->any())
            ->method('getSelfUrl')
            ->willReturn(self::$selfUrl);
        $httpUtil->expects($testCase->any())
            ->method('getGets')
            ->willReturn(self::$selfGetParameters);

        $utilContainer->register('Http', $httpUtil);
    }


    public static function tearDownAfterClass()
    {
        $utilContainer = UtilContainer::getInstance();

        $utilContainer->register('Http', self::$httpUtilBackup);
    }


    public function testGetFullUrl()
    {
        $urlGenerator = $this->buildMock();

        $this->assertEquals(
            self::$selfUrl,
            $urlGenerator->getFullUrl()
        );

        // Without tailing '/', original path '/index.php' will kept
        $urlGenerator->setBaseUrl('http://net.com');
        $this->assertEquals(
            'http://net.com/index.php?foo=bar',
            $urlGenerator->getFullUrl()
        );

        // With tailing '/', the new path is '/'
        $urlGenerator->setBaseUrl('http://net.com/');
        $this->assertEquals(
            'http://net.com/?foo=bar',
            $urlGenerator->getFullUrl()
        );

        $url = 'http://username:password@hostname/path?arg=value#anchor';
        $urlGenerator->setFullUrl($url);
        $this->assertEquals($url, $urlGenerator->getFullUrl());

        $urlGenerator->setParameter('foo', 'bar');
        $url = 'http://username:password@hostname/path?arg=value&foo=bar#anchor';
        $this->assertEquals($url, $urlGenerator->getFullUrl());

        $urlGenerator->setFullUrl('');
        $this->assertEquals('', $urlGenerator->getFullUrl());
    }


    public function testGetLink()
    {
        $urlGenerator = $this->buildMock();

        $this->assertEquals(
            "<a href='?foo=bar' hidden='hidden'>FOO</a>",
            $urlGenerator->getLink('FOO', 'hidden=\'hidden\'')
        );

        $this->assertEquals(
            "<a href='https://domain.tld/index.php?foo=bar' hidden='hidden'>FOO</a>",
            $urlGenerator->getFullLink('FOO', 'hidden=\'hidden\'')
        );
    }


    public function testGetUrl()
    {
        $urlGenerator = $this->buildMock();

        $this->assertEquals(
            '?foo=bar',
            $urlGenerator->getUrl()
        );

        $urlGenerator->setParameter('f2', 42);
        $this->assertEquals(
            '?foo=bar&f2=42',
            $urlGenerator->getUrl()
        );

        $urlGenerator->setParameters([
            'f3' => 420,
            'f4' => '4200',
        ]);
        $this->assertEquals(
            '?foo=bar&f2=42&f3=420&f4=4200',
            $urlGenerator->getUrl()
        );

        $urlGenerator->unsetParameter('f4');
        $this->assertEquals(
            '?foo=bar&f2=42&f3=420',
            $urlGenerator->getUrl()
        );

        $urlGenerator->unsetParameters(['f2', 'f3']);
        $this->assertEquals(
            '?foo=bar',
            $urlGenerator->getUrl()
        );

        $urlGenerator->unsetAllParameters();
        $this->assertEmpty($urlGenerator->getUrl());

        $urlGenerator->reset(true);
        $this->assertEquals(
            '?foo=bar',
            $urlGenerator->getUrl()
        );
    }
}
