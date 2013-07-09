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
 * @package         Pi\Mvc
 * @subpackage      Controller
 */

namespace Pi\Mvc\Controller;

use Pi;
use Pi\Feed\Model as DataModel;

/**
 * Basic feed controller
 */
abstract class FeedController extends ActionController
{
    /**
     * Get feed data model
     *
     * @param array $data
     * @return DataModel;
     */
    public function getDataModel(array $data = array())
    {
        if (!isset($data['feed_link'])) {
            $e = $this->getEvent();
            $routeMatch = $e->getRouteMatch();
            $feedType = $routeMatch->getParam('type', 'rss');
            $data['feed_link'] = array(
                'link'      => Pi::url($this->url('feed', $routeMatch->getParams()), true),
                'type'      => $feedType,
            );
        }

        $model = new DataModel($data);
        return $model;
    }
}
