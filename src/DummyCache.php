<?php

namespace TFC\Cache;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\Cache;

/**
 * Dummy cache provider.
 */
class DummyCache extends CacheProvider
{
    public function __construct() { }

    /**
     * {@inheritdoc}
     */
    protected function doFetch($id)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetchMultiple(array $keys)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function doSaveMultiple(array $keysAndValues, $lifetime = 0)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doContains($id)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doSave($id, $data, $lifeTime = 0)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($id)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDeleteMultiple(array $keys)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFlush()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetStats()
    {
        return [
            Cache::STATS_HITS              => 0,
            Cache::STATS_MISSES            => 0,
            Cache::STATS_UPTIME            => 0,
            Cache::STATS_MEMORY_USAGE      => 0,
            Cache::STATS_MEMORY_AVAILABLE  => false,
        ];
    }
}
