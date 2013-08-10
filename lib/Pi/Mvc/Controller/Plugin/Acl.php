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
use Pi\Acl\Acl as AclManager;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\InjectApplicationEventInterface;

/**
 * ACL plugin for controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Acl extends AbstractPlugin
{
    /** @var AclManager ACL handler */
    protected $aclManager;

    /**
     * Invoke as a functor
     *
     * @return aclManager
     */
    public function __invoke()
    {
        if (!$this->aclManager) {
            $this->aclManager = new AclManager;
            $this->aclManager->setSection('module')->setModule(
                $this->getController()->getModule()
            );
        }

        return $this->aclManager;
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
                'ACL plugin requires a controller that implements'
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
