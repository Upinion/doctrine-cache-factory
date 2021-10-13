<?php

namespace TFC\Cache;

use TFC\Cache\CustomCacheProvider;
use Doctrine\Common\Cache\Cache;
use \Memcached;

/**
 * Memcached cache provider.
 */
class CustomMemcachedCache extends CustomCacheProvider
{
    /**
     * @var Memcached|null
     */
    private $memcached;

    /**
     * Sets the memcached instance to use.
     *
     * @param Memcached $memcached
     *
     * @return void
     */
    public function setMemcached(Memcached $memcached)
    {
        // HOTFIX fix problem between compression in hhvm and php
        $memcached->setOption(Memcached::OPT_COMPRESSION, false);
        $memcached->setOption(Memcached::OPT_SERIALIZER, $this->getSerializerValue());
        $this->memcached = $memcached;
    }

    /**
     * Gets the memcached instance used by the cache.
     *
     * @return Memcached|null
     */
    public function getMemcached()
    {
        return $this->memcached;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetch($id)
    {
        return $this->memcached->get($id);
    }

    /**
     * {@inheritdoc}
     */
    
    protected function doFetchMultiple(array $keys)
    {
        return $this->memcached->getMulti($keys) ?: [];
    }


    /**
     * {@inheritdoc}
     */
    protected function doContains($id)
    {
        $this->memcached->get($id);

        return $this->memcached->getResultCode() === Memcached::RES_SUCCESS;
    }


    /**
     * {@inheritdoc}
     */
    protected function doSave($id, $data, $lifeTime = 0)
    {
        if ($lifeTime > 30 * 24 * 3600) {
            $lifeTime = time() + $lifeTime;
        }
        return $this->memcached->set($id, $data, (int) $lifeTime);
    }


    /**
     * {@inheritdoc}
     */
    protected function doDelete($id)
    {
        return $this->memcached->delete($id)
            || $this->memcached->getResultCode() === Memcached::RES_NOTFOUND;
    }


    /**
     * {@inheritdoc}
     */
    protected function doFlush()
    {
        return $this->memcached->flush();
    }


    /**
     * {@inheritdoc}
     */
    protected function doGetStats()
    {
        $stats   = $this->memcached->getStats();
        $servers = $this->memcached->getServerList();
        $key     = $servers[0]['host'] . ':' . $servers[0]['port'];
        $stats   = $stats[$key];
        return array(
            Cache::STATS_HITS   => $stats['get_hits'],
            Cache::STATS_MISSES => $stats['get_misses'],
            Cache::STATS_UPTIME => $stats['uptime'],
            Cache::STATS_MEMORY_USAGE     => $stats['bytes'],
            Cache::STATS_MEMORY_AVAILABLE => $stats['limit_maxbytes'],
        );
    }


    /**
     * Returns the serializer constant to use. Force SERIALIZER_PHP for compatibility reasons.
     *
     * @return integer One of the Memcached::SERIALIZER_* constants
     */
    protected function getSerializerValue()
    {
        // HOTFIX hhvm only allows the php serializer
        return Memcached::SERIALIZER_PHP;
    }
}
