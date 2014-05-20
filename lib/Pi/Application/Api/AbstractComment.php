<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Api;

use Zend\Mvc\Router\RouteMatch;

/**
 * Abstract class for module comment callback
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractComment extends AbstractApi
{
    /**
     * Get target data of item(s)
     *
     * - Fetch data of an item:
     *   - title
     *   - url
     *   - time
     *   - uid
     *
     * @param int|int[] $item
     *
     * @throws \Exception
     * @return array|bool
     */
    public function get($item)
    {
        throw new \Exception('Method is not defined.');
    }

    /**
     * Locate source id via route
     *
     * @param RouteMatch|array $params
     *
     * @throws \Exception
     * @return mixed|bool
     */
    public function locate($params = null)
    {
        throw new \Exception('Method is not defined.');
    }
}
