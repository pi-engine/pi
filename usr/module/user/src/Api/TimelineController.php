<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Controller\Api;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Timeline webservice controller
 *
 * @author Liu Chuang <liuchuangww@gmail.com>
 */
class TimelineController extends ActionController
{
    public function indexAction()
    {
        return array('status' => 1);
        
    }

    /**
     * Write a timeline log
     *
     * Log array:
     *  - message
     *  - timeline
     *  - module
     *  - link
     *  - time
     *
     * @return bool
     */
    public function addAction()
    {
        $result   = array(
            'status' => 0,
            'message' => 'Add timeline fail',
        );

        $uid      = (int) _post('uid');
        $timeline = _post('timeline');
        $message  = _post('message');
        $time     = (int) _post('time');
        $link     = _post('link');

        if (!$uid || !$timeline || !$message ) {
            return $result;
        }

        // Check timeline
        $rowset = $this->getModel('timeline')->find($timeline, 'name');
        if (!$rowset) {
            return $result;
        }

        $log = array(
            'uid'      => $uid,
            'timeline' => $timeline,
            'message'  => $message,
        );
        if ($time) {
            $log['time'] = $time;
        }
        if ($link) {
            $log['link'] = $link;
        }

        // Add timeline
        $stauts = Pi::api('user', 'timeline')->add($log);
        if ($stauts) {
            $result['status']  = 1;
            $result['message'] = 'Add timeline successfully';
        }

        return $result;

    }
}