<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-cache for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-cache/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Cache;

use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Zend\Expressive\Cache\CacheMiddleware;
use Prophecy\Argument;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response\TextResponse;

class CacheMiddlewareTest extends TestCase
{
    protected function setUp()
    {
        $this->cache = $this->prophesize(CacheInterface::class);
        $this->request = $this->prophesize(ServerRequestInterface::class);

        $this->stream = $this->prophesize(StreamInterface::class);

        $this->response = $this->prophesize(ResponseInterface::class);
        $this->response
            ->getBody()
            ->willReturn($this->stream->reveal());

        $this->delegate = $this->prophesize(DelegateInterface::class);
        $this->delegate
            ->process($this->request->reveal())
            ->willReturn($this->response->reveal());
    }

    public function testConstructor()
    {
        $middleware = new CacheMiddleware($this->cache->reveal(), []);
        $this->assertInstanceOf(CacheMiddleware::class, $middleware);
    }

    public function getConfigEmptyCache()
    {
        return [
            'ttl-null' => ['GET', '/', null, []],
            'ttl-1h'   => ['GET', '/', null, ['ttl' => 3600]],
        ];
    }

    /**
     * @dataProvider getConfigEmptyCache
     */
    public function testProcess(string $method, string $path, ?string $toCache, array $config)
    {
        $this->cache->get($method . $path)->willReturn($toCache);
        $this->cache
            ->set(
                Argument::type('string'),
                Argument::type('string'),
                Argument::any()
            )
            ->willReturn(true);

        $uri = $this->prophesize(UriInterface::class);
        $uri->getPath()->willReturn($path);

        $this->request->getMethod()->willReturn($method);
        $this->request->getUri()->willReturn($uri->reveal());

        $middleware = new CacheMiddleware($this->cache->reveal(), $config);
        $this->assertInstanceOf(CacheMiddleware::class, $middleware);

        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );
        $this->assertInstanceOf(ResponseInterface::class, $response);

        return $response;
    }

    public function getConfigWithCache()
    {
        return [
            'html-nottl'     => ['GET', '/', serialize(new HtmlResponse('test')), []],
            'html-ttl'       => ['GET', '/', serialize(new HtmlResponse('test')), [ 'ttl' => 3600 ]],
            'empty-nottl'    => ['GET', '/', serialize(new EmptyResponse()), []],
            'empty-ttl'      => ['GET', '/', serialize(new EmptyResponse()), [ 'ttl' => 3600 ]],
            'json-nottl'     => ['GET', '/', serialize(new JsonResponse(['result' => 'test'])), []],
            'json-ttl'       => ['GET', '/', serialize(new JsonResponse(['result' => 'test'])), [ 'ttl' => 3600 ]],
            'redirect-nottl' => ['GET', '/', serialize(new RedirectResponse('/redirect')), []],
            'redirect-ttol'  => ['GET', '/', serialize(new RedirectResponse('/redirect')), [ 'ttl' => 3600 ]],
            'text-nottl'     => ['GET', '/', serialize(new TextResponse('test')), []],
            'text-ttl'       => ['GET', '/', serialize(new TextResponse('test')), [ 'ttl' => 3600 ]],
        ];
    }

    /**
     * @dataProvider getConfigWithCache
     */
    public function testProcessWithCache(string $method, string $path, string $toCache, array $config)
    {
        $response = $this->testProcess($method, $path, $toCache, $config);
        $this->assertInstanceOf(get_class(unserialize($toCache)), $response);
        $this->assertEquals(unserialize($toCache), $response);
    }
}
