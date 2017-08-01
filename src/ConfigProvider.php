<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-cache for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-cache/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Cache;

class ConfigProvider
{
    /**
     * Return the configuration array.
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'cache'        => [
                'service_name' => '',   // Service name of a PSR-16 adapter to use
                'ttl'          => 3600, // seconds
            ],
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies() : array
    {
        return [
            'factories'  => [
                CacheMiddleware::class => CacheMiddlewareFactory::class,
            ],
        ];
    }
}
