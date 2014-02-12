<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Persist;

use APCIterator;

/**
 * APC persist storage
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ApcStorage extends AbstractStorage
{
    /**
     * Constructor
     *
     * @param array $options
     * @throws \Exception
     */
    public function __construct($options = array())
    {
        if (version_compare('3.1.6', phpversion('apc')) > 0) {
            throw new \Exception('Missing ext/apc >= 3.1.6');
        }

        $enabled = ini_get('apc.enabled');
        if (PHP_SAPI == 'cli') {
            $enabled = $enabled && (bool) ini_get('apc.enable_cli');
        }

        if (!$enabled) {
            throw new \Exception(
                'ext/apc is disabled - see "apc.enabled" and "apc.enable_cli"'
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return 'apc';
    }

    /**
     * {@inheritDoc}
     */
    public function load($id)
    {
        $id = $this->prefix($id);

        return apc_fetch($this->prefix($id));
    }

    /**
     * {@inheritDoc}
     */
    public function save($data, $id, $ttl = 0)
    {
        $id = $this->prefix($id);

        return apc_store($id, $data, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($id)
    {
        $id = $this->prefix($id);

        return apc_delete($id);
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        $regex  = '/^' . preg_quote($this->prefix(), '/') . '+/';

        return apc_delete(
            new APCIterator('user', $regex, 0, 1, \APC_LIST_ACTIVE)
        );
    }
}
