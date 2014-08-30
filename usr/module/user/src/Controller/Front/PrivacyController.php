<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
        Pi::api('profile', 'user')->requireComplete();
        $uid = Pi::user()->getId();

        $fields = Pi::registry('field', 'user')->read('', 'display');
        $forcedPrivacy = Pi::registry('privacy', 'user')->read(true);
        if ($this->request->isPost()) {
            $privacySettings = $this->request->getPost()->toArray();
            $model = $this->getModel('privacy_user');
            foreach ($privacySettings as $field => $value) {
                if (!isset($fields[$field]) || isset($forcedPrivacy[$field])) {
                    continue;
                }
                $row = $model->select(array(
                    'uid'       => $uid,
                    'field'     => $field,
                ))->current();
                if ($row) {
                    $row['value'] = $value;
                } else {
                    $row = $model->createRow(array(
                        'uid'       => $uid,
                        'field'     => $field,
                    ));
                }
                $row->save();
            }

            Pi::service('event')->trigger('user_update', $uid);
            $this->jump(
                array('action' => 'index'),
                __('Privacy settings saved successfully.'),
                'success'
            );

            return;
        }

        $userPrivacy = Pi::api('privacy', 'user')->getUserPrivacy($uid);
        $privacy = array();
        foreach ($fields as $field => $data) {
            $pv = array(
                'field'     => $field,
                'title'     => $data['title'],
                'value'     => 0,
                'is_forced' => 0,
            );
            if (isset($userPrivacy[$field])) {
                $pv['value'] = $userPrivacy[$field];
            }
            if (isset($forcedPrivacy[$field])) {
                $pv['is_forced'] = 1;
            }
            $privacy[] = $pv;
        }

        $levels = Pi::api('privacy', 'user')->getList(
            array(),
            true
        );
        //$user = Pi::api('user', 'user')->get($uid, array('uid', 'name'));
        // Get side nav items
        //$groups = Pi::api('group', 'user')->getList();
        $this->view()->assign(array(
            'privacy' => $privacy,
            //'groups'  => $groups,
            'levels'  => $levels,
            //'user'    => $user,
        ));

        $this->view()->headTitle(__('Privacy Settings'));
        $this->view()->headdescription(__('Set profile field privacy.'), 'set');
        $this->view()->headkeywords($this->config('head_keywords'), 'set');
    }
}