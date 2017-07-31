<?php
namespace ZendTest\Expressive\Cache;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Cache\CacheMiddlewareFactory;
use Psr\SimpleCache\CacheInterface;
use Zend\Expressive\Cache\CacheMiddleware;

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
    public function testInvokeWihoutConfig()
    {
        $middleware = ($this->factory)($this->container->reveal());
    }

    public function testInvoke()
    {
        $config = [
            'cache' => [
                'service-name' => 'foo'
            ]
        ];
        $this->container->get('config')
                        ->willReturn($config);

        $this->container->get($config['cache']['service-name'])
                        ->willReturn($this->cache->reveal());

        $middleware = ($this->factory)($this->container->reveal());

        $this->assertInstanceOf(CacheMiddleware::class, $middleware);
    }
}
