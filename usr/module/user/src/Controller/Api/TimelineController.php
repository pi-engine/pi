<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Api;

use Pi;
use Pi\Mvc\Controller\ApiController;

/**
 * Timeline webservice controller
 *
 * @author Liu Chuang <liuchuangww@gmail.com>
 */
class TimelineController extends ApiController
{
    public function indexAction()
    {
        return array('status' => 1);
        
    }

    /**
     * Write a timeline log
     *
     * Log array:
     *  - uid
     *  - message
     *  - timeline
     *  - module
     *  - link
     *  - time
     *  - app_key
     *
     * @return bool
     */
    public function insertAction()
    {
        $result   = array(
            'status' => 0,
            'message' => __('Timeline post failed.'),
        );

        $uid        = (int) _post('uid');
        $timeline   = _post('timeline');
        $title      = _post('title');
        $message    = _post('message');
        $time       = (int) _post('time') ?: time();
        $link       = _post('link');
        $appKey     = _post('app_key');
        $module     = _post('module');

        if (!$uid || !$timeline || !$message ) {
            return $result;
        }

        // Check timeline
        $rowset = $this->getModel('timeline')->find($timeline, 'name');
        if (!$rowset && !$appKey) {
            return $result;
        // Register timeline meta if not exist
        } elseif (!$rowset && $appKey) {
            $data = array(
                'name'      => $timeline,
                'module'    => $module,
                'title'     => $title ?: $timeline,
                'app_key'   => $appKey,
                'active'    => 1,
            );

            // Insert timeline meta
            $row = $this->getModel('timeline')->createRow($data);
            $row->save();
            if (!$row->id) {
                return $result;
            }
        }

        // Add timeline log
        $log = compact('uid', 'timeline', 'message', 'time', 'link');
        $stauts = Pi::api('timeline', 'user')->add($log);
        if ($stauts) {
            $result['status']  = 1;
            $result['message'] = __('Timeline added successfully.');
        }

        return $result;
    }
}