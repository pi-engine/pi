<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Pi\Session\SessionManager;
use Zend\Session\Container;

/**
 * Session service
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Session extends AbstractService
{
    /** {@inheritDoc} */
    protected $fileIdentifier = 'session';

    /**
     * Session Manager
     *
     * @var SessionManager
     */
    protected $manager;

    /**
     * Callback on shutdown
     *
     * @return void
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
     *
     * @return SessionManager
     */
    public function manager()
    {
        if (!$this->manager) {
            $options = $this->options;
            $sessionConfig = null;
            if (!empty($options['config'])
                && !empty($options['config']['class'])
            ) {
                $class  = $options['config']['class'];
                $sessionConfig = new $class;
                if (isset($options['config']['options'])) {
                    if (!isset($options['config']['options']['cookie_path'])
                        && $baseUrl = Pi::host()->get('baseUrl')
                    ) {
                        $options['config']['options']['cookie_path'] =
                            rtrim($baseUrl, '/') . '/';
                    }
                    $sessionConfig->setOptions($options['config']['options']);
                }
            }
            $sessionStorage = null;
            if (!empty($options['storage'])
                && !empty($options['storage']['class'])
            ) {
                $class  = $options['storage']['class'];
                $input  = isset($options['storage']['input'])
                    ? $options['storage']['input'] : null;
                $sessionStorage = new $class($input);
            }
            $saveHandler = null;
            if (!empty($options['save_handler'])
                && !empty($options['save_handler']['class'])
            ) {
                $class  = $options['save_handler']['class'];
                $opts = isset($options['save_handler']['options'])
                    ? $options['save_handler']['options'] : array();
                $saveHandler = new $class($opts);
            }
            $this->manager = new SessionManager(
                $sessionConfig,
                $sessionStorage,
                $saveHandler
            );

            if (!empty($options['config'])
                && !empty($options['config']['validators'])
            ) {
                $this->manager->setValidators(
                    $options['config']['validators']
                );
            }

            // Set default session manager in case Zend\Session called directly
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
     * Clear expired containers
     *
     * @return void
     */
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
            if ((isset($metadata['EXPIRE'])
                && $_SERVER['REQUEST_TIME'] > $metadata['EXPIRE'])
                || (isset($metadata['EXPIRE_HOPS'])
                    && $ts > $metadata['EXPIRE_HOPS']['ts']
                    && 0 >= $metadata['EXPIRE_HOPS']['hops']
                   )
            ) {
                $storage->clear($name);
            }
        }
    }

    /**
     * Magic method to get session container
     *
     * @param string $name
     * @return Container
     */
    public function __get($name)
    {
        return $this->container($name);
    }

    /**
     * Magic method to proxy calls to session manager
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->manager(), $method), $args);
    }
}
