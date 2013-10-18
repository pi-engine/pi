<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Privacy controller
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class PrivacyController extends ActionController
{
    /**
     * Display user privacy
     * If not set, display default privacy options
     *
     * @return array|void
     */
    public function indexAction()
    {

        // Redirect login page if not logged in
        $uid = Pi::user()->getIdentity();
        if (!$uid) {
            $this->jump(
                'user',
                array('controller' => 'login', 'action' => 'index'),
                __('Need login'),
                2
            );
        }

        $privacy = Pi::api('user', 'privacy')->getUserPrivacy($uid, 'list');

        return $privacy;
    }

    /**
     * Set user privacy
     *
     * @return array
     */
    public function setPrivacyAction()
    {
        $uid   = (int) _post('uid');
        $field = _post('field');
        $value = (int) _post('value');

        $result = array(
            'status'  => 0,
            'message' => __('Set privacy failed'),
        );

        // Check post data
        if (!$uid) {
           return $result;
        }
        if (!in_array($value, array(0, 1, 2, 4, 255))) {
            return $result;
        }
        $row = $this->getModel('privacy')->find($field, 'field');
        // Field is not exist
        if (!$row) {
            return $result;
        }
        // Field is not allow setting
        if (!$row->is_forced) {
            return $result;
        }
        $row = $this->getModel('privacy_user')->find($uid, 'uid');
        if (!$row) {
            return $result;
        }

        // Update user privacy setting
        try {
            $this->getModel('privacy_user')->update(
                array(
                    'value' => $value,
                ),
                array(
                    'uid'       => $uid,
                    'field'     => $field,
                    'is_forced' => 1,
                )
            );
            $result['status']  = 1;
            $result['message'] = __('Set privacy successfully');
        } catch (\Exception $e) {
            return $result;
        }

        return $result;

    }
}