<?php
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
        $this->response->getBody()
                       ->willReturn($this->stream->reveal());

        $this->delegate = $this->prophesize(DelegateInterface::class);
        $this->delegate->process($this->request->reveal())
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
            ['GET', '/', null, []],
            ['GET', '/', null, [ 'ttl' => 3600 ]]
        ];
    }

    /**
     * @dataProvider getConfigEmptyCache
     */
    public function testProcess($method, $url, $item, $config)
    {
        $this->cache->get($method . $url)->willReturn($item);
        $this->cache->set(
            Argument::type('string'),
            Argument::type('string'),
            Argument::any()
        )->willReturn(true);

        $uri = $this->prophesize(UriInterface::class);
        $uri->getPath()->willReturn($url);

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
            ['GET', '/', serialize(new HtmlResponse('test')), []],
            ['GET', '/', serialize(new HtmlResponse('test')), [ 'ttl' => 3600 ]],
            ['GET', '/', serialize(new EmptyResponse()), []],
            ['GET', '/', serialize(new EmptyResponse()), [ 'ttl' => 3600 ]],
            ['GET', '/', serialize(new JsonResponse(['result' => 'test'])), []],
            ['GET', '/', serialize(new JsonResponse(['result' => 'test'])), [ 'ttl' => 3600 ]],
            ['GET', '/', serialize(new RedirectResponse('/redirect')), []],
            ['GET', '/', serialize(new RedirectResponse('/redirect')), [ 'ttl' => 3600 ]],
            ['GET', '/', serialize(new TextResponse('test')), []],
            ['GET', '/', serialize(new TextResponse('test')), [ 'ttl' => 3600 ]],
        ];
    }

    /**
     * @dataProvider getConfigWithCache
     */
    public function testProcessWithCache($method, $url, $item, $config)
    {
        $response = $this->testProcess($method, $url, $item, $config);
        $this->assertInstanceOf(get_class(unserialize($item)), $response);
        $this->assertEquals(unserialize($item), $response);
    }
}
