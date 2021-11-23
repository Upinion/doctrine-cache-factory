Doctrine Cache factory
==========================
[![Total Downloads](https://poser.pugx.org/thefuriouscoder/doctrine-cache-factory/downloads.png)](https://packagist.org/packages/thefuriouscoder/doctrine-cache-factory)

A simple static wrapper for Doctrine Cache drivers.

Requirements
------------
- PHP >= 5.3
- [Doctrine Cache](https://github.com/doctrine/cache) >= 1.5.1


Install
-------

### Composer

Add a dependency on `thefuriouscoder/doctrine-cache-factory` to your project's `composer.json` file.

```javascript
{
    "require": {
        "thefuriouscoder/doctrine-cache-factory": "dev-master"
    }
}
```

Configuration
-------------

### Initialize the Cache Store.

Add the following configuration code to your project bootstraping file depending on the storage you are goinng to use.

#### Currently working drivers
- Redis
- Memcached
- Dummy
##### Other drivers
- TODO add support for more drivers

#### Driver customise parameters
```
- prefix: Prefix appended to the elements key in the storage
- idPrefix: Prefix appened to the id of the elements when generating the key
- forceDummy: Use a dummy driver instead
```

#### Key storage format

```
{prefix}{namespace}[{idPrefix}{id}][{namespaceVersion}]
```

### Using Memcached (php5-memcached extension needed)
```php
// configure memcached setting.
TFC\Cache\DoctrineCacheFactory::setOption(
    [
        'storage'     => 'memcached',
        'prefix'      => 'rlyeh',
        'idPrefix'    => 'php_',
        'default_ttl' => 3600,
        'servers'     => [
            ['server1', 11211, 20],
            ['server2', 11211, 80]
        ]
    ]
);

```

### Using Redis
```php
// configure Redis setting.
TFC\Cache\DoctrineCacheFactory::setOption(
    [
        'storage'     => 'redis',
        'prefix'      => 'rlyeh',
        'prefix'      => 'php_',
        'host'        => '127.0.0.1',
        'port'        => 6379,
        'default_ttl' => 3600
    ]
);
// configure Redis with readonly setting.
TFC\Cache\DoctrineCacheFactory::setOption(
    [
        'storage'     => 'redis',
        'prefix'      => 'rlyeh',
        'idPrefix'    => 'php_',
        'host'        => '127.0.0.1',
        'port'        => 6379,
        'hostRO'        => '127.0.0.2',
        'portRO'        => 6379,
        'default_ttl' => 3600
    ]
);

```

### Basic usage

```php
$cache = \TFC\Cache\DoctrineCacheFactory::factory("redis");
$cache->setNamespace("miskatonic");
$cache->save($key,$data);
$cache->contains($key);
$data = $cache->fetch($key);
$cache->delete($key);
$cache->deleteAll();

```

For more detailed instructions on Doctrine cache usage, please refer to doctrine [documentation](http://doctrine-orm.readthedocs.org/projects/doctrine-orm/en/latest/reference/caching.html)

License
-------

MIT License
