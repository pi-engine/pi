<?php
/**
 * Prepare FeedModel listener
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
use Pi\Feed\Model as DataModel;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface as Events;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Zend\View\Model\FeedModel;
use Zend\Stdlib\ArrayUtils;

class PrepareFeedModelListener extends AbstractListenerAggregate
{
    /**
     * {@inheritDoc}
     */
    public function attach(Events $events)
    {
        $sharedEvents = $events->getSharedManager();

        // Prepare ViewModel for MvcEvent
        // Must be triggered before ViewManager
        $this->listeners[] = $events->attach(MvcEvent::EVENT_BOOTSTRAP, array($this, 'prepareViewModel'), 20000);

        // Close debugger in case necessary
        //$this->listeners[] = $events->attach(MvcEvent::EVENT_BOOTSTRAP, array($this, 'prepareDebugMode'),  -70);

        // Canonize and inject ViewModel
        //$this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'canonizeViewModel'),  10000);
        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($this, 'canonizeViewModel'), -70);
    }

    public function prepareViewModel(MvcEvent $e)
    {
        $e->setViewModel(new FeedModel);
    }

    /**
     * Disable debug mode
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function prepareDebugMode(MvcEvent $e)
    {
        // Disable error debugging
        Pi::service('log')->debugger(false);
    }

    /**
     * Inspect the result, and cast it to a ViewModel if an assoc array is detected and inject it to MvcEvent
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function canonizeViewModel(MvcEvent $e)
    {
        $result = $e->getResult();

        //var_dump($result);
        $model = null;
        if ($result instanceof FeedModel) {
            $model = $result;
        } elseif ($result instanceof DataModel) {
            $model = new FeedModel((array) $result, array('feed_type' => $result->getType()));
        } elseif ($result instanceof ViewModel) {
            $model = new FeedModel($result->getVariables(), $result->getOptions());
        } elseif (ArrayUtils::hasStringKeys($result, true)) {
            $model = new FeedModel($result);
        } else {
            $model = new FeedModel;
        }

        // Inject ViewModel
        if ($model) {
            // Skip following result handling
            $e->setResult(false);

            // Inject ViewModel
            $e->setViewModel($model);
        }
    }
}
