<?php
/**
 * Pi cache registry
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
 * @package         Pi\Application
 * @subpackage      Registry
 * @version         $Id$
 */

namespace Pi\Application\Registry;
use Pi;

class Event extends AbstractRegistry
{
    /**
     * load event data from module config
     *
     * A module event configuration file (events in app/press/config/event.ini.php):
     * event[] = article_post
     * event[] = article_delete
     * event[] = article_rate
     *
     * Trigger in app/press/controller/ArticleController.php
     * \Pi::service('event')->trigger('press_article_post', $articleObject);
     *
     * Callback configurations in apps/user/config/event.ini.php
     * observer.press.article_post[] = stats::article
     *
     * Callback calss in app/user/class/stats.php
     * class User_Stats
     * {
     *      public static function article($articleObject) { ... }
     * }
     */
    protected function loadDynamic($options)
    {
        $listeners = array();
        $modelEvent = Pi::model('event');
        $rowset = $modelEvent->select(array(
            'module'    => $options['module'],
            'name'      => $options['event'],
            'active'    => 1
        ));
        if ($rowset->count()) {
            return $listeners;
        }

        $modelListener = Pi::model('event_listener');
        $select = $modelListener->select()->where(array(
            'event_module'  => $options['module'],
            'event_name'    => $options['event'],
            'active'        => 1
        ));
        $listenerList = $modelListener->selectWith($select);
        $directory = Pi::service('module')->directory($options['module']);
        foreach ($listenerList as $row) {
            $class = sprintf('Module\\%s\\%s', ucfirst($directory), ucfirst($class));
            $listeners[] = array($class, $row->method, $row->module);
        }

        return $listeners;
    }

    public function read($module, $event)
    {
        if (empty($event)) return false;
        $options = compact('module', 'event');
        return $this->loadData($options);
    }

    /**
     * Add a module event
     */
    public function create($module, $event = null)
    {
        $this->clear($module);
        $this->read($module, $event);
        return true;
    }
}
