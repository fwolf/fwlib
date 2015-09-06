<?php
namespace FwlibTest\Web\Helper;

use Fwlib\Util\Common\HttpUtil;
use Fwlib\Util\UtilContainer;
use Fwlib\Web\Helper\HttpRequestWrapperTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class HttpRequestWrapperTraitTest extends PHPUnitTestCase
{
    /**
     * @var HttpUtil
     */
    protected $httpUtilBak = null;


    public function beginHttpUtilMock()
    {
        $utilContainer = UtilContainer::getInstance();
        $this->httpUtilBak = $utilContainer->getHttp();

        /** @var MockObject|HttpUtil $httpUtil */
        $httpUtil = $this->getMock(
            HttpUtil::class,
            [
                'getCookie',
                'getCookies',
                'getGet',
                'getGets',
                'getPost',
                'getPosts',
            ]
        );

        $httpUtil->expects($this->once())->method('getCookie');
        $httpUtil->expects($this->once())->method('getCookies');
        $httpUtil->expects($this->once())->method('getGet');
        $httpUtil->expects($this->once())->method('getGets');
        $httpUtil->expects($this->once())->method('getPost');
        $httpUtil->expects($this->once())->method('getPosts');

        $utilContainer->register('Http', $httpUtil);
    }


    /**
     * @param   string[] $methods
     * @return  MockObject|HttpRequestWrapperTrait
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMockBuilder(HttpRequestWrapperTrait::class)
            ->setMethods($methods)
            ->getMockForTrait();

        return $mock;
    }


    public function endHttpUtilMock()
    {
        $utilContainer = UtilContainer::getInstance();

        $utilContainer->register('Http', $this->httpUtilBak);
    }


    public function testAccessors()
    {
        $this->beginHttpUtilMock();

        $trait = $this->buildMock();

        $trait->getCookie('dummy');
        $trait->getCookies();
        $trait->getGet('dummy');
        $trait->getGets();
        $trait->getPost('dummy');
        $trait->getPosts();

        $this->endHttpUtilMock();
    }
}
