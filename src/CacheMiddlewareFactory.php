<?php
namespace Zend\Expressive\Cache;

use Psr\Container\ContainerInterface;
use Zend\Cache\StorageFactory;

class CacheMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : CacheMiddleware
    {
        $config = $container->get('config');
        if (!isset($config['cache']['service-name']) ||
            empty($config['cache']['service-name'])) {
            throw new Exception\InvalidConfigException(
                'The cache service-name value is not configured'
            );
        }

        return new CacheMiddleware(
            $container->get($config['cache']['service-name']),
            $config['cache']
        );
    }
}
