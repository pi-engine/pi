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
    public function insertAction()
    {
        $result   = array(
            'status' => 0,
            'message' => 'Add timeline fail',
        );

        $uid      = (int) _post('uid');
        $timeline = _post('timeline');
        $title    = _post('title');
        $message  = _post('message');
        $time     = (int) _post('time');
        $link     = _post('link');
        $app_key  = _post('app_key');
        $module   = _post('module');

        if (!$uid || !$timeline || !$message ) {
            return $result;
        }

        // Check timeline

        $rowset = $this->getModel('timeline')->find($timeline, 'name');
        if (!$rowset && !$app_key) {
            return $result;
        }
        $result['t'] = $_POST;

        if (!$rowset && $app_key) {

            $data = array(
                'name'    => $timeline,
                'module'  => $module,
                'title'   => $title,
                'app_key' => $app_key,
            );

            // Insert timeline meta
            $row = $this->getModel('timeline')->createRow($data);
            $row->save();
            if (!$row) {
                return $result;
            }
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