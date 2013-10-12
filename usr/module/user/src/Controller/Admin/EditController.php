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
use Module\User\Form\ProfileEditForm;
use Module\User\Form\ProfileEditFilter;
use Module\User\Form\CompoundForm;
use Module\User\Form\CompoundFilter;

/**
 * Edit user controller
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class EditController extends ActionController
{
    /**
     * Edit base information
     *
     * @return array|void
     */
    public function indexAction()
    {
        $uid = _get('uid');
        if (!$uid) {
            return $this->jumpTo404('Invalid uid');
        }

        // Get compound nav
        $compoundNav = $this->getCompoundNav();

        // Get edit form
        $fields   = $this->getFields();
        $elements = array();
        $filters  = array();
        foreach ($fields as $field) {
            $element = Pi::api('user', 'form')->getElement($field);
            $filter  = Pi::api('user', 'form')->getFilter($field);

            if ($element) {
                $elements[] = $element;
            }
            if ($filter) {
                $filters[] = $filter;
            }
        }

        $elements[] =  array(
            'name'  => 'uid',
            'type'  => 'hidden',
            'attributes' => array(
                'value' => $uid,
            ),
        );

        $form = new ProfileEditForm('base', $elements);
        $data = Pi::api('user', 'user')->get($uid, $fields);
        if (isset($data['credential'])) {
            unset($data['credential']);
        }

        $form->setData($data);
        vd($form);
        vd($compoundNav);

    }


    /**
     * Update base info
     *
     * @return array
     */
    public function updateBaseInfoAction()
    {
        $result = array(
            'status'  => 0,
            'message' => __('Update failed'),
        );
        $post = $this->params()->fromPost();

        $uid = $post['uid'];
        if (!$uid) {
            return $result;
        }

        foreach ($post as $col => $val) {
            if (is_array($val)) {
                $data[$col] = implode('-', array_values($val));
            } else {
                $data[$col] = $val;
            }
        }

        $status = Pi::api('user', 'user')->updateUser($uid, $data);
        if ($status) {
            $result['status'] = 1;
            $result['message'] = __('Update successfully');
        }

        return $result;

    }

    /**
     * Edit compound
     */
    public function editCompoundAction()
    {
        $uid      = _get('uid');
        $compound = _get('compound');
        if (!$uid || !$compound) {
            return $this->jumpTo404('Invalid uid');
        }

        // Get compound title
        $row = $this->getModel('display_group')->find($compound, 'compound');
        if (!$row) {
            return $this->jumpTo404('Invalid compound');
        }
        $title = $row->title;


        // Get compound element for edit
        $compoundElements = Pi::api('user', 'form')->getCompoundElement($compound);
        $compoundFilters  = Pi::api('user', 'form')->getCompoundFilter($compound);

        // Get user compound
        $compoundData = Pi::api('user', 'user')->get($uid, $compound);
        // Generate compound edit form
        $forms = array();
        foreach ($compoundData as $set => $row) {
            $formName    = 'compound' . $set;
            $forms[$set] = new CompoundForm($formName, $compoundElements);
            // Set form data
            $row += array(
                'set'   => $set,
                'uid'   => $uid,
            );

            $forms[$set]->setData($row);
        }

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

        // Get compound nav
        $compoundNav = $this->getCompoundNav();

        vd($forms);
        vd($title);
        vd($compoundNav);

    }

    /**
     * Edit compound order
     * For ajax
     * @return array
     */
    public function editCompoundSetAction()
    {
        $compound   = _post('compound');
        $set        = _post('set');
        $uid        = _post('uid');
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
     * Get field name of system
     * Default return base field
     * @param string $compound
     * @return array
     */
    protected function getFields($compound = '')
    {
        $model = $this->getModel('field');
        $where = array(
            'active'  => 1,
            'is_edit' => 1,
        );
        if ($compound) {
            $where['type'] = 'compound';
        } else {
            $where['type <> ?'] = 'compound';
        }
        $select = $model->select()->where($where);
        $select->columns(array('name', 'title'));
        $rowset = $model->selectWith($select);

        $result = array();
        foreach ($rowset as $row) {
            if ($compound) {
                $result[] = array(
                    'name'  => $row['name'],
                    'title' => $row['title'],
                );
            } else {
                $result[] = $row['name'];
            }
        }

        return $result;

    }

    /**
     * Get compound nav
     *
     * @return array
     */
    protected function getCompoundNav()
    {
        $result = array();
        $model  = $this->getModel('display_group');
        $select = $model->select()->where(array('compound <> ?' => ''));
        $select->columns(array('id', 'title'));
        $select->order('order');
        $rowset = $model->selectWith($select);

        foreach ($rowset as $row) {
            $result[] = $row->toArray();
        }

        return $result;

    }
}