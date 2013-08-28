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
use Module\User\Form\CompoundForm;
use Module\User\Form\CompoundFilter;

class ProfileController extends ActionController
{

    /**
     * User profile page
     * 1. Owner profile view
     * 2. Other profile view
     * 3. Display user time line
     * 4. Display activity title
     *
     * @return array|void
     */
    public function indexAction()
    {
        $uid     = $this->params('id');
        $isLogin = Pi::service('user')->hasIdentity();
        $isOwner = false;
        $data    = array();

        if (!$uid && !$isLogin) {
            $this->jumpTo404();
        }

        // Check owner
        $loginUid = Pi::service('user')->getIdentity();
        if (!$uid || $uid == $loginUid) {
            $uid = Pi::service('user')->getIdentity();
            $isOwner = true;
        }

        // Get user information
        $user = $this->getUser($uid);

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
            $model    = $this->getModel('field_display');
            $select   = $model->select()
                        ->where(array('group' => $group->name));
            $select->order('order ASC');
            $fields = $model->selectWith($select);

            foreach ($fields as $field) {
                $data[$group->name]['fields'][$field->name] = array(
                    'name'  => $field->field,
                    'order' => $field->order,
                    'value' => Pi::api('user', 'user')->get($uid, $field->name),
                );

                if (!$compound) {
                    // Profile group
                    $profileFields = Pi::registry('profile', 'user')->read();
                    if (isset($profileFields[$field->field])) {
                        $title = $profileFields[$field->field]['title'];
                    }
                    $data[$group->name]['fields'][$field->name]['title'] = $title;
                } else {
                    // Compound group
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
            'data'    => $data,
            'uid'     => $uid,
            'isOwner' => $isOwner,
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
        $isLogin = Pi::user()->hasIdentity();
        $isOwner = false;

        if (!$uid && !$isLogin) {
            $this->jumpTo404('An error occur');
        }

        $loginUid = Pi::user()->getIdentity();
        if (!$uid || $uid == $loginUid) {
            $uid = Pi::user()->getIdentity();
            $isOwner = true;
        }

        // Get user information
        $user = $this->getUser($uid);

        // Get timeline
        $count    = Pi::api('user', 'timeline')->getCount();
        $timeline = Pi::api('user', 'timeline')->get($uid, $limit, $offset);

        // Get timeline meta list
        $timelineMetaList = Pi::api('user', 'timeline')->getList();

        // Set timeline meta
        foreach ($timeline as &$item) {
            $item['icon']  = $timelineMeta[$item['timeline']]['icon'];
            $item['title'] = $timelineMeta[$item['timeline']]['title'];
        }

        // Get activity meta for nav display
        $activityList = Pi::api('user', 'activity')->getList();

        // Set paginator
        $paginatorOption = array(
            'count'      => $count,
            'limit'      => $limit,
            'page'       => $page,
            'controller' => 'profile',
            'action'     => 'home',
            'uid'        => $uid,
        );
        $paginator = $this->setPaginator($paginatorOption);

        $this->view()->assign(array(
            'user'         => $user,
            'timeline'     => $timeline,
            'paginator'    => $paginator,
            'isOwner'      => $isOwner,
            'activityList' => $activityList,
        ));
    }

    /**
     * Edit profile action
     *
     */
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

        $form = new ProfileEditForm('profile', $fields);
        $form->setAttributes(array(
            'action' => $this->url('default',
                array(
                    'controller' => 'profile',
                    'action'     => 'edit.profile',
                    'group'      => $groupName,
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
                        'default',
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
            $profileData = Pi::api('user', 'user')->get($uid, $data);
            $form->setData($profileData);
        }

        $this->view()->assign('title', $groupName);
        $this->view()->setTemplate('profile-edit');
    }

    /**
     * Edit compound action
     */
    public function editCompoundAction()
    {
        $groupName    = $this->params('group', '');
        $uid          = Pi::service('user')->getIdentity();
        $errorMsg     = '';

        if ($this->request->isPost()) {
            $groupName = _post('group');
        }

        // Get compound name
        $rowset = $this->getModel('display_group')->find($groupName, 'name');
        $compound = $rowset ? $rowset->compound : '';

        if (!$groupName || !$uid || !$compound) {
            return $this->jumpTo404();
        }

        // Get compound element for edit
        $compoundElements = Pi::api('user', 'form')->getCompoundElement($compound);
        $compoundFilters  = Pi::api('user', 'form')->getCompoundFilter($compound);


        // Get user compound
        $compoundData = Pi::api('user', 'user')->get($uid, $compound);

        // Generate compound edit form
        $forms = array();
        foreach ($compoundData[$uid][$compound] as $set => $row) {
            $formName = 'compound' . $set;
            $forms[$set] = new CompoundForm($formName, $compoundElements);
            // Set form data
            $row += array('set' => $set);
            $forms[$set]->setData($row);
        }

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $set = $post['set'];
            $currentForm = $forms[$set];
            $currentForm->setInputFilter(new CompoundFilter($compoundFilters));

            if ($currentForm->isValid()) {
                //
                $data = $currentForm->getData();
                $curSet = $data['set'];

                // Replace compound
                $compoundData[$uid][$compound][$curSet] = $data;

                // Update compound
                $status = Pi::api('user', 'user')->updateCompound($uid, $compoundData[$uid]);
                $errorMsg = $status ? '' : __('Update error occur');
            } else {
                $errorMsg = __('Input data invalid');
            }
        }

        $this->view()->setTemplate('profile-edit-compound');
        $this->view()->assign(array(
            'form' => $forms,
            'errorMsg' => $errorMsg,
            'curGroup' => $groupName,
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

    /**
     * Get display group elements
     *
     * @param $groupNname
     * @param string $compound
     * @return array
     */
    protected function getGroupElements($groupName, $compound = '')
    {
        $fieldsModel = $this->getModel('field_display');
        $select      = $fieldsModel
                       ->select()
                       ->where(array('group' => $groupName));

        $select->order('order ASC');
        $rowset   = $fieldsModel->selectWith($select);
        $elements = array();
        $filters  = array();

        if (!$compound) {
            // Profile
            foreach ($rowset as $row) {
                $element    = Pi::api('user', 'form')->getElement($row->field);
                $filter     = Pi::api('user', 'form')->getFilter($row->field);
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

    /**
     * Get activity meta
     *
     * @return array active meta
     */
    protected function getActivityMeta()
    {
        $result = array();
        $model  = $this->getModel('activity');
        $select = $model->select()->where(array('active' => 1));
        $rowset = $model->selectWith($select);

        foreach ($rowset as $row) {
            $result[$row->name] = $row->array();
        }

        return $result;
    }


    /**
     * Get user information for profile page head display
     *
     * @param $uid
     * @return array user information
     */
    protected function getUser($uid)
    {
        $result = Pi::api('user', 'user')->get(
            $uid,
            array('name', 'gender', 'birthday')
        );

        return $result;
    }

    public function testAction()
    {
        $compoundMeta = Pi::api('user', 'user')->getMeta('compound');
        //vd($compoundMeta);
        $compoundElements = Pi::api('user', 'form')->getCompoundElement('address');

        //vd($compoundElements);
        $compoundElements = Pi::api('user', 'form')->getCompoundFilter('address');
        vd($compoundElements);


        $this->view()->setTemplate(false);
    }
}