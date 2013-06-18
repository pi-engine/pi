<?php
/**
 * Feed View Strategy Listener
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
 * @package         Pi\Mvc
 * @subpackage      View
 */

namespace Pi\Mvc\View\Http;

use Pi;
use Zend\EventManager\EventManagerInterface as Events;
use Zend\Mvc\MvcEvent;

class FeedStrategyListener extends ViewStrategyListener
{
    /**
     * {@inheritDoc}
     */
    protected $type = 'feed';

    /**
     * {@inheritDoc}
     */
    public function attach(Events $events)
    {
        $sharedEvents = $events->getSharedManager();

        // Detect request type and disable debug in case necessary
        $this->listeners[] = $events->attach(MvcEvent::EVENT_BOOTSTRAP, array($this, 'prepareRequestType'),  99999);

        // Prepare root ViewModel for MvcEvent
        // Must be triggered before ViewManager
        $this->listeners[] = $events->attach(MvcEvent::EVENT_BOOTSTRAP, array($this, 'prepareRootModel'), 20000);

        // Canonize ViewModel for action
        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($this, 'canonizeActionResult'), -70);

        // Inject ViewModel, should be performed before injectTemplateListener
        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($this, 'injectTemplate'), -85);

        // Set theme layout if necessary
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'setThemeLayout'), 1000);
    }

}
