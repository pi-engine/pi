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
 * View user controller
 *
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
class ViewController extends ActionController
{
    public function indexAction()
    {
        // Get uid
        $uid = _get('uid');

        // Set meta
        $meta = Pi::registry('field', 'user')->read();

        // Get user
        $user                   = Pi::api('user', 'user')->get($uid, array_keys($meta));
        $user['avatar']         = Pi::user()->avatar()->get($uid, 'large', ['class' => 'rounded-circle']);
        $user['time_created']   = _date($user['time_created']);
        $user['time_activated'] = $user['time_activated'] ? _date($user['time_activated']) : '';
        $user['time_disabled']  = $user['time_disabled'] ? _date($user['time_disabled']) : '';
        $user['time_deleted']   = $user['time_deleted'] ? _date($user['time_deleted']) : '';
        $user['last_modified']  = $user['last_modified'] ? _date($user['last_modified']) : '';
        $user['editUrl'] = Pi::url($this->url('', [
            'controller' => 'edit',
            'action' => 'index',
            'uid' => $user['uid']
        ]));

        // Get user role
        $user['roleSystem'] = Pi::registry('role')->read();
        $user['roleUserFront']   = Pi::user()->getRole($uid, 'front');
        $user['roleUserAdmin']   = Pi::user()->getRole($uid, 'admin');
        $user['roleList']   = [
            'front' => [],
            'admin' => [],
        ];
        foreach ($user['roleUserFront'] as $role) {
            $user['roleList']['front'][$role] = $user['roleSystem'][$role];
        }
        foreach ($user['roleUserAdmin'] as $role) {
            $user['roleList']['admin'][$role] = $user['roleSystem'][$role];
        }

        // ToDo : show order module logs
        if (Pi::service('module')->isActive('order')) {
        }

        // ToDo : show guide module logs
        if (Pi::service('module')->isActive('guide')) {
        }

        // ToDo : show shop module logs
        if (Pi::service('module')->isActive('shop')) {
        }

        // ToDo : show video module logs
        if (Pi::service('module')->isActive('video')) {
        }

        // ToDo : show vote module logs
        if (Pi::service('module')->isActive('vote')) {
        }

        // ToDo : show statistics module logs
        if (Pi::service('module')->isActive('statistics')) {
        }

        // Set view
        $this->view()->setTemplate('view-user');
        $this->view()->assign('user', $user);
        $this->view()->assign('meta', $meta);
        $this->view()->assign('nav', $this->getNav($uid));
        $this->view()->assign('name', 'view');
    }

    protected function getNav($uid)
    {
        $result[] = [
            'name'  => 'info',
            'title' => _a('Base info'),
            'link'  => $this->url('', ['controller' => 'edit', 'uid' => $uid]),
        ];

        // Password
        $result[] = [
            'name'  => 'password',
            'title' => _a('Password'),
            'link'  => $this->url(
                '',
                [
                    'controller' => 'edit',
                    'action'     => 'password',
                    'uid'        => $uid,
                ]
            ),
        ];

        // Two-Factor
        $result[] = [
            'name'  => 'twoFactor',
            'title' => _a('Two-Factor'),
            'link'  => $this->url(
                '',
                [
                    'controller' => 'edit',
                    'action'     => 'twoFactor',
                    'uid'        => $uid,
                ]
            ),
        ];

        // Avatar
        $result[] = [
            'name'  => 'avatar',
            'title' => _a('Avatar'),
            'link'  => $this->url('', [
                'controller' => 'edit',
                'action'     => 'avatar',
                'uid'        => $uid,
            ]),
        ];

        $rowset = $this->getModel('field')->select(
            [
                'type'       => 'compound',
                'is_display' => 1,
                'is_edit'    => 1,
                'active'     => 1,
            ]
        );

        foreach ($rowset as $row) {
            $result[] = [
                'name'  => $row['name'],
                'title' => $row['title'],
                'link'  => $this->url('', [
                    'controller' => 'edit',
                    'action'     => 'compound',
                    'uid'        => $uid,
                    'name'       => $row['name'],
                ]),
            ];
        }

        // Avatar
        $result[] = [
            'name'  => 'view',
            'title' => _a('View'),
            'link'  => $this->url('', [
                'controller' => 'view',
                'action'     => 'index',
                'uid'        => $uid,
            ]),
        ];

        return $result;
    }
}
