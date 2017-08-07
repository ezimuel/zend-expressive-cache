# Expressive Cache module

[![Build Status](https://secure.travis-ci.org/zendframework/zend-expressive-cache.svg?branch=master)](https://secure.travis-ci.org/zendframework/zend-expressive-cache)
[![Coverage Status](https://coveralls.io/repos/github/zendframework/zend-expressive-cache/badge.svg?branch=master)](https://coveralls.io/github/zendframework/zend-expressive-cache?branch=master)

Zend-expressive-cache is an [Expressive](https://github.com/zendframework/zend-expressive)
module for caching any HTTP [PSR-7](http://www.php-fig.org/psr/psr-7/) response
using a [PSR-16](http://www.php-fig.org/psr/psr-16/) cache system.

## Installation

You can install the *zend-expressive-cache* module with composer:

```bash
$ composer require zendframework/zend-expressive-cache
```

## Documentation

Documentation is [in the doc tree](doc/book/), and can be compiled using [mkdocs](http://www.mkdocs.org):

```bash
$ mkdocs build
```

You may also [browse the documentation online](https://docs.zendframework.com/problem-details/).

## Configuration

After the installation you need to set the following configuration in your
*Expressive* application:

```php
return [
    'cache' => [
        'service-name' => <cache-service-name>,
        'ttl' => 3600, // in seconds
    ]
];
```

where `<cache-service-name>` is the name of a PSR-16 cache service and `ttl` is
the *Time to live* value, reported in seconds.
The cache service should be provided using using a [PSR-11](https://github.com/php-fig/container)
container of your *Expressive* application.

## Usage

When configured, you can add the `CacheMiddleware` to the routes that you
want to cache. For instance, if you have a `/home` route, you can add the
caching as follows:

```php
use Zend\Expressive\Cache\CacheMiddleware;
use App\Action\HomeAction;

$app->get('/home', [CacheMiddleware::class, HomeAction::class], 'home');
```

The `CacheMiddleware` is typically the first one in the pipe of the route. The
first time, the cache will store the HTTP response provided by `HomeAction::class`.
Starting from the second request, the cache will return the value stored until
the `ttl` timeout.
