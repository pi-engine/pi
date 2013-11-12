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

/**
 * Profile controller
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class ProfileController extends ActionController
{

    /**
     * User profile for owner
     *
     * @return array|void
     */
    public function indexAction()
    {
        $uid = Pi::user()->getId();
        if (!$uid) {
            $this->jump(
                array(
                    '',
                    array('controller' => 'login', 'action' => 'index')
                ),
                __('Please login'),
                5
            );
            return;
        }

        // Get user information
        $user = $this->getUser($uid);

        // Get display group
        $profileGroup = $this->getProfile($uid);

        // Get activity meta for nav display
        $nav = Pi::api('user', 'nav')->getList('profile');

        // Get quicklink
        $quicklink = $this->getQuicklink();

        $this->view()->assign(array(
            'profile_group' => $profileGroup,
            'uid'           => $uid,
            'user'          => $user,
            'nav'           => $nav,
            'quicklink'     => $quicklink,
            'is_owner'      => true,
        ));

        $this->view()->setTemplate('profile-index');
    }

    /**
     * Profile for view
     *
     */
    public function viewAction()
    {
        $uid = $this->params('uid', '');
        if (!$uid) {
            return $this->jumpTo404(__('Invalid user ID!'));
        }

        // Get user information
        $user = $this->getUser($uid);

        // Get display group
        $profileGroup = $this->getProfile($uid);

        // Get viewer role: public member follower following owner
        $role = Pi::user()->hasIdentity() ? 'member' : 'public';

        // Filter field according to privacy setting
        $profileGroup = Pi::api('user', 'privacy')->filterProfile(
            $uid,
            $role,
            $profileGroup,
            'group'
        );
        $user         = Pi::api('user', 'privacy')->filterProfile(
            $uid,
            $role,
            $user,
            'user'
        );

        // Get activity meta for nav display
        $nav = Pi::api('user', 'nav')->getList('profile', $uid);

        // Get quicklink
        $quicklink = $this->getQuicklink();

        $this->view()->assign(array(
            'profile_group' => $profileGroup,
            'uid'           => $uid,
            'user'          => $user,
            'nav'           => $nav,
            'quicklink'     => $quicklink,
            'is_owner'      => false,
        ));

        $this->view()->setTemplate('profile-view');

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
        $uid = Pi::user()->getId();
        $groupId   = $this->params('group', '');
        $result = array(
            'status'  => 0,
            'message' => '',
        );

        // Redirect login page if not logged in
        if (!$uid) {
            $this->jump(
                'user',
                array('controller' => 'login', 'action' => 'index'),
                __('Need login'),
                2
            );
        }

        // Error hand
        if (!$groupId) {
            return $this->jumpTo404();
        }

        // Get fields and filters for edit
        list($fields, $filters) = $this->getGroupElements($groupId);
        $fields[] = array(
            'name'  => 'group',
            'type'  => 'hidden',
            'attributes' => array(
                'value' => $groupId,
            ),
        );
        $form = new ProfileEditForm('profile', $fields);
        $form->setAttributes(array(
            'action' => $this->url('',
                array(
                    'controller' => 'profile',
                    'action'     => 'edit.profile',
                    'group'      => $groupId,
                )),
        ));

        if ($this->request->isPost()) {
            // Get profile filter
            $filters = array_filter($filters);
            $form->setInputFilter(new ProfileEditFilter($filters));
            $form->setData($this->request->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                $data['last_modified'] = time();
                // Update user
                Pi::api('user', 'user')->updateUser($uid, $data);
                $result['status']  = 1;
                $result['message'] = __('Update successfully');
            } else {
                $result['message'] = __('Data saved failed');
            }

            $this->view()->assign('result', $result);
        } else {
            // Get profile data
            $model = $this->getModel('display_field');
            $select = $model->select()->where(array('group' => $groupId));
            $select->order('order');
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

        $this->view()->assign(array(
            'form'      => $form,
            'title'     => $groups[$groupId]['title'],
            'groups'    => $groups,
            'group_id'  => $groupId,
            'user'      => $this->getUser($uid)
        ));
        $this->view()->setTemplate('profile-edit');
    }

    /**
     * Edit compound
     *
     */
    public function editCompoundAction()
    {
        $groupId = $this->params('group', '');
        $uid     = Pi::service('user')->getId();

        // Redirect login page if not logged in
        if (!$uid) {
            $this->jump(
                'user',
                array('controller' => 'login', 'action' => 'index'),
                __('Need login'),
                2
            );
        }

        // Get compound name
        $rowset = $this->getModel('display_group')->find($groupId, 'id');
        $compound = $rowset ? $rowset->compound : '';

        if (!$groupId || !$compound) {
            return $this->jumpTo404();
        }

        // Get compound element for edit
        $compoundElements = Pi::api('user', 'form')->getCompoundElement($compound);
        $compoundFilters  = Pi::api('user', 'form')->getCompoundFilter($compound);
        $form = new CompoundForm('new-compound', $compoundElements);
        $form->setData(array('group' => $groupId));

        // Get side nav items
        $groups       = Pi::api('user', 'group')->getList();
        $profileGroup = $this->getProfile($uid);
        foreach ($profileGroup[$groupId]['fields'] as $key => $value) {
            $compounds[$key]['set']    = $key;
            $compounds[$key]['fields'] = $value;
        }
        $this->view()->assign(array(
            'compounds' => $compounds,
            'group_id'  => $groupId,
            'title'     => $groups[$groupId]['title'],
            'groups'    => $groups,
            'form'      => $form,
            'user'      => $this->getUser($uid)
        ));

        $this->view()->setTemplate('profile-edit-compound');
    }

    /**
     * Get edit compound form
     *
     * @return $this|array
     */
    public function compoundFormAction()
    {
        $groupId = _get('groupId');
        $set     = _get('set', '');d($set);
        $set     = $set !== '' ? $set : _get('order');
        $uid     = Pi::service('user')->getId();
   
        // Get compound name
        $rowset = $this->getModel('display_group')->find($groupId, 'id');
        $compound = $rowset ? $rowset->compound : '';

        if (!$groupId || !$compound) {
            return $this->jumpTo404();
        }

        $compoundElements = Pi::api('user', 'form')->getCompoundElement($compound);
        $compoundFilters  = Pi::api('user', 'form')->getCompoundFilter($compound);
        $form     = new CompoundForm('new-compound', $compoundElements);
        $compoundData = Pi::api('user', 'user')->get($uid, $compound);

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $set  = (int) $post['set'];
            $form->setInputFilter(new CompoundFilter($compoundFilters));
            $form->setData($post);

            if ($form->isValid()) {
                $values = $form->getData();
                $values['uid'] = $uid;

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
                foreach ($compoundData as $key => $item) {
                    if ($key == $values['set']) {
                        $newCompoundData[$key] = $canonizeColumn(
                            $values,
                            array_keys($item)
                        );
                    }
                }

                // Update compound
                Pi::api('user', 'user')->set($uid, $compound, $newCompoundData);
                $profileGroup = $this->getProfile($uid);
                $compounds = array();
                foreach ($profileGroup[$groupId]['fields'] as $key => $value) {
                    $compounds[$key]['set']    = $key;
                    $compounds[$key]['fields'] = $value;
                }
                return array(
                    'status' => 1,
                    'data'   => $compounds[$set],
                );
            } else {
                return array(
                    'status' => 0,
                    'message' => $form->getMessages(),
                );
            }
        }

        if (isset($compoundData[$set])) {
            $compoundData[$set]['set']   = $set;
            $compoundData[$set]['group'] = $groupId;
            $form->setData($compoundData[$set]);
        }

        $this->view()->assign(array(
            'form'      => $form,
            'ResetShow' => 1
        ));
        $this->view()->setTemplate('system:component/form-horizontal');
    }

    /**
     * Edit compound order
     * For ajax
     * @return array
     */
    public function editCompoundSetAction()
    {
        $groupId    = _post('groupId');
        $row        = $this->getModel('display_group')->find($groupId, 'id');
        $compound   = $row ? $row->compound : '';
        $set        = _post('set');
        $set        = $set !== '' ? $set : _post('order');
        $uid        = Pi::user()->getId();
        $result    = array(
            'status' => 0,
        );

        $order = explode(',', $set);
        if (!$order || !$uid) {
            return $result;
        }

        $oldCompound = Pi::api('user', 'user')->get($uid, $compound);

        if (!$oldCompound) {
            return $result;
        }

        foreach ($order as $key => $value) {
            $newCompound[$value] = $oldCompound[$key];
        }
        ksort($newCompound);

        // Update compound
        Pi::api('user', 'user')->set($uid, $compound, $newCompound);
        $result['status'] = 1;

        return $result;
    }

    /**
     * Delete compound action for ajax
     *
     * @return array
     */
    public function deleteCompoundAction()
    {
        $result = array(
            'status'  => 0,
            'message' => ''
        );

        $uid        = Pi::user()->getId();
        $groupId = _post('groupId', '');
        $set        = _post('set');

        $row = $this->getModel('display_group')->find($groupId, 'id');
        if (!$row) {
            $result['message'] = 'error';
            return $result;
        }

        $compound = $row->compound;
        $oldCompound = Pi::api('user', 'user')->get($uid, $compound);
        $newCompound = array();
        foreach ($oldCompound as $key => $value) {
            if ($set != $key ) {
                $newCompound[] = $value;
            }
        }

        // Update compound
        $status = Pi::api('user', 'user')->set($uid, $compound, $newCompound);
        $result['status'] = $status ? 1 : 0;
        $result['message'] = $status ? 'success' : 'error';

        return $result;

    }

    /**
     * Add compound item
     *
     * @return array
     */
    public function addCompoundItemAction()
    {
        $uid        = Pi::user()->getId();
        $groupId = _post('group', '');

        if (!$uid || !$groupId) {
            return array(
                'status'  => 0,
                'message' => 'error',
            );
        }

        // Get compound name
        $compound = $this->getCompoundName($groupId);

        // Get compound element for edit
        $compoundMeta     = Pi::registry('compound_field', 'user')->read($compound);
        $compoundElements = Pi::api('user', 'form')->getCompoundElement($compound);
        $compoundFilters  = Pi::api('user', 'form')->getCompoundFilter($compound);
        $compoundData     = Pi::api('user', 'user')->get($uid, $compound);

        $form = new CompoundForm('new-compound', $compoundElements);
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setInputFilter(new CompoundFilter($compoundFilters));
            $form->setData($post);

            if ($form->isValid()) {
                $values = $form->getData();
                $values['uid'] = $uid;
                unset($values['submit']);
                unset($values['group']);

                $newCompoundItem = array();
                foreach ($values as $col => $val) {
                    if (isset($compoundMeta[$col])) {
                        $newCompoundItem[$col] = $val;
                    }
                }

                $compoundData[] = $newCompoundItem;

                // Update compound
                $status = Pi::api('user', 'user')->set(
                    $uid,
                    $compound,
                    $compoundData
                );

                $profileGroup = $this->getProfile($uid);
                $compounds = array();
                foreach ($profileGroup[$groupId]['fields'] as $key => $value) {
                    $compounds[$key]['set']    = $key;
                    $compounds[$key]['fields'] = $value;
                }

                return array(
                    'status'  => $status ? 1 : 0,    
                    'data'    => array_pop($compounds),
                );
            } else {
                return array(
                    'status' => 0,
                    'message' => $form->getMessages(),
                );
            }
        }
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
        $params = array(
            'module'        => $this->getModule(),
            'controller'    => $option['controller'],
            'action'        => $option['action'],
        );

        if (isset($option['uid'])) {
            $params['uid'] = $option['uid'];
        }

        $paginator = Paginator::factory(intval($option['count']), array(
            'limit' => $option['limit'],
            'page'  => $option['page'],
            'url_options'   => array(
                'params'    => $params
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
    protected function getGroupElements($groupId, $compound = '')
    {
        $fieldsModel = $this->getModel('display_field');
        $select      = $fieldsModel
                       ->select()
                       ->where(array('group' => $groupId));

        $select->order('order ASC');
        $rowset   = $fieldsModel->selectWith($select);
        $elements = array();
        $filters  = array();

        if (!$compound) {
            // Profile
            foreach ($rowset as $row) {
                $element    = Pi::api('user', 'form')->getElement($row->field);
                $filter     = Pi::api('user', 'form')->getFilter($row->field);
                if ($element) {
                    $elements[] = $element;
                }
                if ($filter) {
                    $filters[] = $filter;
                }
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
            array('name', 'gender', 'birthdate'),
            true
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
     * @param $group
     * @return array
     */
    protected function getFieldDisplay($groupId)
    {
        $result = array();

        $model  = $this->getModel('display_field');
        $select = $model->select()->where(array('group' => $groupId));
        $select->columns(array('field', 'order'));
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
    protected function getProfile($uid)
    {
        $result = array();

        // Get account or profile meta
        $fieldMeta = Pi::api('user', 'user')->getMeta('', 'display');
        $groups    = $this->getDisplayGroup();

        foreach ($groups as $groupId => $group) {
            $result[$groupId] = $group;
            $result[$groupId]['fields'] = array();
            $fields = $this->getFieldDisplay($groupId);

            if ($group['compound']) {
                // Compound meta
                $compoundMeta = Pi::registry('compound_field', 'user')->read(
                    $group['compound'],
                    true
                );

                // Compound value
                $compound     = Pi::api('user', 'user')->get(
                    $uid, $group['compound']
                );
                // Generate Result
                foreach ($compound as $set => $item) {
                    // Compound value
                    $compoundValue = array();
                    foreach ($fields as $field) {
                        $compoundValue[] = array(
                            'title' => $compoundMeta[$field]['title'],
                            'value' => $item[$field],
                        );

                    }
                    $result[$groupId]['fields'][$set] = $compoundValue;
                }
            } else {
                // Profile
                foreach ($fields as $field) {
                    $result[$groupId]['fields'][0][$field] = array(
                        'title' => $fieldMeta[$field]['title'],
                        'value' => Pi::api('user', 'user')->get($uid, $field, true),
                    );
                }
            }
        }

        return $result;

    }

    /**
     * Get quicklink
     *
     * @param null $limit
     * @param null $offset
     * @return array
     */
    protected function getQuicklink($limit = null, $offset = null)
    {
        $result = array();
        $model  = $this->getModel('quicklink');
        $where  = array(
            'active'      => 1,
            'display <>?' => 0,
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

    /**
     * Get compound name by id
     *
     * @param string $compoundId
     * @return string
     */
    protected function getCompoundName($compoundId = '')
    {
        $compound = '';
        if (!$compoundId) {
            return $compound;
        }

        $model = $this->getModel('display_group');
        $row   = $model->find($compoundId, 'id');

        return $row ? $row['compound'] : '';

    }
}