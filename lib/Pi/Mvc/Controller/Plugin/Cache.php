<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Cache\Storage\Adapter\AbstractAdapter as CacheAdapter;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\InjectApplicationEventInterface;

/**
 * Cache plugin for controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Cache extends AbstractPlugin
{
    /** @var CacheAdapter Cache storage */
    protected $cache;

    /**
     * Invoke as a functor
     *
     * @return CacheAdapter
     */
    public function __invoke()
    {
        if (!$this->cache) {
            $this->cache = clone Pi::service('cache')->storage();
            $this->cache->getOptions()->setNamespace(
                $this->getEvent()->getRouteMatch()->getParam('module')
                . 'action'
            );
        }

        return $this->cache;
    }

    /**
     * Get the event
     *
     * @return MvcEvent
     * @throws \DomainException if unable to find event
     */
    protected function getEvent()
    {
        if ($this->event) {
            return $this->event;
        }

        $controller = $this->getController();
        if (!$controller instanceof InjectApplicationEventInterface) {
            throw new \DomainException(
                'Cache plugin requires a controller that implements'
                . ' InjectApplicationEventInterface'
            );
        }

        $event = $controller->getEvent();
        if (!$event instanceof MvcEvent) {
            $params = $event->getParams();
            $event  = new MvcEvent();
            $event->setParams($params);
        }
        $this->event = $event;

        return $this->event;
    }
}
