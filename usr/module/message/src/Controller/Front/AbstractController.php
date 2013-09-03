<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Message\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Zend\Mvc\MvcEvent;

/**
 * Basic message action controller
 *
 * @author Xingyu Ji <xingyu@eefocus.com>
 */
abstract class AbstractController extends ActionController
{
    /**
     * Execute the request
     *
     * @param  MvcEvent $e
     * @return mixed
     * @throws \DomainException
     */
    public function onDispatch(MvcEvent $e)
    {
        if (!Pi::user()->hasIdentity()) {
            $this->redirect('', array(
                'module'     => 'system',
                'controller' => 'login',
                'action'     => 'index',
            ));
            return;
        }

        return parent::onDispatch($e);
    }
}
