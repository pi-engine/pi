<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Cache\Storage\Adapter;

use Zend\Cache\Storage\Adapter\Redis as ZendRedis;
use Exception as BaseException;
use Zend\Cache\Storage\Adapter\Exception;

/**
 * Redis cache adapter
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Redis extends ZendRedis
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
        
        $redis      = $this->getRedisResource();
        $result     = $redis->get($prefix);
        $existsKeys = array();
        if ($result) {
            $existsKeys = unserialize($result);
        }
        foreach ($existsKeys as $key) {
            $redis->delete($prefix . $key);
        }
        $redis->delete($prefix);

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
        $redis      = $this->getRedisResource();
        $result     = $redis->get($this->namespacePrefix);
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
        $redis = $this->getRedisResource();
        
        // Use namespace to manage all keys belong to it
        $existsKeys = array();
        $result     = $redis->get($this->namespacePrefix);
        if ($result) {
            $existsKeys = unserialize($result);
        }
        $existsKeys[$normalizedKey] = $normalizedKey;
        $namespaceNormalizedKey = '';
        $existsKeysValue = serialize($existsKeys);
        parent::internalSetItem($namespaceNormalizedKey, $existsKeysValue);
        
        // Set item to redis
        return parent::internalSetItem($normalizedKey, $value);
    }
}
