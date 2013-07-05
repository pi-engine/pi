<?php
/**
 * Session service
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 * @subpackage      Service
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Service;

use Pi;
use Pi\Session\SessionManager;
use Zend\Session\Container;

class Session extends AbstractService
{
    protected $fileIdentifier = 'session';
    /**
     * Session Manager
     * @var SessionManager
     */
    protected $manager;

    /**
     * Callback on shutdown
     */
    public function shutdown()
    {
        // Clear expired items by a probability
        $clearProbability = $this->options['clear_probability'];
        if (rand(1, 100) <= $clearProbability) {
            $this->clearExpirations();
        }

        // session_write_close
        $this->manager()->writeClose();
    }

    /**
     * Load session manager
     * @return SessionManager
     */
    public function manager()
    {
        if (!$this->manager) {
            $options = $this->options;
            $sessionConfig = null;
            if (!empty($options['config']) && !empty($options['config']['class'])) {
                $class  = $options['config']['class'];
                $sessionConfig = new $class;
                if (isset($options['config']['options'])) {
                    if (!isset($options['config']['options']['cookie_path']) && $baseUrl = Pi::host()->get('baseUrl')) {
                        $options['config']['options']['cookie_path'] = rtrim($baseUrl, '/') . '/';
                    }
                    $sessionConfig->setOptions($options['config']['options']);
                }
            }
            $sessionStorage = null;
            if (!empty($options['storage']) && !empty($options['storage']['class'])) {
                $class  = $options['storage']['class'];
                $input  = isset($options['storage']['input']) ? $options['storage']['input'] : null;
                $sessionStorage = new $class($input);
            }
            $saveHandler = null;
            if (!empty($options['save_handler']) && !empty($options['save_handler']['class'])) {
                $class  = $options['save_handler']['class'];
                $opts = isset($options['storage']['options']) ? $options['storage']['options'] : array();
                $saveHandler = new $class($opts);
            }
            $this->manager = new SessionManager($sessionConfig, $sessionStorage, $saveHandler);

            if (!empty($options['config']) && !empty($options['config']['validators'])) {
                $this->manager->setValidators($options['config']['validators']);
            }

            // Set default session manager in case Zend\Session is called directly
            Container::setDefaultManager($this->manager);
        }

        return $this->manager;
    }

    /**
     * Get session container
     *
     * @param string $name
     * @return Container
     */
    public function container($name = 'PI')
    {
        $container = new Container($name, $this->manager());
        return $container;
    }

    /**
     * Get container
     *
     * @return Container
     */
    public function __get($name)
    {
        return $this->container($name);
    }

    public function clearExpirations()
    {
        $storage = $this->manager()->getStorage();
        if ($storage->isImmutable()) {
            return;
        }
        $ts = $storage->getRequestAccessTime();
        $meta = (array) $storage->getMetadata();
        foreach ($meta as $name => $metadata) {
            if (!is_array($metadata)) {
                continue;
            }
            if ((isset($metadata['EXPIRE']) && $_SERVER['REQUEST_TIME'] > $metadata['EXPIRE'])
                || (isset($metadata['EXPIRE_HOPS']) && $ts > $metadata['EXPIRE_HOPS']['ts'] && 0 >= $metadata['EXPIRE_HOPS']['hops'])
            ) {
                $storage->clear($name);
            }
        }
    }
}
