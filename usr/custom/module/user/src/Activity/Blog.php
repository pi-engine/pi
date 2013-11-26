<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Custom\User\Activity;

use Pi;

class Blog
{
    protected $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function get($uid, $limit, $offset = 0)
    {

        $uri = 'http://www.eefocus.com/passport/api.php';

        $params = array(
            'uid' => $uid,
            'act' => 'blog'
        );

        $data = json_decode(Pi::service('remote')->get($uri, $params), true);
        $items = array();

        foreach ($data['list'] as $item) {
            $item['message'] = sprintf(
                '<a href="%s" target="_blank">%s</a>',
                $item['url'],
                $item['message']
            );

            $items[] = $item;
        }

        return array(
            'link'  => $data['home'],
            'items' => $items
        );
    }
}