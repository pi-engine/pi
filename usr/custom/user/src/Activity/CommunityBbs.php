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

class CommunityBbs
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


        $data = json_decode(Pi::service('remote')->get($uri, $params), true);

        //$data['topics']

        //$data

        $data['topics'] = array(
            array(
                'title'         => 'title1',
                'url'           => 'url1',
                'community'     =>  'community1',
                'communityUrl'  =>  'communityUrl1',
                'time'          =>  '2013-2'
            ),
            array(
                'title'         => 'title2',
                'url'           => 'url2',
                'community'     =>  'community2',
                'communityUrl'  =>  'communityUrl2',
                'time'          =>  '2013-2'
            ),
        );

        $data['replies'] = array(
            array(
                'title'         => 'title11',
                'url'           => 'url11',
                'community'     =>  'community11',
                'communityUrl'  =>  'communityUrl11',
                'time'          =>  '2013-2'
            ),
            array(
                'title'         => 'title22',
                'url'           => 'url22',
                'community'     =>  'community22',
                'communityUrl'  =>  'communityUrl22',
                'time'          =>  '2013-2'
            ),
        );

        $data['questions'] = array();
        $data['answers'] = array();
        $data['projects'] = array();


        return $data;
    }
}