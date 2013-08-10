<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Mvc\Controller;

use Pi;
use Pi\Feed\Model as DataModel;

/**
 * Basic feed controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
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
                'link'      => Pi::url(
                                $this->url('feed', $routeMatch->getParams()),
                                true
                               ),
                'type'      => $feedType,
            );
        }

        $model = new DataModel($data);

        return $model;
    }
}
