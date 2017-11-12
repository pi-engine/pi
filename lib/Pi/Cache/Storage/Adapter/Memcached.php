<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Cache\Storage\Adapter;

use Zend\Cache\Storage\Adapter\Memcached as ZendMemcached;
use Exception as BaseException;
use Zend\Cache\Storage\Adapter\Exception;

/**
 * Memcached cache adapter
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Memcached extends ZendMemcached
{
    /**
     * Remove items by given namespace.
     * Keys belong to this namespace will first be searched and then their value
     * will be removed from memcached one by one.
     *
     * @param string $namespace
     * @throws Exception\RuntimeException
     * @return bool
     */
    public function clearByNamespace($namespace)
    {
        $namespace = (string) $namespace;
        if ($namespace === '') {
            throw new Exception\InvalidArgumentException('No namespace given');
        }

        $options = $this->getOptions();
        $prefix  = $namespace . $options->getNamespaceSeparator();
        
        $memc       = $this->getMemcachedResource();
        $result     = $memc->get($prefix);
        $existsKeys = array();
        if ($result) {
            $existsKeys = unserialize($result);
        }
        foreach ($existsKeys as $key) {
            $memc->delete($prefix . $key);
        }

        return true;
    }
    
    /**
     * Add custom code to check if key is exists in item with namespace as key.
     * Null value will be returned if it is not exists, therefore, the key will
     * be rebuilded into the item and then it can be flushed.
     * 
     * {@inheritDoc}
     */
    public function getItem($key, &$success = null, &$casToken = null)
    {
        $memc       = $this->getMemcachedResource();
        $result     = $memc->get($this->namespacePrefix);
        if (!$result) {
            return null;
        }
        $existsKeys = unserialize($result);
        $this->normalizeKey($key);
        if (!isset($existsKeys[$key])) {
            return null;
        }
        
        return parent::getItem($key, $success, $casToken);
    }
    
    /**
     * Custom internal method to store an item, use a item with namespace as key
     * to manage all keys so that memcached can be flushed by namespace.
     * 
     * <code>
     * `{namespacePrefix}` = array(
     *     `{namespacePrefix}{normalizedKey1}` => `{namespacePrefix}{normalizedKey1}`,
     *     `{namespacePrefix}{normalizedKey2}` => `{namespacePrefix}{normalizedKey2}`,
     *     ...
     * )
     * </code>
     *
     * {@inheritDoc}
     */
    protected function internalSetItem(& $normalizedKey, & $value)
    {
        $memc       = $this->getMemcachedResource();
        $expiration = $this->expirationTime();
        
        // Use namespace to manage all keys belong to it
        $existsKeys = array();
        $result     = $memc->get($this->namespacePrefix);
        if ($result) {
            $existsKeys = unserialize($result);
        }
        $existsKeys[$normalizedKey] = $normalizedKey;
        $memc->set($this->namespacePrefix, serialize($existsKeys), $expiration);
        
        // Set item to memcached
        if (!$memc->set($this->namespacePrefix . $normalizedKey, $value, $expiration)) {
            throw $this->getExceptionByResultCode($memc->getResultCode());
        }

        return true;
    }
}
