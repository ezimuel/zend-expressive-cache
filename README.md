# Expressive Cache module

[![Build Status](https://secure.travis-ci.org/ezimuel/zend-expressive-cache.svg?branch=master)](https://secure.travis-ci.org/ezimuel/zend-expressive-cache)
[![Coverage Status](https://coveralls.io/repos/github/ezimuel/zend-expressive-cache/badge.svg?branch=master)](https://coveralls.io/github/ezimuel/zend-expressive-cache?branch=master)

Zend-expressive-cache is an [Expressive](https://github.com/zendframework/zend-expressive)
module for caching any HTTP [PSR-7](http://www.php-fig.org/psr/psr-7/) response
using a [PSR-16](http://www.php-fig.org/psr/psr-16/) cache system.

## Getting Started

You can install the *zend-expressive-cache* module with composer:

```bash
$ composer require ezimuel/zend-expressive-cache
```

After the installation you need to set a ['cache']['service-name'] value in a
configuration file. This is the name of a PSR-16 cache service that you need
to provide in the [PSR-11](https://github.com/php-fig/container) container of
your Expressive application.

When configured, you need to add the `CacheMiddleware` to the routes that you
want to cache. For instance, image you have a `/home` route, you can add the
caching as follows:

```php
use Zend\Expressive\Cache\CacheMiddleware;
use App\Action\HomeAction;

$app->get('/', [CacheMiddleware::class, HomeAction::class], 'home');
```

The `CacheMiddleware` is typically the first one in the pipe of the route.
