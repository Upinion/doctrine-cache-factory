<?php

namespace TFC\Cache;

use Doctrine\Common\Cache\CacheProvider;

abstract class CustomCacheProvider extends CacheProvider
{
    protected $namespaceCustom = '';

    protected $namespaceVersionCustom;

    protected $cacheKeyLifetime = 604800;

    public function setNamespace($namespace)
    {
        $this->namespaceCustom = (string) $namespace;
        $this->namespaceVersionCustom = null;
    }

    public function getNamespace()
    {
        return $this->namespaceCustom;
    }

    public function fetch($id)
    {
        return $this->doFetch($this->getNamespacedIdCustom($id));
    }

    public function fetchMultiple(array $keys)
    {
        if (empty($keys)) {
            return array();
        }
        
        // note: the array_combine() is in place to keep an association between our $keys and the $namespacedKeys
        $namespacedKeys = array_combine($keys, array_map(array($this, 'getNamespacedIdCustom'), $keys));
        $items          = $this->doFetchMultiple($namespacedKeys);
        $foundItems     = array();

        // no internal array function supports this sort of mapping: needs to be iterative
        // this filters and combines keys in one pass
        foreach ($namespacedKeys as $requestedKey => $namespacedKey) {
            if (isset($items[$namespacedKey]) || array_key_exists($namespacedKey, $items)) {
                $foundItems[$requestedKey] = $items[$namespacedKey];
            }
        }

        return $foundItems;
    }

    public function contains($id)
    {
        return $this->doContains($this->getNamespacedIdCustom($id));
    }

    public function save($id, $data, $lifeTime = 0)
    {
        return $this->doSave($this->getNamespacedIdCustom($id), $data, $lifeTime);
    }

    public function delete($id)
    {
        return $this->doDelete($this->getNamespacedIdCustom($id));
    }

    public function getStats()
    {
        return $this->doGetStats();
    }

    public function flushAll()
    {
        return $this->doFlush();
    }

    public function deleteAll()
    {
        $namespaceCacheKey = $this->getNamespaceCacheKeyCustom();
        $namespaceVersion  = $this->getNamespaceVersionCustom() + 1;

        if ($this->doSave($namespaceCacheKey, $namespaceVersion, $this->cacheKeyLifetime)) {
            $this->namespaceVersionCustom = $namespaceVersion;

            return true;
        }

        return false;
    }

    protected function getNamespacedIdCustom($id)
    {
        $namespaceVersion  = $this->getNamespaceVersionCustom();

        return sprintf('%s[%s][%s]', $this->namespaceCustom, $id, $namespaceVersion);
    }

    protected function getNamespaceCacheKeyCustom()
    {
        return sprintf(self::DOCTRINE_NAMESPACE_CACHEKEY, $this->namespaceCustom);
    }

    protected function getNamespaceVersionCustom()
    {
        if (null !== $this->namespaceVersionCustom) {
            return $this->namespaceVersionCustom;
        }

        $namespaceCacheKey = $this->getNamespaceCacheKeyCustom();
        $this->namespaceVersionCustom = $this->doFetch($namespaceCacheKey) ?: 1;

        return $this->namespaceVersionCustom;
    }

    public function setCacheKeyLifetime ($value) {
        $this->cacheKeyLifetime = $value;
    }

}
