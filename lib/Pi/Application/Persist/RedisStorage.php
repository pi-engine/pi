<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Persist;

use Pi;
use Redis;

/**
 * Redis storage
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class RedisStorage extends AbstractStorage
{
    /**
     * Server Values
     */
    /** @var string */
    const SERVER_HOST = '127.0.0.1';

    /** @var int */
    const SERVER_PORT =  6379;

    /** @var int */
    const SERVER_TIMEOUT =  0;

    /** @var Redis Redis storage */
    protected $redis;

    /**
     * Constructor
     *
     * @param array $options
     * @throws \Exception
     */
    public function __construct($options = array())
    {
        if (!extension_loaded('redis')) {
            throw new \Exception(
                'The redis extension must be loaded for using this model !'
            );
        }
        $redis = new Redis;
        $status = $redis->connect(
            isset($options['host']) ? $options['host'] : static::SERVER_HOST,
            isset($options['port']) ? $options['port'] : static::SERVER_PORT,
            isset($options['timeout'])
                ? $options['timeout'] : static::SERVER_TIMEOUT
        );
        if (!$status) {
            throw new \Exception('The redis server connection failed.');
        }
        // use igBinary serialize/unserialize
        $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
        $this->redis = $redis;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return 'redis';
    }

    /**
     * {@inheritDoc}
     */
    public function getEngine()
    {
        return $this->redis;
    }

    /**
     * {@inheritDoc}
     */
    public function load($id)
    {
        $id = $this->prefix($id);
        $data = $this->redis->get($id);

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function save($data, $id, $ttl = 0)
    {
        $id = $this->prefix($id);
        $this->redis->sadd($this->namespace, $id);
        if ($ttl) {
            $result = $this->redis->setex($id, $data, $ttl);
        } else {
            $result = $this->redis->set($id, $data);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function remove($id)
    {
        $id = $this->prefix($id);
        
        return $this->redis->delete($id);
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        $members = $this->redis->sMembers($this->namespace);
        $multi = $this->redis->multi();
        foreach ($members as $id) {
            $multi->delete($id);
            $this->redis->sRem($this->namespace, $id);
        }
        $multi->exec();

        return true;
    }
}
