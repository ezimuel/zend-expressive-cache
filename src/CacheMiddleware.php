<?php
namespace Zend\Expressive\Cache;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;

class CacheMiddleware implements ServerMiddlewareInterface
{
    private $cache;
    private $config;

    public function __construct(CacheInterface $cache, array $config)
    {
        $this->cache = $cache;
        $this->config = $config;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $key = $request->getMethod() . $request->getUri()->getPath();
        $value = $this->cache->get($key);
        if (null !== $value) {
            return unserialize($value);
        }
        $response = $delegate->process($request);
        $this->cache->set(
            $key,
            serialize($response),
            $this->config['ttl'] ?? null
        );
        return $response;
    }
}
