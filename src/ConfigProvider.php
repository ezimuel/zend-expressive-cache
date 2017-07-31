<?php
namespace Zend\Expressive\Cache;;

class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method or file which returns an array with its configuration.
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'cache'        => include __DIR__ . '/../config/cache.php'
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies() : array
    {
        return [
            'factories'  => [
                CacheMiddleware::class => CacheMiddlewareFactory::class
            ]
        ];
    }
}
