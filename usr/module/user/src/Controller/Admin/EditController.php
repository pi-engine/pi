<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\User\Form\EditUserForm;
use Module\User\Form\EditUserFilter;
use Module\User\Form\CompoundForm;
use Module\User\Form\CompoundFilter;

/**
 * Edit user controller
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class EditController extends ActionController
{

    function indexAction() {
        $uid = _get('uid');

        // Check user exist
        $isExist = Pi::api('user', 'user')->getUser($uid)->id;
        if (!$isExist) {
            return $this->jumpTo404(__('User was not found.'));
        }

        // Get user basic information and user data
        $user = Pi::api('user', 'user')->get(
            $uid,
            array(
                'name',
            )
        );

        $nav = $this->getNav($uid);

        return array(
            'user'  => $user,
            'nav'   => $nav,
        );

    }

    /**
     * Edit user fields
     *
     * @return array|void
     */
    public function infoAction()
    {
        $result = array(
            'status' => 0,
            'message' => __('Edit faild'),
        );
        $uid = _get('uid');

        if (!$uid) {
            return $result;
        }

        // Get available edit fields
        list($fields, $formFields, $formFilters) = $this->getEditField();

        $form = new EditUserForm('info', $formFields);
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new EditUserFilter($formFilters, $uid));
            if ($form->isValid($uid)) {
                $values = $form->getData();

                // Update user
                $status = Pi::api('user', 'user')->updateUser($uid, $values);
                if ($status) {
                    $result['message'] = __('Edit user info successfully');
                    $result['status']  = 1;

                    return $result;
                } else {
                    return $result;
                }
            } else {
                $result['message'] = __('Edit user info fail');
                $result['error'] = $form->getMessages();
                return $result;
            }
        } else {
            $fieldsData = Pi::api('user', 'user')->get($uid, $fields);
            $form->setData($fieldsData);
            $this->view()->assign(array(
                'form'    => $form
            ));

            $this->view()->setTemplate('edit-info');
        }
        
    }

    /**
     * Edit user compound
     *
     * @return array
     */
    public function compoundAction()
    {
        $result = array(
            'status' => 0,
            'message' => __('Edit faild'),
        );

        $uid      = _get('uid');
        $compound = _get('compound');

        if (!$uid || !$compound) {
            return $result;
        }

        // Check uid and compound
        $row = $this->getModel('account')->find($uid, 'id');
        if (!$row) {
            return $result;
        }
        $row = $this->getModel('field')->find($compound, 'name');
        if (!$row) {
            return $result;
        }

        // Get compound elements and filters
        $compoundElements = Pi::api('user', 'form')->getCompoundElement($compound);
        $compoundFilters  = Pi::api('user', 'form')->getCompoundFilter($compound);

        // Get user compound
        $userCompound = Pi::api('user', 'user')->get($uid, $compound);

        // Compound edit form
        $forms = array();
        foreach ($userCompound as $set => $row) {
            $formName    = 'compound' . $set;
            $forms[$set] = new CompoundForm($formName, $compoundElements);
            // Set form data
            $row += array(
                'set'   => $set,
                'uid'   => $uid,
            );

            $forms[$set]->setData($row);
        }

        // Update compound
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $set  = (int) $post['set'];
            $forms[$set]->setInputFilter(new CompoundFilter($compoundFilters));
            $forms[$set]->setData($post);

            if ($forms[$set]->isValid()) {
                $values        = $forms[$set]->getData();
                $values['uid'] = $uid;
                unset($values['submit']);
                unset($values['group']);

                // Canonize column function
                $canonizeColumn = function ($data, $meta) {
                    $result = array();
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
                if ($status) {
                    $result['message'] = __('Update successfully');
                    $result['status']  = 1;
                    $result['set'] = $set;
                    return $result;
                } else {
                    return $result;
                }
            } else {
                $result['message'] = __('Edit compound faild');
                $result['error'] = $forms[$set]->getMessages();
                $result['set'] = $set;
                return $result;
            }
        } else {
            $this->view()->assign(array(
                'forms'    => $forms,
            ));
            $this->view()->setTemplate('edit-compound');
        }
    }

    /**
     * Delete compound action for ajax
     *
     * @return array
     */
    public function deleteCompoundAction()
    {
        $uid      = Pi::user()->getId();
        $compound = _post('compound', '');
        $set      = _post('set');

        $oldCompound = Pi::api('user', 'user')->get($uid, $compound);
        $newCompound = array();
        foreach ($oldCompound as $key => $value) {
            if ($set != $key ) {
                $newCompound[] = $value;
            }
        }

        // Update compound
        $status = Pi::api('user', 'user')->set($uid, $compound, $newCompound);

        return array(
            'status'  => $status ? 1 : 0,
            'message' => $status ? 'ok' : 'error',
        );
    }

    /**
     * Display user avatar and delete
     */
    public function avatarAction()
    {
        $uid  = _get('uid');
        $type = _get('type', '');

        if (!$uid) {
            return $this->jumpTo404('Inval user id');
        }

        if ($uid && $type == 'delete') {
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
            Pi::user()->set($uid, 'avatar', '');
            $this->view()->assign('message', __('Delete avatar successfully'));
        }

        $nav = $this->getNav($uid);
        $this->view()->assign(array(
            'uid'     => $uid,
            'nav'     => $nav,
            'cur_nav' => 'avatar'
        ));
    }

    /**
     * Get edit field and filter
     *
     * @return array
     */
    protected function getEditField()
    {
        $fields      = array();
        $formFields  = array();
        $formFilters = array();

        $model = $this->getModel('field');
        $rowset = $model->select(array(
            'is_edit'    => 1,
            'is_display' => 1,
            'active'     => 1,
            'type <> ?'  => 'compound',
        ));

        foreach ($rowset as $row) {
            $fields[]      = $row['name'];
            $formFields[]  = Pi::api('user', 'form')->getElement($row['name']);
            $formFilters[] = Pi::api('user', 'form')->getFilter($row['name']);
        }

        return array($fields, $formFields, $formFilters);

    }

    /**
     * Get base profile and compound nav
     * @param $uid
     *
     * @return array
     */
    protected function getNav($uid)
    {
        $result[] = array(
            'name' => 'info',
            'title' => __('Base info'),
        );

        // Avatar
        $result[] = array(
            'name' => 'avatar',
            'title' => __('Avatar'),
        );

        $rowset = $this->getModel('field')->select(
            array(
                'type'       => 'compound',
                'is_display' => 1,
                'is_edit'    => 1,
                'active'     => 1,
            )
        );

        foreach ($rowset as $row) {
            $result[] = array(
                'name'  => $row['name'],
                'title' => $row['title'],
            );
        }

        return $result;

    }
}