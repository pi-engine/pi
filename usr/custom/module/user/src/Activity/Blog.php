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

        $uri = Pi::url(Pi::service('url')->assemble(
            'default',
            array(
                'module'     => 'demo',
                'controller' => 'activity',
                'action'     => 'get'
            )
        ), true);

        $uri = 'http://www.eefocus.com/passport/api.php';

        $params = array(
            'uid' => $uid,
            'act' => 'basic'
        );

        $data = Pi::service('remote')->get($uri, $params);
        
        $data = array(
            'items'  =>  array(
                array(
                    'message' => 'message1',
                    'time'    => time()
                ),
                array(
                    'message' => 'message2',
                    'time'    => time()
                )
            ),
            'link'   =>  'test.com'
        );
        return $data;
    }
}