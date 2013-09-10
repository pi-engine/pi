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
     * User profile for owner
     *
     * @return array|void
     */
    public function indexAction()
    {
        $uid = Pi::user()->hasIdentity();

        if (!$uid) {
            $this->jump(
                array('', array('controller' => 'login', 'action' => 'index')),
                __('Please login'),
                5
            );
            return;
        }
        $uid = Pi::user()->getIdentity();

        // Get user information
        $user = $this->getUser($uid);

        // Get display group
        $profileGroup = $this->getProfile($uid, 'display');

        // Get activity meta for nav display
        $nav = $this->getNav($uid, 'profile');

        // Get quicklink
        $quicklink = $this->getQuicklink();

        $this->view()->assign(array(
            'profile_group' => $profileGroup,
            'uid'           => $uid,
            'user'          => $user,
            'nav'           => $nav,
            'quicklink'     => $quicklink,
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
        $profileGroup = $this->getProfile($uid, 'display');

        // Get activity meta for nav display
        $nav = $this->getNav($uid, 'profile');

        // Get quicklink
        $quicklink = $this->getQuicklink();

        $this->view()->assign(array(
            'profile_group' => $profileGroup,
            'uid'           => $uid,
            'user'          => $user,
            'nav'           => $nav,
            'quicklink'     => $quicklink,
        ));

        $this->view()->setTemplate('profile-view');

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
            $uid = $loginUid;
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
        $nav = $this->getNav($uid, 'homepage');

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
            'is_owner'      => $isOwner,
            'quicklink'    => $quicklink,
            'nav'          => $nav,
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
        $groupId   = $this->params('group', '');
        $status = 0;
        $isPost = 0;

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
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new ProfileEditFilter($filters));
            if ($form->isValid()) {
                $data = $form->getData();
                // Update user
                Pi::api('user', 'user')->updateUser($uid, $data);
                $status = 1;
            }

            $isPost = 1;
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
            'form'      => $form,
            'title'     => $groups[$groupId]['title'],
            'groups'    => $groups,
            'cur_group' => $groupId,
            'status'    => $status,
            'is_post'   => $isPost,
            'user'      => $this->getUser($uid)
        ));
        $this->view()->setTemplate('profile-edit');
    }

    /**
     * Edit compound action
     */
    public function editCompoundAction()
    {
        $groupId      = $this->params('group', '');
        $uid          = Pi::service('user')->getIdentity();
        $errorMsg     = '';

        // Redirect login page if not logged in
        if (!$uid) {
            $this->jump(
                'user',
                array('controller' => 'login', 'action' => 'index'),
                __('Need login'),
                2
            );
        }

        if ($this->request->isPost()) {
            $groupId = _post('group');
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
                'group' => $groupId,
                'uid'   => $uid,
            );

            $forms[$set]->setData($row);
            $i++;
        }

        // New compound form
        $addForm = new CompoundForm('new-compound', $compoundElements);
        $addForm->setData(array(
            'set'   => $i,
            'group' => $groupId,
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
            'forms'     => $forms,
            'error_msg' => $errorMsg,
            'cur_group' => $groupId,
            'title'     => $groups[$groupId]['title'],
            'groups'    => $groups,
            'add_form'  => $addForm,
            'user'      => $this->getUser($uid)
        ));
    }

    /**
     * Edit compound order
     * For ajax
     * @return array
     */
    public function editCompoundSetAction()
    {
        $compoundId = _post('compound');
        $row   = $this->getModel('display_group')->find($compoundId, 'id');
        $compound = $row ? $row->compound : '';
        $set        = _post('set');
        $uid        = Pi::user()->getIdentity();
        $message    = array(
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
        $uid      = Pi::user()->getIdentity();
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
     * Add compound item
     *
     * @return array
     */
    public function addCompoundItemAction()
    {
        $uid      = Pi::user()->getIdentity();
        $compound = _post('group', '');

        if (!$uid || !$compound) {
            return array(
                'status'  => 0,
                'message' => 'error',
            );
        }

        $compoundField = Pi::registry('profile', 'user')->read('compound');
        if (!isset($compoundField[$compound])) {
            return array(
                'status'  => 0,
                'message' => 'compound name invalid',
            );
        }

        // Get compound element for edit
        $compoundMeta     = Pi::registry('compound', 'user')->read($compound);
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

                return array(
                    'status'  => $status ? 1 : 0,
                    'message' => $status ? 'ok' : 'error',
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
//        $paginator = Paginator::factory(intval($option['count']));
//        $paginator->setItemCountPerPage($option['limit']);
//        $paginator->setCurrentPageNumber($option['page']);
//        $paginator->setUrlOptions(array(
//            // Use router to build URL for each page
//            'pageParam'     => 'p',
//            'totalParam'    => 't',
//            'router'        => $this->getEvent()->getRouter(),
//            'route'         => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
//            'params'        => array(
//                'module'        => $this->getModule(),
//                'controller'    => $option['controller'],
//                'action'        => $option['action'],
//                'uid'           => $option['uid'],
//            ),
//        ));

        $paginator = Paginator::factory(intval($option['count']), array(
            'limit' => $option['limit'],
            'page'  => $option['page'],
            'url_options'   => array(
                'params'    => array(
                    'module'        => $this->getModule(),
                    'controller'    => $option['controller'],
                    'action'        => $option['action'],
                    'uid'           => $option['uid'],
                    'uid'   => $option['uid'],
                ),
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

        $model = $this->getModel('display_field');
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
    protected function getProfile($uid, $type = 'display')
    {
        $result = array();
        if ('display' == $type) {
            // Get account or profile meta
            $fieldMeta = Pi::api('user', 'user')->getMeta('', 'display');
            $groups = $this->getDisplayGroup();

            foreach ($groups as $groupId => $group) {
                $result[$groupId] = $group;
                $result[$groupId]['fields'] = array();
                $fields = $this->getFieldDisplay($groupId);

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
                            $result[$groupId]['fields'][$set][] = array(
                                'title' => $compoundMeta[$key]['title'],
                                'value' => $value,
                            );
                        }
                    }
                } else {
                    // Profile
                    foreach ($fields as $field) {
                        $result[$groupId]['fields'][0][$field] = array(
                            'title' => $fieldMeta[$field]['title'],
                            'value' => Pi::api('user', 'user')->get($uid, $field),
                        );
                    }
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

    /**
     * Set nav form home page profile and activity
     *
     * @param $uid
     * @return array
     */
    protected function getNav($uid, $cur)
    {
        // Get activity list
        $items = array();
        $nav = array(
            'cur'   => $cur,
            'items' => $items,
        );

        if (!$uid) {
            return $nav;
        }

        // Set homepage
        $homepageUrl = $this->url(
            'user',
            array(
                'controller' => 'profile',
                'action'     => 'home',
                'uid'        => $uid
            )
        );
        $items[] = array(
            'title' => __('Homepage'),
            'name'  => 'homepage',
            'url'   => $homepageUrl,
            'icon'  => '',
        );

        // Set profile
        $profileUrl = $this->url(
            'user',
            array(
                'controller' => 'profile',
                'action'     => 'index',
                'uid'        => $uid,
            )
        );
        $items[] = array(
            'title' => __('Profile'),
            'name'  => 'profile',
            'url'   => $profileUrl,
            'icon'  => '',
        );

        // Set activity
        $activityList = Pi::api('user', 'activity')->getList();
        foreach ($activityList as $key => $value) {
            $url = $this->url(
                'user',
                array(
                    'controller' => 'activity',
                    'action'     => 'index',
                    'uid'        => $uid,
                    'name'       => $key,
                )
            );
            $items[] = array(
                'title' => $value['title'],
                'name'  => $key,
                'icon'  => $value['icon'],
                'url'   => $url,
            );
        }

        $nav['items'] = $items;

        return $nav;

    }

    public function testAction()
    {
        //$compoundMeta = Pi::api('user', 'user')->getMeta('compound');
        //vd($compoundMeta);
        //$compoundElements = Pi::api('user', 'form')->getCompoundElement('address');

        //vd($compoundElements);
        //$compoundElements = Pi::api('user', 'form')->getCompoundFilter('address');
        //vd($compoundElements);
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
        //vd(Pi::registry('compound', 'user')->read('work'));
        //vd($this->getQuicklink());
        //$this->getNav(1, 'profile');
        //vd($this->url('', array('action' => 'press')));
        $this->view()->setTemplate(false);
    }
}