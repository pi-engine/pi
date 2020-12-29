<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Admin;

use Module\User\Form\CompoundFilter;
use Module\User\Form\CompoundForm;
use Module\User\Form\EditUserFilter;
use Module\User\Form\EditUserForm;
use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Edit user controller
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class EditController extends ActionController
{
    public function indexAction()
    {
        $uid = _get('uid');

        // Get user basic information and user data
        $user = $this->getUser($uid);

        // Get available edit fields
        list($fields, $formFields, $formFilters) = $this->getEditField();

        // Add other elements
        $formFields[] = [
            'name'       => 'uid',
            'type'       => 'hidden',
            'attributes' => [
                'value' => $uid,
            ],
        ];
        $formFields[] = [
            'name'       => 'id',
            'type'       => 'hidden',
            'attributes' => [
                'value' => $uid,
            ],
        ];

        // Set form
        $form = new EditUserForm('info', $formFields);
        if ($this->request->isPost()) {
            $form->setData($this->request->getPost());
            $form->setInputFilter(new EditUserFilter($formFilters));
            $result['message'] = _a('User data update failed.');
            $result['status']  = 0;
            if ($form->isValid()) {

                // Update user
                $values                  = $form->getData();
                $values['last_modified'] = time();
                if (isset($values['credential'])
                    && !$values['credential']
                ) {
                    unset($values['credential']);
                }
                unset($values['id']);

                // Check if email is empty
                if (isset($values['email']) && empty($values['email'])) {
                    $values['email'] = null;
                }

                $status = Pi::api('user', 'user')->updateUser($uid, $values);

                // Subscription
                if (Pi::service('module')->isActive('subscription') && isset($values['email']) && !empty($values['email'])) {
                    Pi::api('people', 'subscription')->update(
                        ['email'  => $values['email'], 'first_name' => $values['first_name'], 'last_name' => $values['last_name'],
                         'mobile' => $values['mobile']],
                        $uid
                    );
                }

                if ($status == 1) {
                    Pi::service('event')->trigger('user_update', $uid);
                    $result['message'] = _a('User data update successful.');
                    $result['status']  = 1;
                }
            }
            $this->view()->assign('result', $result);
        } else {
            $fieldsData = Pi::api('user', 'user')->get($uid, $fields);
            if ($fieldsData['credential']) {
                unset($fieldsData['credential']);
            }
            $form->setData($fieldsData);
        }

        $this->view()->assign(
            [
                'user' => $user,
                'nav'  => $this->getNav($uid),
                'name' => 'info',
                'form' => $form,
            ]
        );
        $this->view()->setTemplate('edit-user');
    }

    /**
     * Display user avatar and delete
     */
    public function avatarAction()
    {
        $uid = _get('uid');

        // Get user basic information and user data
        $user = $this->getUser($uid);

        if ($this->request->isPost()) {
            $oldAvatar = Pi::user()->get($uid, 'avatar');
            $adapter   = Pi::avatar()->getAdapter('upload');
            $oldPaths  = $adapter->getMeta($uid, $oldAvatar);
            foreach ($oldPaths as $oldPath) {
                $oldFile = dirname($oldPath['path']) . '/' . $oldAvatar;
                if (file_exists($oldFile)) {
                    @unlink($oldFile);
                }
            }
            // Delete user avatar
            $status = Pi::user()->set($uid, 'avatar', '');
            $result = [
                'status'  => 0,
                'message' => _a('User avatar change failed.'),
            ];
            if ($status) {
                $result = [
                    'status'  => 1,
                    'message' => _a('User avatar change successful.'),
                ];
                Pi::service('event')->trigger('user_update', $uid);
            }
            $this->view()->assign('result', $result);
        }


        $this->view()->assign(
            [
                'user'   => $user,
                'nav'    => $this->getNav($uid),
                'name'   => 'avatar',
                'avatar' => Pi::user()->avatar()->get($uid, 'large'),
            ]
        );
        $this->view()->setTemplate('edit-user');
    }

    /**
     * Edit user compound
     *
     * @return array
     */
    public function compoundAction()
    {
        $uid      = _get('uid');
        $compound = _get('name');

        // Get user basic information and user data
        $user = $this->getUser($uid);


        // Get compound elements and filters
        $compoundElements = Pi::api('form', 'user')->getCompoundElement($compound);
        $compoundFilters  = Pi::api('form', 'user')->getCompoundFilter($compound);

        // Get user compound
        $userCompound = Pi::api('user', 'user')->get($uid, $compound);

        // Compound edit form
        $forms = [];
        foreach ($userCompound as $set => $row) {
            $formName    = 'compound' . $set;
            $forms[$set] = new CompoundForm($formName, $compoundElements);
            // Set form data
            $row += [
                'set' => $set,
                'uid' => $uid,
            ];

            $forms[$set]->setData($row);
        }

        // Update compound
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $set  = (int)$post['set'];
            $forms[$set]->setInputFilter(new CompoundFilter($compoundFilters));
            $forms[$set]->setData($post);
            $result = [
                'status'  => 0,
                'message' => _a('User data update failed.'),
            ];
            if ($forms[$set]->isValid()) {
                $values        = $forms[$set]->getData();
                $values['uid'] = $uid;
                unset($values['submit']);
                unset($values['group']);

                // Canonize column function
                $canonizeColumn = function ($data, $meta) {
                    $result = [];
                    foreach ($data as $col => $val) {
                        if (in_array($col, $meta)) {
                            $result[$col] = $val;
                        }
                    }

                    return $result;
                };

                // Get new compound
                $userNewCompound = $userCompound;
                foreach ($userCompound as $key => $item) {
                    if ($key == $values['set']) {
                        $userNewCompound[$key] = $canonizeColumn(
                            $values,
                            array_keys($item)
                        );
                    }
                }

                // Update compound
                $status = Pi::api('user', 'user')->set(
                    $uid,
                    $compound,
                    $userNewCompound
                );
                Pi::api('user', 'user')->updateUser($uid, ['last_modified' => time()]);
                if ($status) {
                    Pi::service('event')->trigger('user_update', $uid);
                    $result['message'] = _a('User data update successful.');
                    $result['status']  = 1;
                }
            }
            $this->view()->assign('result', $result);
        }

        $this->view()->assign(
            [
                'user'  => $user,
                'forms' => $forms,
                'nav'   => $this->getNav($uid),
                'name'  => $compound,
            ]
        );
        $this->view()->setTemplate('edit-user');
    }

    /**
     * Delete compound action for ajax
     *
     * @return array
     */
    public function deleteCompoundAction()
    {
        $uid  = _get('uid');
        $name = _get('name', '');
        $set  = _get('set');

        $oldCompound = Pi::api('user', 'user')->get($uid, $name);
        $newCompound = [];
        foreach ($oldCompound as $key => $value) {
            if ($set != $key) {
                $newCompound[] = $value;
            }
        }

        // Update compound
        Pi::api('user', 'user')->set($uid, $name, $newCompound);
        Pi::api('user', 'user')->updateUser(
            $uid,
            ['last_modified' => time()]
        );
        Pi::service('event')->trigger('user_update', $uid);

        return $this->jump(
            [
                'controller' => 'edit',
                'action'     => 'compound',
                'uid'        => $uid,
                'name'       => $name,
            ],
            _a('Group deleted successfully.')
        );
    }

    /**
     * Get edit field and filter
     *
     * @return array
     */
    protected function getEditField()
    {
        $fields   = [];
        $elements = [];
        $filters  = [];

        $meta       = Pi::registry('field', 'user')->read();
        $editFields = [];
        foreach ($meta as $row) {
            if ($row['edit'] && $row['type'] != 'compound') {
                $editFields[] = $row;
            }
        }

        foreach ($editFields as $row) {
            $fields[] = $row['name'];
            $element  = Pi::api('form', 'user')->getElement($row['name']);
            /*
            $filter     = Pi::api('form', 'user')->getFilter($row['name']);
            */
            if ($row['name'] !== 'birthdate') {
                $filters[] = [
                    'name'     => $row['name'],
                    'required' => false,
                ];
            }
            if ($element) {
                $elements[] = $element;
            }

            /*
            if ($filter) {
                $filters[] = $filter;
            }
            */
        }

        return [$fields, $elements, $filters];
    }

    /**
     * Get base profile and compound nav
     *
     * @param $uid
     *
     * @return array
     */
    protected function getNav($uid)
    {
        $result[] = [
            'name'  => 'info',
            'title' => _a('Base info'),
            'link'  => $this->url('', ['controller' => 'edit', 'uid' => $uid]),
        ];

        // Avatar
        $result[] = [
            'name'  => 'avatar',
            'title' => _a('Avatar'),
            'link'  => $this->url(
                '',
                [
                'controller' => 'edit',
                'action'     => 'avatar',
                'uid'        => $uid,
            ]
            ),
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
                'link'  => $this->url(
                    '',
                    [
                    'controller' => 'edit',
                    'action'     => 'compound',
                    'uid'        => $uid,
                    'name'       => $row['name'],
                ]
                ),
            ];
        }

        // Avatar
        $result[] = [
            'name'  => 'view',
            'title' => _a('View'),
            'link'  => $this->url(
                '',
                [
                'controller' => 'View',
                'action'     => 'index',
                'uid'        => $uid,
            ]
            ),
        ];

        return $result;
    }

    protected function getUser($uid)
    {
        $user = Pi::api('user', 'user')->get(
            $uid,
            [
                'name',
            ],
            true
        );

        if (!$user || !$user['name']) {
            return $this->jumpTo404(_a('User was not found.'));
        }

        return $user;
    }
}
