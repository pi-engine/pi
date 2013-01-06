<?php
/**
 * Create ViewModel listener
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
 * @subpackage      View
 * @version         $Id$
 */

namespace Pi\Mvc\View\Http;

use Zend\EventManager\EventManagerInterface as Events;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class CreateViewModelListener implements ListenerAggregateInterface
{
    protected $type;

    /**
     * Listeners we've registered
     *
     * @var array
     */
    protected $listeners = array();

    /**
     * Attach listeners
     *
     * @param  Events $events
     * @return void
     */
    public function attach(Events $events)
    {
        $this->listeners[] = $events->attach('dispatch', array($this, 'createViewModelFromArray'),  -80);
        $this->listeners[] = $events->attach('dispatch', array($this, 'createViewModelFromNull'),   -80);
    }

    /**
     * Detach listeners
     *
     * @param  Events $events
     * @return void
     */
    public function detach(Events $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * Inspect the result, and cast it to a ViewModel if an assoc array is detected
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function createViewModelFromArray(MvcEvent $e)
    {
        $result = $e->getResult();
        if (!is_array($result)) {
            return;
        }
        /*
        if (!ArrayUtils::hasStringKeys($result, true)) {
            return;
        }
        */

        switch ($this->type) {
            case 'feed':
                $model = new FeedModel($result);
                break;
            case 'json':
                $model = new JsonModel($result);
                break;
            case 'ajax':
                $model = new ViewModel;
                $model->setVariable('content', json_encode($result));
                $model->setTerminal(true);
                break;
            default:
                if (!ArrayUtils::hasStringKeys($result, true)) {
                    return;
                }
                $model = new ViewModel($result);
                break;
        }
        //$model = new ViewModel($result);
        $e->setResult($model);
    }

    /**
     * Inspect the result, and cast it to a ViewModel if null is detected
     *
     * @param MvcEvent $e
     * @return void
    */
    public function createViewModelFromNull(MvcEvent $e)
    {
        $result = $e->getResult();
        if (null !== $result) {
            return;
        }

        $model = new ViewModel;
        $e->setResult($model);
    }

    public function setType($type)
    {
        $this->type = $type;
    }
}
