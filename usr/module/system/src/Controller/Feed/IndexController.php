<?php
/**
 * Feed controller class
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
 * @package         Module\System
 * @subpackage      Controller
 * @version         $Id$
 */

namespace Module\System\Controller\Feed;
use Pi\Mvc\Controller\FeedController;
use Pi;

/**
 * Index action controller
 */
class IndexController extends FeedController
{
    /**
     * Create feeds for recent module updates
     *
     * @return array
     */
    public function indexAction()
    {
        $feed = array(
            'title'         => __('What\'s new'),
            'description'   => __('Recent module updates.'),
            'date_created'  => time(),
        );
        $model = $this->getModel('update');
        $select = $model->select()->order('time DESC')->limit(10);
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $entry = array();
            $entry['title'] = $row->title;
            $entry['description'] = $row->content;
            $entry['date_modified'] = (int) $row->time;
            $entry['link'] = $this->getHref($row);
            $feed['entries'][] = $entry;
        }
        return $feed;
    }

    protected function getHref($row)
    {
        $uri = $row->uri ?:
            $this->url(
                $row->route ? $row->route : 'default',
                array(
                    'module'        => $row->module,
                    'controller'    => $row->controller,
                    'action'        => $row->action,
                    'params'        => empty($row->params) ? array() : parse_str($row->params)
                )
            );

        return Pi::url($uri, true);
    }
}
