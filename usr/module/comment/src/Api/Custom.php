<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Comment\Api;

use Pi;
use Pi\Application\AbstractComment;
use Zend\Mvc\Router\RouteMatch;

/**
 * Custom comment target callback handler and locator
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Custom extends AbstractComment
{
    /** @var string */
    protected $module = 'comment';

    /**
     * Get target data
     *
     * @param int|int[] $item Item id(s)
     *
     * @return array
     */
    public function get($item)
    {
        $result = array();
        $items = (array) $item;

        foreach ($items as $id) {
            $result[$id] = array(
                'id'    => $id,
                'title' => sprintf(__('Custom article %d'), $id),
                'url'   => Pi::service('url')->assemble(
                    'comment',
                    array(
                        'module'        => 'comment',
                        'controller'    => 'custom',
                        'id'            => $id,
                        'custom'        => 'yes',
                    )
                ),
                'uid'   => rand(1, 5),
                'time'  => time(),
            );
        }

        if (is_scalar($item)) {
            $result = $result[$item];
        }

        return $result;
    }

    /**
     * Locate source id via route
     *
     * @param RouteMatch|array $params
     *
     * @return mixed|bool
     */
    public function locate($params = null)
    {
        if (null == $params) {
            $params = Pi::engine()->application()->getRouteMatch();
        }
        if ($params instanceof RouteMatch) {
            $params = $params->getParams();
        }
        if ('comment' == $params['module']
            && 'custom' == $params['controller']
            && !empty($params['custom'])
        ) {
            $item = $params['id'];
        } else {
            $item = false;
        }

        return $item;
    }
}
