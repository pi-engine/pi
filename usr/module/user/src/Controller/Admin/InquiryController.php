<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * User manage cases controller
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class InquiryController extends ActionController
{
    /**
     * Default action
     * @return array|void
     */
    public function indexAction()
    {
        $this->view()->setTemplate('inquiry');
    }


    /**
     * Search user info buy display name
     *
     * @return array
     */
    public function profileAction()
    {
        $field = _get('field') ?: 'name';
        $data  = _get('data');
        $user  = Pi::service('user')->getUser($data, $field);

        if (!$user) {
            $this->response->setStatusCode(404);
            return [
                'message' => 'User not found',
            ];
        }

        $uid     = $user->get('id');
        $profile = $this->getProfileGroup($uid);
        $user    = Pi::api('user', 'user')->get(
            $uid,
            [
                'identity',
                'name',
                'email',
                'time_activated',
                'time_disabled',
            ]
        );

        $user['time_activated'] = $user['time_activated']
            ? _date($user['time_activated']) : 0;
        $user['time_disabled']  = $user['time_disabled']
            ? _date($user['time_disabled']) : 0;

        $user['link']   = $this->url(
            'user',
            [
                'controller' => 'home',
                'action'     => 'view',
                'uid'        => $uid,
            ]
        );
        $user['avatar'] = Pi::user()->avatar()->get($uid, 'large', false);

        return [
            'user'   => $user,
            'groups' => array_values($profile),
        ];
    }

    /**
     * Get user profile information
     * Group and group items title and value
     *
     * @param int $uid User id
     *
     * @return array
     */
    protected function getProfileGroup($uid)
    {
        $result = [];

        // Get account or profile meta
        $fieldMeta = Pi::api('user', 'user')->getMeta('', 'display');
        $groups    = $this->getDisplayGroup();

        foreach ($groups as $groupId => $group) {
            $result[$groupId]           = $group;
            $result[$groupId]['fields'] = [];
            $fields                     = $this->getFieldDisplay($groupId);

            if ($group['compound']) {
                // Compound meta
                $compoundMeta = Pi::registry('compound_field', 'user')->read(
                    $group['compound']
                );

                // Compound value
                $compound = Pi::api('user', 'user')->get(
                    $uid,
                    $group['compound']
                );
                // Generate Result
                foreach ($compound as $set => $item) {
                    // Compound value
                    $compoundValue = [];
                    foreach ($fields as $field) {
                        $compoundValue[] = [
                            'title' => $compoundMeta[$field]['title'],
                            'value' => $item[$field],
                        ];
                    }
                    $result[$groupId]['fields'][$set] = $compoundValue;
                }
            } else {
                // Profile
                foreach ($fields as $field) {
                    $result[$groupId]['fields'][0][$field] = [
                        'title' => $fieldMeta[$field]['title'],
                        'value' => Pi::api('user', 'user')->get($uid, $field),
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Get Administrator custom display group
     *
     * @return array
     */
    protected function getDisplayGroup()
    {
        $result = [];

        $model  = $this->getModel('display_group');
        $select = $model->select();
        $select->order('order ASC');
        $groups = $model->selectWith($select);

        foreach ($groups as $group) {
            $result[$group->id] = $group->toArray();
        }

        return $result;
    }

    /**
     * Get field display
     *
     * @param int $groupId
     *
     * @return array
     */
    protected function getFieldDisplay($groupId)
    {
        $result = [];

        $model  = $this->getModel('display_field');
        $select = $model->select()->where(['group' => $groupId]);
        $select->columns(['field', 'order']);
        $select->order('order ASC');
        $fields = $model->selectWith($select);

        foreach ($fields as $field) {
            $result[] = $field->field;
        }

        return $result;
    }
}
