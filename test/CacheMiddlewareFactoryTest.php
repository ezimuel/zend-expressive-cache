<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-cache for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-cache/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Cache;

use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Zend\Expressive\Cache\CacheMiddleware;
use Zend\Expressive\Cache\CacheMiddlewareFactory;
use Zend\Expressive\Cache\Exception\InvalidConfigException;

class CacheMiddlewareFactoryTest extends TestCase
{
    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new CacheMiddlewareFactory();
        $this->cache = $this->prophesize(CacheInterface::class);
    }

    /**
     * @expectedException Zend\Expressive\Cache\Exception\InvalidConfigException
     */
    public function testFactoryFailsWhenInvokedWihoutConfigService()
    {
        $exception = $this->prophesize(Exception::class)
            ->willImplement(ContainerExceptionInterface::class)
            ->reveal();
        $this->container->get('config')->willThrow($exception);

        $this->expectException(ContainerExceptionInterface::class);
        $middleware = ($this->factory)($this->container->reveal());
    }

    public function testFactoryRaisesExceptionWhenInvokedWithConfigServiceMissingCacheServiceName()
    {
        $this->container->get('config')->willReturn(['cache' => []]);

        $this->expectException(InvalidConfigException::class);
        $middleware = ($this->factory)($this->container->reveal());
    }

    public function testFactoryRaisesExceptionWhenInvokedWithInvalidCacheServiceName()
    {
        $exception = $this->prophesize(Exception::class)
            ->willImplement(ContainerExceptionInterface::class)
            ->reveal();

        $this->container->get('config')->willReturn(['cache' => ['service_name' => 'foo']]);
        $this->container->get('foo')->willThrow($exception);

        $this->expectException(ContainerExceptionInterface::class);
        $middleware = ($this->factory)($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredCacheMiddlewareWhenValidConfigExists()
    {
        $config = [
            'cache' => [
                'service_name' => 'foo',
            ],
        ];
        $this->container
            ->get('config')
            ->willReturn($config);

        $this->container
            ->get($config['cache']['service_name'])
            ->willReturn($this->cache->reveal());

        $middleware = ($this->factory)($this->container->reveal());

        $this->assertInstanceOf(CacheMiddleware::class, $middleware);
    }
}
