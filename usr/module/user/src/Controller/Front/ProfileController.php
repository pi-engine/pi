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
use Module\User\Form\ProfileEditForm;
use Module\User\Form\ProfileEditFilter;

class ProfileController extends ActionController
{

    /**
     * User profile page
     * 1. Owner profile view
     * 2. Other profile view
     *
     * @return array|void
     */
    public function indexAction()
    {
        $uid = $this->params('id');
        $isLogin = Pi::service('user')->hasIdentity();

        if (!$uid && !$isLogin) {
            $this->jumpTo404();
        }

        $loginUid = Pi::service('user')->getIdentity();
        if (!$uid || $uid == $loginUid) {
            $uid = Pi::service('user')->getIdentity();
            $isOwner = true;
        }

        // Get display group
        $model  = $this->getModel('display_group');
        $select = $model->select();
        $select->columns('name', 'title', 'order');
        $select->order('order ASC');
        $groups = $model->selectWith($select);
        foreach ($groups as $group) {
            $data[$group->name] = array(
                'name'     => $group->name,
                'compound' => $group->compound,
                'title'    => $group->title,
            );

            $compound = $group->compound;
            $model = $this->getModel('field_display');
            $select = $model->select()
                ->where(array('group' => $group->name));
            $select->order('order ASC');
            $fields = $model->selectWith($select);

            foreach ($fields as $field) {
                $data[$group->name]['fields'][$field->name] = array(
                    'name' => $field->field,
                    'order' => $field->order,
                    'value' => Pi::api('user', 'user')->get($field->name, $uid),
                );

                // Profile group
                if (!$compound) {
                    $profileFields = Pi::registry('profile', 'user')->read();
                    if (isset($profileFields[$field->field])) {
                        $title = $profileFields[$field->field]['title'];
                    }
                    $data[$group->name]['fields'][$field->name]['title'] = $title;
                } else {
                    $compoundFields = Pi::registry('compound', 'user')
                        ->read($compound);
                    if (isset($compoundFields[$field->field])) {
                        $title = $data[$group->name]['fields'][$field->name]['title'];
                    }
                    $data[$group->name]['fields'][$field->name]['title'] = $title;
                }
            }
        }

        $this->view()->assign(array(
            'data' => $data,
        ));
    }

    /**
     * User home page
     * 1. Display timeline
     * 2. Display activity link
     *
     * @return array|void
     */
    public function homeAction()
    {
        $page   = $this->params('p', 1);
        $limit  = 10;
        $offset = (int) ($page -1) * $limit;

        $uid = $this->params('uid', '');
        $isLogin = Pi::service('user')->hasIdentity();
        $isOwner = false;

        if (!$uid && !$isLogin) {
            $this->jumpTo404('An error occur');
        }

        $loginUid = Pi::service('user')->getIdentity();
        if (!$uid || $uid == $loginUid) {
            $uid = Pi::service('user')->getIdentity();
            $isOwner = true;
        }

        // Get user information
        $user = array(
            'name'     => Pi::api('user', 'user')->get($uid, 'name'),
            'gender'   => Pi::api('user', 'user')->get($uid, 'gender'),
            'birthday' => Pi::api('user', 'user')->get($uid, 'birthday'),
        );

        // Get timeline
        $count    = Pi::service('user')->timeline($uid)->getCount();
        $timeline = Pi::service('user')->timeline($uid)->get($limit, $offset);

        // Get activity

        // Set paginator
        $paginatorOption = array(
            'count'      => $count,
            'limit'      => $limit,
            'page'       => $page,
            'controller' => 'profile',
            'action'     => 'home'
        );
        $paginator = $this->setPaginator($paginatorOption);

        $this->view()->assign(array(
            'user'      => $user,
            'timeline'  => $timeline,
            'paginator' => $paginator,
            'isOwner'   => $isOwner,
        ));
    }

    /**
     * Set paginator
     *
     * @param $option
     * @return \Pi\Paginator\Paginator
     */
    protected function setPaginator($option)
    {
        $paginator = Paginator::factory(intval($option['count']));
        $paginator->setItemCountPerPage($option['limit']);
        $paginator->setCurrentPageNumber($option['page']);
        $paginator->setUrlOptions(array(
            // Use router to build URL for each page
            'pageParam'     => 'p',
            'totalParam'    => 't',
            'router'        => $this->getEvent()->getRouter(),
            'route'         => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params'        => array(
                'module'        => $this->getModule(),
                'controller'    => $option['controller'],
                'action'        => $option['action'],
            ),
        ));

        return $paginator;
    }

    public function editProfileAction()
    {
        $uid = Pi::service('user')->getIdentity();
        $groupName = $this->params('group');

        if (!$uid || $groupName) {
            return $this->jumpTo404();
        }

        list($fields, $filters) = $this->getGroupElements($groupName);

        // Add other elements
        $fields[] = array(
            'name'  => 'uid',
            'type'  => 'hidden',
            'attributes' => array(
                'value' => $uid,
            ),
        );
        $fields[] = array(
            'name'       => 'submit',
            'type'       => 'submit',
            'attributes' => array(
                'value' => 'submit'
            ),
        );

        $form = new ProfileEditForm('profile', $fields);
        $form->setAttributes(array(
            'action' => $this->url('',
                array(
                    'controller' => 'profile',
                    'action' => 'editProfile'
                )),
        ));

        if ($this->request->isPost()) {
            // Get profile filter
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new ProfileEditFilter($filters));
            if ($form->isValid()) {
                $data = $form->getData();
                // Update user
                $status = Pi::api('user', 'user')->updateUser($data, $uid);

                // Redirect to profile page
                if ($status) {
                    return $this->redirect(
                        '',
                        array('controller' => 'profile', 'action' => 'index')
                    );
                }
            }
        } else {
            // Get profile data
            $model = $this->getModel('field_display');
            $select = $model->select()->where(array('group' => $groupName));
            $result = $model->selectWith($select);
            foreach ($result as $row) {
                $data[] = $row->field;
            }
            $profileData = Pi::api('user', 'user')->get($data, $uid);
            $form->setData($profileData);
        }
    }

    /**
     * Edit compound
     *
     */
    public function editCompoundAction()
    {
        $compound = $this->params('group', '');
        $uid = Pi::service('user')->getIdentity();
        $compoundMeta = Pi::api('user', 'user')->getMeta('compound');
        if (!in_array($compound, $compoundMeta) || !$uid) {
            return $this->jumpTo404();
        }
    }

    public function testAction()
    {
        $this->view()->setTemplate(false);
    }

    protected function getGroupElements($groupNmae, $compound = '')
    {
        $fieldsModel = $this->getModel('field_display');
        $select = $fieldsModel->select()->where(array('group' => $groupNmae));
        $select->order('order ASC');
        $rowset = $fieldsModel->selectWith($select);

        $elements = array();
        $filters  = array();
        // Profile
        if (!$compound) {
            foreach ($rowset as $row) {
                $element = Pi::api('user', 'form')->getElement($row->field);
                $filter = Pi::api('user', 'form')->getFilter($row->field);
                $elements[] = $element;
                $filters[]  = $filter;
            }

            return array($elements, $filters);
        } else {
            // Compound
            foreach ($rowset as $row) {
                $element = Pi::api('user', 'form')
                    ->getCompoundElement($compound, $row->field);
                $filter = Pi::api('user', 'form')
                    ->getCompoundFilter($compound, $row->field);
                $elements[] = $element;
                $filters[]  = $filter;
            }
            return array($elements, $filters);
        }
    }
}