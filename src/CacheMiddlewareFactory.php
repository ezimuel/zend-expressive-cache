<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-cache for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-cache/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Cache;

use Psr\Container\ContainerInterface;
use Zend\Cache\StorageFactory;

class CacheMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : CacheMiddleware
    {
        $config = $container->get('config');
        $config = $config['cache'] ?? [];

        if (empty($config['service_name'])) {
            throw new Exception\InvalidConfigException(
                'The cache service_name value is not configured'
            );
        }

        return new CacheMiddleware(
            $container->get($config['service_name']),
            $config
        );
    }
}
