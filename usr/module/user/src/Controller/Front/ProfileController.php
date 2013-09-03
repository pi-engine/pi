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
use Pi\Paginator\Paginator;

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
        $loginUid = Pi::service('user')->getIdentity();vd($loginUid);
        if (!$uid || $uid == $loginUid) {
            $uid = Pi::service('user')->getIdentity();
            $isOwner = true;
        }

        // Get user information
        $user = $this->getUser($uid);

        // Get display group
        $profileGroup = $this->getProfile($uid, 'display');

        // Get activity meta for nav display
        $activityList = Pi::api('user', 'activity')->getList();

        $this->view()->assign(array(
            'profileGroup' => $profileGroup,
            'uid'          => $uid,
            'isOwner'      => $isOwner,
            'user'         => $user,
            'activityList' => $activityList,
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
        $count    = Pi::api('user', 'timeline')->getCount($uid);
        $timeline = Pi::api('user', 'timeline')->get($uid, $limit, $offset);

        // Get timeline meta list
        $timelineMetaList = Pi::api('user', 'timeline')->getList();

        // Set timeline meta
        foreach ($timeline as &$item) {
            $item['icon']  = $timelineMetaList[$item['timeline']]['icon'];
            $item['title'] = $timelineMetaList[$item['timeline']]['title'];
        }

        // Get activity meta for nav display
        $activityList = Pi::api('user', 'activity')->getList();

        // Get quick link
        $quicklink = $this->getQuicklink();


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
            'uid'          => $uid,
            'user'         => $user,
            'timeline'     => $timeline,
            'paginator'    => $paginator,
            'isOwner'      => $isOwner,
            'quicklink'    => $quicklink,
            'activityList' => $activityList,
        ));
    }

    /**
     * Edit profile action
     * Task:
     * 1. Receive profile group name
     * 2. According to group name construct form
     * 3. Process form submit info
     * 4. Update user profile info
     *
     */
    public function editProfileAction()
    {
        $uid = Pi::user()->getIdentity();
        $groupName = $this->params('group', '');
        $groupName = 'basic_info';
        $status    = '';

        // Error hand
        if (!$uid || !$groupName) {
            return $this->jumpTo404();
        }

        // Get fields and filters for edit
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
            'name'  => 'group',
            'type'  => 'hidden',
            'attributes' => array(
                'value' => $groupName,
            ),
        );

        $form = new ProfileEditForm('profile', $fields);
        $form->setAttributes(array(
            'action' => $this->url('',
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
                $status = Pi::api('user', 'user')->updateUser($uid, $data);
                if (empty($status)) {
                    $status = true;
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
            // Set user info to form
            $form->setData($profileData);
        }

        // Get side nav items
        $groups = Pi::api('user', 'group')->getList();
        foreach ($groups as $key => &$group) {
            $action = $group['compound'] ? 'edit.compound' : 'edit.profile';
            $group['link'] = $this->url(
                '',
                array(
                    'controller' => 'profile',
                    'action'     => $action,
                    'group'      => $key,
                )
            );
        }

        $this->view()->assign(array(
            'form'     => $form,
            'title'    => $groupName,
            'groups'   => $groups,
            'curGroup' => $groupName,
            'status'   => $status,
        ));
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
        $i = 0;
        foreach ($compoundData as $set => $row) {
            $formName = 'compound' . $set;
            $forms[$set] = new CompoundForm($formName, $compoundElements);
            // Set form data
            $row += array(
                'set'   => $set,
                'group' => $groupName,
                'uid'   => $uid,
            );

            $forms[$set]->setData($row);
            $i++;
        }

        // New compound form
        $addForm = new CompoundForm('new.compound', $compoundElements);
        $addForm->setData(array(
            'set'   => $i,
            'group' => $groupName,
            'uid'   => $uid,
        ));
        unset($i);

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $set  = (int) $post['set'];
            $forms[$set]->setInputFilter(new CompoundFilter($compoundFilters));
            $forms[$set]->setData($post);

            if ($forms[$set]->isValid()) {
                $values = $forms[$set]->getData();
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
                $newCompoundData = $compoundData;
                $i = 0;
                foreach ($compoundData as $key => $item) {
                    $i++;
                    if ($key == $values['set']) {
                        $newCompoundData[$key] = $canonizeColumn(
                            $values,
                            array_keys($item)
                        );
                    }
                }

                // Add compound
                if ($values['set'] == $i) {
                    $newCompoundData[$i] = $canonizeColumn(
                        $values,
                        array_keys($item)
                    );
                }

                // Update compound
                Pi::api('user', 'user')->set($uid, $compound, $newCompoundData);
                return array(
                    'status' => 1
                );
            } else {
                return array(
                    'status' => 0,
                    'message' => $forms[$set]->getMessages(),
                );
            }
        }

        // Get side nav items
        $groups = Pi::api('user', 'group')->getList();
        foreach ($groups as $key => &$group) {
            $action = $group['compound'] ? 'edit.compound' : 'edit.profile';
            $group['link'] = $this->url(
                '',
                array(
                    'controller' => 'profile',
                    'action'     => $action,
                    'group'      => $key,
                )
            );
        }

        $this->view()->setTemplate('profile-edit-compound');
        $this->view()->assign(array(
            'forms'        => $forms,
            'errorMsg'     => $errorMsg,
            'curGroup'     => $groupName,
            'groups'       => $groups,
            'addForm'      => $addForm,
        ));
    }

    /**
     * Edit compound order
     * For ajax
     * @return array
     */
    public function editCompoundSetAction()
    {
        Pi::service('log')->active(false);
        $compound = _post('compound');
        $set      = _post('set');
        $uid      = Pi::user()->getIdentity();
        $message = array(
            'status' => 0,
        );

        $order = explode(',', $set);
        if (!$order || !$uid) {
            return $message;
        }

        $oldCompound = Pi::api('user', 'user')->get($uid, $compound);

        if (!$oldCompound) {
            return $message;
        }

        foreach ($order as $key => $value) {
            $newCompound[$value] = $oldCompound[$key];
        }
        ksort($newCompound);

        // Update compound
        //$data = $this->assembleCompound($uid, $compound, $newCompound);
        Pi::api('user', 'user')->set($uid, $compound, $newCompound);
        $message['status'] = 1;

        return $message;
    }

    /**
     * Delete compound action for ajax
     *
     * @return array
     */
    public function deleteCompoundAction()
    {
        Pi::service('log')->active(false);

        $uid      = _post('uid', '');
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
     * Assemble compound according to rawData
     *
     * @param $uid
     * @param $compound
     * @param $rawData
     * @return array
     */
    protected function assembleCompound($uid, $compound, $rawData)
    {
        // Get user compound map
        $model  = $this->getModel('compound');
        $select = $model->select()->where(array('uid' => $uid));
        $select->group(array('compound'));
        $select->columns(array('compound'));
        $rowset = $model->selectWith($select)->toArray();

        $map = array();
        foreach ($rowset as $row) {
            $map[] = $row['compound'];
        }

        if (!in_array($compound, $map)) {
            return false;
        }

        $result = Pi::api('user', 'user')->get($uid, $map);
        if (isset($result[$compound])) {
            $result[$compound] = $rawData;
        }
        return $result;

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
                'uid'           => $option['uid'],
            ),
        ));

        return $paginator;
    }

    /**
     * Get display group elements for edit
     * Include
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
            array('name', 'gender', 'birthdate')
        );

        return $result;
    }

    /**
     * Get Administrator custom display group
     *
     * @return array
     */
    protected function getDisplayGroup()
    {
        $result = array();

        $model  = $this->getModel('display_group');
        $select = $model->select();
        $select->columns(array('name', 'title', 'order', 'compound'));
        $select->order('order ASC');
        $groups = $model->selectWith($select);

        foreach ($groups as $group) {
            $result[$group->name] = $group->toArray();
        }

        return $result;
    }

    /**
     * Get field display
     *
     * @param $group
     * @return array
     */
    protected function getFieldDisplay($group)
    {
        $result = array();

        $model = $this->getModel('field_display');
        $select = $model->select()->where(array('group' => $group));
        $select->columns(array('field', 'group', 'order'));
        $select->order('order ASC');
        $fields = $model->selectWith($select);

        foreach ($fields as $field) {
            $result[] = $field->field;
        }

        return $result;
    }

    /**
     * Get user profile information
     * Group and group items title and value
     *
     * @param $uid User id
     * @param string $type Display or edit
     * @return array
     */
    protected function getProfile($uid, $type = 'display')
    {
        $result = array();
        if ('display' == $type) {
            // Get account or profile meta
            $fieldMeta = Pi::api('user', 'user')->getMeta('', 'display');
            $groups = $this->getDisplayGroup();

            foreach ($groups as $groupName => $group) {
                $result[$groupName] = $group;
                $result[$groupName]['fields'] = array();
                $fields = $this->getFieldDisplay($groupName);

                if ($group['compound']) {
                    // Compound

                    // Compound meta
                    $compoundMeta = Pi::registry('compound', 'user')->read(
                        $group['compound']
                    );

                    // Compound value
                    $compound     = Pi::api('user', 'user')->get(
                        $uid, $group['compound']
                    );

                    // Gen Result
                    foreach ($compound as $set => $item) {
                        foreach ($item as $key => $value) {
                            $result[$groupName]['fields'][$set][] = array(
                                'title' => $compoundMeta[$key]['title'],
                                'value' => $value,
                            );
                        }
                    }
                } else {
                    // Profile
                    foreach ($fields as $field) {
                        $result[$groupName]['fields'][0][$field] = array(
                            'title' => $fieldMeta[$field]['title'],
                            'value' => Pi::api('user', 'user')->get($uid, $field),
                        );
                    }
                }
            }
        }

        return $result;
    }

    protected function getQuicklink($limit = null, $offset = null)
    {
        $result = array();
        $model = $this->getModel('quicklink');
        $where = array(
            'active'  => 1,
            'display' => 1,
        );
        $columns = array(
            'id',
            'name',
            'title',
            'module',
            'link',
            'icon',
        );

        $select = $model->select()->where($where);
        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }

        $select->columns($columns);
        $rowset = $model->selectWith($select);

        foreach ($rowset as $row) {
            $result[] = $row->toArray();
        }

        return $result;

    }

    public function testAction()
    {
//        $compoundMeta = Pi::api('user', 'user')->getMeta('compound');
//        //vd($compoundMeta);
//        $compoundElements = Pi::api('user', 'form')->getCompoundElement('address');
//
//        //vd($compoundElements);
//        $compoundElements = Pi::api('user', 'form')->getCompoundFilter('address');
//        vd($compoundElements);
        //vd($this->getDisplayGroup());
        //vd($this->getFieldDisplay('basic_info'));
        //vd(Pi::api('user', 'user')->getMeta('', 'display'));
        //vd(Pi::api('user', 'user')->getMeta('', 'display'));
        //vd(Pi::registry('compound', 'user')->read(array('work', 'education')));
        //vd(Pi::api('user', 'user')->get(7, 'work'));
        //vd($this->getFieldDisplay('work'));
        //vd(Pi::registry('compound', 'user')->read('work'));
        //
        //d($this->getProfile(7));
        //$this->getProfile(7);
        //d(Pi::api('user', 'user')->get(8, 'work'));
        //$param = $this->params('test', '');
        //vd($param);
        //$result = $this->getQuicklink();
        //vd($result);
        //vd(Pi::path('module'));
        //vd(Pi::registry('profile', 'user')->read());
        $this->view()->setTemplate(false);
    }
}