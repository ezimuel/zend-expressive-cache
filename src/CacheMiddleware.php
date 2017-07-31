<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-cache for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-cache/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Cache;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;

class CacheMiddleware implements ServerMiddlewareInterface
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var array
     */
    private $config;

    public function __construct(CacheInterface $cache, array $config)
    {
        $this->cache = $cache;
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
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
