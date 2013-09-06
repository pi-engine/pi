<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Api;

use Pi;
use Pi\Application\AbstractApi;

/**
 * User activity APIs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Activity extends AbstractApi
{
    /** @var string Module name */
    protected $module = 'user';

    /**
     * Get activity list sorted by display order
     *
     * @return array
     */
    public function getList()
    {
        $result = array();
        $list = Pi::registry('activity', 'user')->read();
        foreach ($list as $name => $activity) {
            $result[$name] = array(
                'title' => $activity['title'],
                'icon'  => $activity['icon'],
            );
        }

        return $result;
    }

    /**
     * Get meta data and message list of an activity
     *
     * Log array: time, message
     *
     * @param int    $uid
     * @param string    $name
     * @param int    $limit
     * @param int $offset
     *
     * @return array
     */
    public function get($uid, $name, $limit, $offset = 0)
    {
        $meta = Pi::registry('activity', 'user')->read($name);
        $reader = new $meta['callback']($meta['module']);

        $list = $reader->get($uid, $limit, $offset);
        $log = array();
        foreach ($list as $item) {
            if (is_string($item)) {
                $log[] = array(
                    'time'      => null,
                    'message'   => $item,
                );
            } else {
                $log[] = array(
                    'time'      => isset($item['time']) ? $item['time'] : null,
                    'message'   => $item['message'],
                );
            }
        }

        $result = array(
            'title'         => $meta['title'],
            'description'   => $meta['description'],
            'module'        => $meta['module'],
            'icon'          => $meta['icon'],
            'link'          => $meta['link'],
            'log'           => $log,
        );

        return $result;
    }
}
