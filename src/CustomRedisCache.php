<?php

namespace TFC\Cache;

use Doctrine\Common\Cache\RedisCache;
use Redis;

/**
 * Redis cache provider.
 */
class CustomRedisCache extends RedisCache
{
    private $cacheKeyLifetime = 604800;

    /**
     * Sets the cache key lifetime to use.
     *
     * @param int $value
     *
     * @return void
     */
    public function setCacheKeyLifetime($value)
    {
        $this->cacheKeyLifetime = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteAll()
    {
        $namespaceCacheKey = $this->getNamespaceCacheKey();
        $namespaceVersion  = $this->getNamespaceVersion() + 1;

        if ($this->doSave($namespaceCacheKey, $namespaceVersion, $this->cacheKeyLifetime)) {
            $this->namespaceVersion = $namespaceVersion;

            return true;
        }

        return false;
    }

    /**
     * Returns the serializer constant to use. Force SERIALIZER_PHP for compatibility reasons.
     *
     * @return integer One of the Redis::SERIALIZER_* constants
     */
    protected function getSerializerValue()
    {
        return Redis::SERIALIZER_PHP;
    }
}
