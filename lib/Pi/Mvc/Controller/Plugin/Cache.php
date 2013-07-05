<?php
/**
 * Controller plugin cache class
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
 * @since           3.0
 * @package         Pi\Mvc
 * @version         $Id$
 */

namespace Pi\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Cache\Storage\Adapter\AbstractAdapter as CacheAdapter;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\InjectApplicationEventInterface;

class Cache extends AbstractPlugin
{
    /**
     * @var CacheAdapter
     */
    protected $cache;

    /**
     * Invoke as a functor
     * @return CacheAdapter
     */
    public function __invoke()
    {
        if (!$this->cache) {
            $this->cache = clone Pi::service('cache')->storage();
            $this->cache->getOptions()->setNamespace($this->getEvent()->getRouteMatch()->getParam('module') . 'action');
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
            throw new \DomainException('Cache plugin requires a controller that implements InjectApplicationEventInterface');
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
