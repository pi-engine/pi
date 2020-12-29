<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
     *
     * @return DataModel;
     */
    public function getDataModel(array $data = [])
    {
        if (!isset($data['feed_link'])) {
            $e                 = $this->getEvent();
            $routeMatch        = $e->getRouteMatch();
            $feedType          = $this->params('type', 'rss');
            $data['feed_link'] = [
                'link' => Pi::url(
                    $this->url('feed', $routeMatch->getParams()),
                    true
                ),
                'type' => $feedType,
            ];
        }

        $model = new DataModel($data);

        return $model;
    }
}
