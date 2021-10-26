<?php

namespace TFC\Cache;

use TFC\Cache\CustomCacheProvider;
use Doctrine\Common\Cache\Cache;
use Redis;

/**
 * Redis cache provider.
 */
class CustomRedisCache extends CustomCacheProvider
{
    /**
     * @var Redis|null
     */
    private $redis;

    /**
     * @var Redis|null
     */
    private $roRedis;

    /**
     * Sets the redis instance to use.
     *
     * @param Redis $redis
     *
     * @return void
     */
    public function setRedis(Redis $redis)
    {
        $redis->setOption(Redis::OPT_SERIALIZER, $this->getSerializerValue());
        $this->redis = $redis;
        if (!$this->roRedis) {
            $this->roRedis = $redis;
        }
    }

    /**
     * Sets the read-only redis instance to use.
     *
     * @param Redis $redis
     *
     * @return void
     */
    public function setReadOnlyRedis(Redis $redis)
    {
        $redis->setOption(Redis::OPT_SERIALIZER, $this->getSerializerValue());
        $this->roRedis = $redis;
    }

    /**
     * Gets the redis instance used by the cache.
     *
     * @return Redis|null
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * Gets the redis instance used by the cache.
     *
     * @return Redis|null
     */
    public function getReadOnlyRedis()
    {
        return $this->roRedis;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetch($id)
    {
        return $this->roRedis->get($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetchMultiple(array $keys)
    {
        $fetchedItems = array_combine($keys, $this->roRedis->mget($keys));

        // Redis mget returns false for keys that do not exist. So we need to filter those out unless it's the real data.
        $foundItems   = array();

        foreach ($fetchedItems as $key => $value) {
            if (false !== $value || $this->roRedis->exists($key)) {
                $foundItems[$key] = $value;
            }
        }

        return $foundItems;
    }

    /**
     * {@inheritdoc}
     */
    protected function doContains($id)
    {
        return $this->roRedis->exists($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function doSave($id, $data, $lifeTime = 0)
    {
        if ($lifeTime > 0) {
            return $this->redis->setex($id, $lifeTime, $data);
        }

        return $this->redis->set($id, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($id)
    {
        return $this->redis->delete($id) >= 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFlush()
    {
        return $this->redis->flushDB();
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetStats()
    {
        $info = $this->roRedis->info();
        return array(
            Cache::STATS_HITS   => $info['keyspace_hits'],
            Cache::STATS_MISSES => $info['keyspace_misses'],
            Cache::STATS_UPTIME => $info['uptime_in_seconds'],
            Cache::STATS_MEMORY_USAGE      => $info['used_memory'],
            Cache::STATS_MEMORY_AVAILABLE  => false
        );
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
