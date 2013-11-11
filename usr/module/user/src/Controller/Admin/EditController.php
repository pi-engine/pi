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

        // Get user basic information and user data
        $user = $this->getUser($uid);

        // Get available edit fields
        list($fields, $formFields, $formFilters) = $this->getEditField();
        // Add other elements
        $formFields[] = array(
            'name'  => 'uid',
            'type'  => 'hidden',
            'attributes' => array(
                'value' => $uid,
            ),
        );
        $form = new EditUserForm('info', $formFields);
        if ($this->request->isPost()) {
            $form->setData($this->request->getPost());
            $form->setInputFilter(new EditUserFilter($formFilters));
            $result['message'] = _a('Edit user info failed');
            $result['status']  = 0;
            if ($form->isValid()) {
                // Update user
                $values = $form->getData();
                $values['last_modified'] = time();
                $status = Pi::api('user', 'user')->updateUser($uid, $values);
                if ($status) {
                    $result['message'] = _a('Edit user info successfully');
                    $result['status']  = 1;
                }
            }
            $this->view()->assign('result', $result);
        } else {
            $fieldsData = Pi::api('user', 'user')->get($uid, $fields);
            $form->setData($fieldsData);
        }

        $this->view()->assign(array(
            'user'   => $user,
            'nav'    => $this->getNav($uid),
            'name'   => 'info',
            'form'   => $form
        ));
        $this->view()->setTemplate('edit-user');
    }

    /**
     * Display user avatar and delete
     */
    public function avatarAction()
    {
        $uid  = _get('uid');

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
            $result = array(
                'status'  => 0,
                'message' => _a('Replace user avater failed')
            );
            if ($status) {
                $result = array(
                    'status'  => 1,
                    'message' => _a('Replace user avater successfully')
                );
            }
            $this->view()->assign('result', $result);
        }
        

        $this->view()->assign(array(
            'user'   => $user,
            'nav'    => $this->getNav($uid),
            'name'   => 'avatar',
            'avatar' => Pi::user()->avatar()->get($uid, 'large')
        ));
        $this->view()->setTemplate('edit-user');
    }

    /**
     * Edit user compound
     *
     * @return array
     */
    public function compoundAction()
    {
        
        $uid = _get('uid');
        $compound = _get('name');

        // Get user basic information and user data
        $user = $this->getUser($uid);


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
            $result = array(
                'status'  => 0,
                'message' => _a('Edit user info failed')
            );
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
                Pi::api('user', 'user')->updateUser($uid, array('last_modified' => time()));

                if ($status) {
                    $result['message'] = _a('Edit user info successfully');
                    $result['status']  = 1;
                }
                
            }
            $this->view()->assign('result', $result);
        }

        $this->view()->assign(array(
            'user'   => $user,
            'forms' => $forms,
            'nav'   => $this->getNav($uid),
            'name'  => $compound
        ));
        $this->view()->setTemplate('edit-user');

    }

    /**
     * Delete compound action for ajax
     *
     * @return array
     */
    public function deleteCompoundAction()
    {
        $uid      = _get('uid');
        $name     = _get('name', '');
        $set      = _get('set');

        $oldCompound = Pi::api('user', 'user')->get($uid, $name);
        $newCompound = array();
        foreach ($oldCompound as $key => $value) {
            if ($set != $key ) {
                $newCompound[] = $value;
            }
        }

        // Update compound
        Pi::api('user', 'user')->set($uid, $name, $newCompound);
        Pi::api('user', 'user')->updateUser(
            $uid,
            array('last_modified' => time())
        );

        return $this->jump(array(
            'controller'  => 'edit',
            'action'      => 'compound',
            'uid'         => $uid,
            'name'        => $name
        ), _a('Delete this group successfully'));
        
    }

    

    /**
     * Get edit field and filter
     *
     * @return array
     */
    protected function getEditField()
    {
        $fields   = array();
        $elements = array();
        $filters  = array();

        $model = $this->getModel('field');
        $rowset = $model->select(array(
            'is_edit'    => 1,
            'is_display' => 1,
            'active'     => 1,
            'type <> ?'  => 'compound',
        ));

        foreach ($rowset as $row) {
            $fields[]   = $row['name'];
            $element    = Pi::api('user', 'form')->getElement($row['name']);
            $filter     = Pi::api('user', 'form')->getFilter($row['name']);
            if ($element) {
                $elements[] = $element;
            }
            if ($filter) {
                $filters[] = $filter;
            }
        }

        return array($fields, $elements, $filters);

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
            'name'  => 'info',
            'title' => _a('Base info'),
            'link'  => $this->url('', array('controller' => 'edit', 'uid' => $uid)),
        );

        // Avatar
        $result[] = array(
            'name'  => 'avatar',
            'title' => _a('Avatar'),
            'link'  => $this->url('', array('controller' => 'edit', 
                                            'action' => 'avatar', 
                                            'uid' => $uid)),
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
                'link'  => $this->url('', array('controller' => 'edit', 
                                                'action' => 'compound', 
                                                'uid' => $uid,
                                                'name' => $row['name'])),
            );
        }

        return $result;

    }

    protected function getUser($uid)
    {
        $user = Pi::api('user', 'user')->get(
            $uid,
            array(
                'name',
            )
        );

        if (!$user['name']) {
            return $this->jumpTo404(_a('User was not found.'));
        }

        return $user;
    }
}