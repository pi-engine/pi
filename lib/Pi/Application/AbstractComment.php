<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application;

//use Zend\Mvc\Router\RouteMatch;
/**
 * Abstract class for module comment callback
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractComment extends AbstractModuleAwareness
{
    /**
     * Get target data of item(s)
     *
     * - Target data of an item:
     *   - title
     *   - url
     *   - time
     *   - uid
     *
     * @param int|string|int[]|string[] $item
     *
     * @throws \exception
     * @return array|bool
     */
    public function get($item)
    {
        throw new \exception('Method is not defined.');
    }

    /**
     * Locate source id via route
     *
     * @param array $params
     *
     * @throws \exception
     * @return mixed|bool
     */
    public function locate(array $params)
    {
        throw new \exception('Method is not defined.');
    }
}
