<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\User\Form\ProfileEditForm;
use Module\User\Form\ProfileEditFilter;
use Module\User\Form\CompoundForm;
use Module\User\Form\CompoundFilter;
//use Pi\Paginator\Paginator;

/**
 * Profile controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
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
        Pi::service('authentication')->requireLogin();
        Pi::api('profile', 'user')->requireComplete();
        $uid = Pi::user()->getId();

        // Get display group
        $groups = $this->getProfile($uid);

        $this->view()->assign(array(
            'groups'        => $groups,
            'name'          => 'profile',
            'uid'           => $uid,
            'owner'         => true,
        ));

        $this->view()->setTemplate('profile-index');

        $this->view()->headTitle(__('User profile'));
        $this->view()->headdescription(__('view profile'), 'set');
        $this->view()->headkeywords($this->config('head_keywords'), 'set');
    }

    /**
     * Profile for view
     *
     */
    public function viewAction()
    {
        $uid    = $this->params('uid', '');
        $groups = $this->getProfile($uid);

        // Get viewer level: everyone, member, follower, following, owner
        $requestId = $uid == Pi::user()->getId() ? 0 : null;
        $level = Pi::api('privacy', 'user')->getLevel($uid, $requestId);

        // Filter field according to privacy setting
        $groups = Pi::api('privacy', 'user')->filterProfile(
            $uid,
            $level,
            $groups,
            'group'
        );
        $this->view()->assign(array(
            'groups'        => $groups,
            'name'          => 'profile',
            'uid'           => $uid,
            'owner'         => false,
        ));

        $this->view()->setTemplate('profile-view');

        $this->view()->headTitle(__('User profile'));
        $this->view()->headdescription(__('view profile'), 'set');
        $this->view()->headkeywords($this->config('head_keywords'), 'set');
    }

    /**
     * Edit profile action
     * Task:
     * 1. Receive profile group name
     * 2. According to group name construct form
     * 3. Process form submit info
     * 4. Update user profile info
     */
    public function editProfileAction()
    {
        Pi::service('authentication')->requireLogin();
        Pi::api('profile', 'user')->requireComplete();
        $uid = Pi::user()->getId();

        $groupId    = $this->params('group', '');
        $result     = array(
            'status'  => 0,
            'message' => '',
        );
        if (!$groupId) {
            return $this->jump(array(
                'controller' => 'profile',
                'action'     => 'index'
            ), __('Profile group ID is invalid.'), 'error');
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
        //d($filters);
        $form = new ProfileEditForm('profile', $fields);
        $form->setAttributes(array(
            'action' => $this->url('', array(
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
                Pi::service('event')->trigger('user_update', $uid);
                $result['status']  = 1;
                $result['message'] = __('Profile updated successfully.');
            } else {
                $result['message'] = __('Profile update failed.');
            }

            $this->view()->assign('result', $result);
        } else {
            // Get profile data
            $fields = Pi::registry('display_field', 'user')->read($groupId);
            $profileData = Pi::api('user', 'user')->get($uid, $fields);
            // Set user info to form
            $form->setData($profileData);
        }

        $group = Pi::registry('display_group', 'user')->read($groupId);
        $this->view()->assign(array(
            'form'      => $form,
            'title'     => $group['title'],
            'group_id'  => $groupId,
        ));
        $this->view()->setTemplate('profile-edit');

        $this->view()->headTitle(__('Edit profile'));
        $this->view()->headdescription(__('Edit profile data.'), 'set');
        $this->view()->headkeywords($this->config('head_keywords'), 'set');
    }

    /**
     * Edit compound
     *
     */
    public function editCompoundAction()
    {
        Pi::service('authentication')->requireLogin();
        Pi::api('profile', 'user')->requireComplete();
        $uid = Pi::user()->getId();

        $groupId = $this->params('group', '');
        // Get compound name
        $group = Pi::registry('display_group', 'user')->read($groupId);
        $compound = $group ? $group['compound'] : '';

        if (!$groupId || !$compound) {
            return $this->jump(array(
                'controller' => 'profile',
                'action'     => 'index'
            ), __('Profile group ID is invalid.'), 'error');
        }

        // Get compound element for edit
        $compoundElements = Pi::api('form', 'user')->getCompoundElement($compound);
        $form = new CompoundForm('new-compound', $compoundElements);
        $form->setData(array('group' => $groupId));

        $profileGroup   = $this->getCompound($uid, $groupId);
        $compounds      = array();
        foreach ($profileGroup as $key => $value) {
            $compounds[$key]['set']    = $key;
            $compounds[$key]['fields'] = $value;
        }
        $group = Pi::registry('display_group', 'user')->read($groupId);
        $this->view()->assign(array(
            'compounds' => $compounds,
            'group_id'  => $groupId,
            'title'     => $group['title'],
            'form'      => $form,
        ));

        $this->view()->setTemplate('profile-edit-compound');

        $this->view()->headTitle(__('Edit profile'));
        $this->view()->headdescription(__('Edit profile data.'), 'set');
        $this->view()->headkeywords($this->config('head_keywords'), 'set');
    }

    /**
     * Get edit compound form
     *
     * @return $this|array
     */
    public function compoundFormAction()
    {
        Pi::service('authentication')->requireLogin();
        $uid     = Pi::user()->getId();
        $groupId = _get('groupId');
        $set     = _get('set', '');
        $set     = $set !== '' ? $set : _get('order');
   
        // Get compound name
        $group = Pi::registry('display_group', 'user')->read($groupId);
        $compound = $group ? $group['compound'] : '';

        if (!$groupId || !$compound) {
            return $this->jump(array(
                'controller' => 'profile',
                'action'     => 'index'
            ), __('Profile group ID is invalid.'), 'error');
        }

        $compoundElements   = Pi::api('form', 'user')->getCompoundElement($compound);
        $compoundFilters    = Pi::api('form', 'user')->getCompoundFilter($compound);
        $compoundData       = Pi::api('user', 'user')->get($uid, $compound);

        $form = new CompoundForm('new-compound', $compoundElements);
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
                Pi::service('event')->trigger('user_update', $uid);
                $profileGroup = $this->getCompound($uid, $groupId);
                $compounds = array();
                foreach ($profileGroup as $key => $value) {
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
        $this->view()->setTemplate('system:component/form');
    }

    /**
     * Edit compound order
     * For ajax
     *
     * @return array
     */
    public function editCompoundSetAction()
    {
        Pi::service('authentication')->requireLogin();
        $uid        = Pi::user()->getId();
        $groupId    = _post('groupId');
        $set        = _post('set');
        $set        = $set !== '' ? $set : _post('order');
        $result     = array(
            'status' => 0,
        );

        $order = explode(',', $set);
        if (!$order || !$uid) {
            return $result;
        }

        // Get compound name
        $group = Pi::registry('display_group', 'user')->read($groupId);
        $compound = $group ? $group['compound'] : '';
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
        Pi::service('event')->trigger('user_update', $uid);
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

        Pi::service('authentication')->requireLogin();
        $uid     = Pi::user()->getId();
        $groupId = _post('groupId', '');
        $set     = _post('set');

        $group = Pi::registry('display_group', 'user')->read($groupId);
        $compound = $group ? $group['compound'] : '';
        if (!$compound) {
            $result['message'] = __('Profile group ID is invalid.');
            return $result;
        }

        $oldCompound = Pi::api('user', 'user')->get($uid, $compound);
        $newCompound = array();
        foreach ($oldCompound as $key => $value) {
            if ($set != $key ) {
                $newCompound[] = $value;
            }
        }

        $meta = Pi::registry('field', 'user')->read('compound');
        // Update compound
        if (empty($newCompound)
            && isset($meta[$compound])
            && $meta[$compound]['is_required']
        ) {
            $result['message'] = __('Profile compound requires at least one set of data.');
        } else {
            $status = Pi::api('user', 'user')->set($uid, $compound, $newCompound);
            Pi::service('event')->trigger('user_update', $uid);
            $result['status']  = $status;
            $result['message'] = $status ? '' : __('Profile compound data was not deleted.');
        }

        return $result;

    }

    /**
     * Add compound item
     *
     * @return array
     */
    public function addCompoundItemAction()
    {
        Pi::service('authentication')->requireLogin();
        $uid = Pi::user()->getId();
        $groupId = _post('group', '');

        if (!$uid || !$groupId) {
            return array(
                'status'  => 0,
                'message' => __('User ID or Profile group ID is invalid.'),
            );
        }

        // Get compound name
        $group      = Pi::registry('display_group', 'user')->read($groupId);
        $compound   = $group['compound'];

        // Get compound element for edit
        $compoundMeta     = Pi::registry('compound_field', 'user')->read($compound);
        $compoundElements = Pi::api('form', 'user')->getCompoundElement($compound);
        $compoundFilters  = Pi::api('form', 'user')->getCompoundFilter($compound);
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
                Pi::service('event')->trigger('user_update', $uid);

                $profileGroup = $this->getCompound($uid, $groupId);
                $compounds = array();
                foreach ($profileGroup as $key => $value) {
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
     * Get display group elements for edit
     *
     * @param int $groupId
     *
     * @return array
     */
    protected function getGroupElements($groupId)
    {
        $meta        = Pi::registry('field', 'user')->read('', 'edit');
        $fields = Pi::registry('display_field', 'user')->read($groupId);
        $elements = array();
        $filters  = array();

        foreach ($fields as $field) {
            if (!isset($meta[$field])) {
                continue;
            }
            $element    = Pi::api('form', 'user')->getElement($field);
            $filter     = Pi::api('form', 'user')->getFilter($field);
            if ($element) {
                $elements[] = $element;
            }
            if ($filter) {
                $filters[] = $filter;
            }
        }

        return array($elements, $filters);

    }

    /**
     * Get user grouped profile data
     * Group and group items title and value
     *
     * @param int $uid User id
     *
     * @return array
     */
    protected function getProfile($uid)
    {
        $filter     = true;

        $groups     = Pi::registry('display_group', 'user')->read();
        $meta       = Pi::api('user', 'user')->getMeta('', 'display');
        $fields     = Pi::registry('display_field', 'user')->read();
        $profile    = Pi::user()->get($uid, $fields, $filter);
        array_walk($groups, function (&$group, $gid) use ($profile, $meta, $uid) {
            if (!$group['compound']) {
                $fields = Pi::registry('display_field', 'user')->read($gid);
                $list   = array();
                foreach ($fields as $field) {
                    $list[$field] = array(
                        'title' => $meta[$field]['title'],
                        'value' => isset($profile[$field]) ? $profile[$field] : '',
                    );
                }
                $group['fields'] = array($list);
            } else {
                $group['fields'] = Pi::api('compound', 'user')->display($uid, $group['compound']);
            }
        });

        return $groups;
    }

    /**
     * Get user compound profile data
     *
     * @param int $uid User id
     * @param int $gid Group ID
     * @param bool $filter
     *
     * @return array
     */
    protected function getCompound($uid, $gid, $filter = true)
    {
        $result = array();

        $group      = Pi::registry('display_group', 'user')->read($gid);
        $fields     = Pi::registry('display_field', 'user')->read($gid);
        $name       = $group['compound'];
        $compound   = Pi::api('user', 'user')->get($uid, $name, $filter);
        $compoundMeta = Pi::registry('compound_field', 'user')->read($name);
        foreach ($compound as $set => $item) {
            $compoundValue = array();
            foreach ($fields as $field) {
                $compoundValue[] = array(
                    'title' => $compoundMeta[$field]['title'],
                    'value' => $item[$field],
                );
            }
            $result[$set] = $compoundValue;
        }

        return $result;
    }
}