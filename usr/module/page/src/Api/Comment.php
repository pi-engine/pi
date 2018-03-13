<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Page\Api;

use Pi;
use Pi\Application\Api\AbstractComment;

/**
 * Comment target callback handler
 */
class Comment extends AbstractComment
{
    /**
     * {@inheritDoc}
     */
    protected $table = 'page';

    /**
     * {@inheritDoc}
     */
    protected $meta
        = [
            'id'           => 'id',
            'title'        => 'title',
            'time_created' => 'time',
            'user'         => 'uid',
        ];

    /**
     * {@inheritDoc}
     */
    public function locate($params = null)
    {
        if (null == $params) {
            $params = Pi::engine()->application()->getRouteMatch();
        }
        if ($params instanceof RouteMatch) {
            $params = $params->getParams();
        }
        if ($this->module == $params['module']
            && 'index' == $params['controller']
            && !empty($params['id'])
        ) {
            $item = $params['id'];
        } else {
            $item = false;
        }

        return $item;
    }

    /**
     * {@inheritDoc}
     */
    protected function buildUrl(array $item)
    {
        $url = Pi::api('api', $this->module)->url($item['id']);

        return $url;
    }
}
