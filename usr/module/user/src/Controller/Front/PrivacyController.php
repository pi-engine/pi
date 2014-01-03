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
        Pi::service('authentication')->requireLogin();
        $uid = Pi::user()->getId();
        // Check profile complete
        if ($this->config('profile_complete_form')) {
            $completeProfile = Pi::api('user', 'user')->get($uid, 'level');
            if (!$completeProfile) {
                $this->redirect()->toRoute(
                    'user',
                    array(
                        'controller' => 'register',
                        'action' => 'profile.complete',
                    )
                );
                return;
            }
        }

        if ($this->request->isPost()) {
            $privacySettings = $this->request->getPost()->toArray();
            foreach ($privacySettings as $key => $value) {
                $this->getModel('privacy_user')->update(
                    array(
                        'value' => $value,
                    ),
                    array(
                        'uid'       => $uid,
                        'field'     => $key,
                        'is_forced' => 1,
                    )
                );
            }

            Pi::service('event')->trigger('user_update', $uid);
            $result = array(
                'status'  => 1,
                'message' => __('Privacy settings saved successfully.'),
            );
            $this->view()->assign('result', $result);
        }

        $privacy = Pi::api('user', 'privacy')->getUserPrivacyList($uid);
        foreach ($privacy as $key => &$value) {
            if (!$value['is_forced']) {
                unset($privacy[$key]);
            }
        }

        $levels = Pi::api('user', 'privacy')->getList(
            array('everyone', 'member', 'owner'),
            true
        );
        $user = Pi::api('user', 'user')->get($uid, array('uid', 'name'));
        // Get side nav items
        $groups = Pi::api('user', 'group')->getList();
        $this->view()->assign(array(
            'privacy' => $privacy,
            'groups'  => $groups,
            'levels'  => $levels,
            'user'    => $user,
        ));
    }
}