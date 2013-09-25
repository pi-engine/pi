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

/**
 * Profile controller
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class ProfileController extends ActionController
{
    public function indexAction()
    {
        $fields = Pi::registry('profile', 'user')->read();

        foreach ($fields as $field) {
            if ($field['type'] == 'compound') {
                $compounds[$field['name']] = array(
                    'name'   => $field['name'],
                    'title'      => $field['title'],
                    'module'     => $field['module'],
                    'is_edit'    => $field['is_edit'],
                    'is_search'  => $field['is_search'],
                    'is_display' => $field['is_display'],

                );
            } else {
                $profile[$field['name']] = array(
                    'name'       => $field['name'],
                    'title'      => $field['title'],
                    'module'     => $field['module'],
                    'is_edit'    => $field['is_edit'],
                    'is_search'  => $field['is_search'],
                    'is_display' => $field['is_display'],
                );
            }
        }

        // Get compound
        foreach ($compounds as $name => &$compound) {
            $compoundMeta = Pi::registry('compound', 'user')->read($name);
            foreach ($compoundMeta as $meta) {
                $compound['fields'][] = array(
                    'name'  => $meta['name'],
                    'title' => $meta['title'],
                );
            }
        }

        $compounds = array_values($compounds);
        $profile   = array_values($profile);
        d($compounds);
        d($profile);

        $this->view()->assign(array(
            'profile'   => $profile,
            'compounds' => $compounds,
        ));
    }

    /**
     * Profile field dress up
     */
    public function dressUpAction()
    {
        $fields = Pi::registry('profile', 'user')->read();

        foreach ($fields as $field) {
            if ($field['type'] == 'compound') {
                $compounds[$field['name']] = array(
                    'name'   => $field['name'],
                    'title'  => $field['title'],
                    'module' => $field['module'],
                );
            } else {
                $profile[$field['name']] = array(
                    'name'   => $field['name'],
                    'module' => $field['module'],
                    'title'  => $field['title'],

                );
            }
        }

        // Get compound
        foreach ($compounds as $name => &$compound) {
            $compoundMeta = Pi::registry('compound', 'user')->read($name);
            foreach ($compoundMeta as $meta) {
                $compound['fields'][] = array(
                    'name'  => $meta['name'],
                    'title' => $meta['title'],
                );
            }
        }

        $data = $this->getGroupDisplay();

        // Canonize right display
        foreach ($data as  $group) {
            // Compound fields
            if ($group['compound']) {
                if (isset($compounds[$group['compound']])) {
                    unset($compounds[$group['compound']]);
                }

            } else {
                // Profile fields
                foreach (array_keys($group['fields']) as $key) {
                    if (isset($profile[$key])) {
                        unset($profile[$key]);
                    }
                }
            }
        }

        $this->view()->assign(array(
            'profile'   => $profile,
            'compounds' => $compounds,
            'data'      => $data,
        ));

        d($profile);
        d($compounds);
        d($data);
        $this->view()->setTemplate('profile-dress-up');
    }

    /**
     * Save display for ajax
     *
     */
    public function saveDressUpAction()
    {
        $result = array(
            'status' => 0,
        );
        $data = _post('data');

        $displayGroupModel = $this->getModel('display_group');
        $displayFieldModel = $this->getModel('display_field');

	    // Flush
        $displayGroupModel->delete(array());
        $displayFieldModel->delete(array());

        $groupOrder = 1;
	    foreach ($data as $group) {
            $groupData = array(
            	'title'    => $group['title'],
                'order'    => $groupOrder,
                'compound' => $group['compound'],
            );

            $row = $displayGroupModel->createRow($groupData);
            
            try {
                $row->save();
            } catch (\Exception $e) {
                return $result;
            }

            $groupId = (int) $row['id'];d($groupId);
            $fieldOrder = 1;
            // Save display field
            foreach ($group['fields'] as $field )  {
                $fieldData = array(
                    'field'  => $field['name'],
                    'group'  => $groupId,
                    'order'  => $fieldOrder,
                );

                $rowField = $displayFieldModel->createRow($fieldData);

                try {
                    $rowField->save();
                } catch (\Exception $e) {
                    return $result;
                }
                $fieldOrder++;
	        }

            $groupOrder++;
        }

        $result['status'] = 1;

        return $result;

    }

    /**
     * Update field meta
     * For ajax
     *
     */
    public function updateFieldAction()
    {
        $this->view()->setTemplate(false);
        $result = array(
            'status' => 0
        );

        $name          = _post('name');
        $compound      = _post('compound');
        $title         = _post('title');

        $fieldModel    = $this->getModel('field');
        $compoundModel = $this->getModel('compound_field');

        if (!$name || !$title) {
            return $result;
        }

        // Update field
        if (!$compound) {
            $row = $fieldModel->find($name, 'name');
            if ($row) {
                $row->assign(array('title' => $title));
                try {
                    $row->save();
                    $result['status'] = 1;
                } catch (\Exception $e) {
                    return $result;
                }
            }

        } else {
            // Update compound field title
            $select = $compoundModel->select()->where(array(
                'compound' => $compound,
                'name'     => $name,
            ));

            $rowset = $compoundModel->selectWith($select)->current();
            if ($rowset) {
                $compoundModel->update(
                    array('title' => $title),
                    array('id'    => $rowset['id'])
                );
                $result['status'] = 1;
            }

        }

        // Flush
        Pi::registry('compound', 'user')->flush();
        Pi::registry('profile', 'user')->flush();

        return $result;

    }

    /**
     * Privacy manage
     */
    public function privacyAction()
    {
        // Get display fields
        $privacyModel = $this->getModel('privacy');
        $select = $privacyModel->select()->where(array());
        $rowset = $privacyModel->selectWith($select);

        foreach ($rowset as $row) {
            $privacy[] = $row->toArray();
        }

        return $privacy;
    }

    public function setPrivacyAction()
    {
        $id       = _post('id');
        $value    = _post('value');
        $isForced = _post('is_forced');

        $status = array(
            'status' => 0,
            'message' => __('Set privacy failed')
        );

        if (!$id) {
            return $status;
        }

        if (!in_array($value, array('0', '1', '2', '4', '255'))) {
            return $status;
        }

        if ($isForced != '0' && $isForced != '1') {
            return $status;
        }

        $model = $this->getModel('privacy');
        $row = $model->find($id, 'id');

        if (!$row) {
            return $status;
        }

        $row->assign(array(
            'value'     => $value,
            'is_forced' => $isForced,
        ));
        try {
            $row->save();
            $status['status'] = 1;
            $status['message'] = __('Set privacy successfully');
        } catch (\Exception $e) {
            return $status;
        }

        return $status;
    }

    public function testAction()
    {
        $this->view()->setTemplate(false);

//        $data = _post('data');
//        $data = $this->getGroupDisplay();
//        d($data);
//        list($displayGroup, $displayFiled) = $this->canonizeDressUp($data);
//        d($displayFiled);
//        d($displayGroup);
        //$data = $this->getGroupDisplay();
        //d($this->canonizeDressUp($data));

//        $displayGroupModel = $this->getModel('display_group');
//        $displayFiledModel = $this->getModel('display_field');

        // Flush
//        $displayGroupModel->delete(array());
//        $displayFiledModel->delete(array());
    }

    /**
     * Get display group and display fields
     *
     * @return array
     */
    protected function getGroupDisplay()
    {
        $profileMeta = Pi::registry('profile', 'user')->read();
        $result = array();
        $groupModel = $this->getModel('display_group');
        $select = $groupModel->select()->where(array());
        $select->order('order');
        $rowset = $groupModel->selectWith($select);


        foreach ($rowset as $row) {
            $result[$row['id']] = array(
                'id'       => $row['id'],
                'title'    => $row['title'],
                'compound' => $row['compound'],
            );

            $displayFieldModel = $this->getModel('display_field');
            $select = $displayFieldModel->select()->where(array('group' => $row['id']));
            $select->order('order');
            $displayFieldRowset = $displayFieldModel->selectWith($select);

            $fields = array();
            foreach ($displayFieldRowset as $field) {
                $fields[$field['field']] = array();
            }

            if ($row['compound']) {
                $compoundMeta = Pi::registry('compound', 'user')->read(
                    $row['compound']
                );
                $result[$row['id']]['module'] = $profileMeta[$row['compound']]['module'];

                foreach ($compoundMeta as $name => $meta) {
                    if (isset($fields[$name])) {
                        $fields[$name]['name']  = $name;
                        $fields[$name]['title'] = $meta['title'];
                    }
                }
            } else {
                foreach ($profileMeta as $name => $meta) {
                    if (isset($fields[$name])) {
                        $fields[$name]['name']   = $name;
                        $fields[$name]['title']  = $meta['title'];
                        $fields[$name]['module'] = $meta['module'];
                    }
                }
            }

            $result[$row['id']]['fields'] = $fields;
        }

        $result = array_values($result);
        foreach ($result as &$row) {
            $row['fields'] = array_values($row['fields']);
        }

        return $result;

    }
}
