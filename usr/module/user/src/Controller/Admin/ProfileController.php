<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
        $this->view()->setTemplate('profile');
    }
    
    public function fieldAction()
    {
        $fields = Pi::registry('field', 'user')->read();
        foreach ($fields as $field) {
            if ($field['type'] == 'compound'/* || $field['type'] == 'custom'*/) {
                $compounds[$field['name']] = array(
                    'name'          => $field['name'],
                    'title'         => $field['title'],
                    'module'        => $field['module'],
                    'is_edit'       => $field['is_edit'],
                    'is_search'     => $field['is_search'],
                    'is_display'    => $field['is_display'],
                    'is_required'   => $field['is_required'],
                );
            } else {
                $profile[$field['name']] = array(
                    'name'          => $field['name'],
                    'title'         => $field['title'],
                    'module'        => $field['module'],
                    'is_edit'       => $field['is_edit'],
                    'is_search'     => $field['is_search'],
                    'is_display'    => $field['is_display'],
                    'is_required'   => $field['is_required'],
                );
            }
        }

        // Get compound
        foreach ($compounds as $name => &$compound) {
            $compoundMeta = Pi::registry('compound_field', 'user')->read($name);
            foreach ($compoundMeta as $meta) {
                $compound['fields'][] = array(
                    'name'          => $meta['name'],
                    'title'         => $meta['title'],
                    'is_required'   => $meta['is_required'],
                );
            }
        }

        $compounds = array_values($compounds);
        $profile   = array_values($profile);

        return array(
            'profile'   => $profile,
            'compounds' => $compounds,
        );
    }

    /**
     * Set `required` attribute for a field
     *
     * @return int
     */
    public function requiredAction()
    {
        $required   = $this->params('required') ? 1 : 0;
        $field      = $this->params('field') ?: '';
        $compound   = $this->params('compound') ?: '';

        if (!$field) {
            return 0;
        }
        $result = $required;
        $row    = null;
        if (!$compound) {
            $row = Pi::model('field', 'user')->find($field, 'name');
        } else {
            $rowset = Pi::model('compound_field', 'user')->select(array(
                'compound'  => $compound,
                'name'      => $field,
            ));
            $row = $rowset->current();
        }
        if ($row) {
            $row['is_required'] = $required;
            $row->save();
            $result = (int) $row['is_required'];
        }

        if ($compound) {
            Pi::registry('compound_field', 'user')->flush();
        } else {
            Pi::registry('field', 'user')->flush();
        }

        return array('is_required' => $result);
    }

    /**
     * Profile field dress up
     */
    public function dressUpAction()
    {
        $fields = Pi::registry('field', 'user')->read('', 'display');

        $compounds = array();
        $profile = array();
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
            $compoundMeta = Pi::registry('compound_field', 'user')->read($name);
            foreach ($compoundMeta as $meta) {
                $compound['fields'][] = array(
                    'name'  => $meta['name'],
                    'title' => $meta['title'],
                );
            }
        }

        $displays = $this->getGroupDisplay();

        // Canonize right display
        foreach ($displays as  $group) {
            // Compound fields
            if ($group['name']) {
                if (isset($compounds[$group['name']])) {
                    unset($compounds[$group['name']]);
                }

            } else {
                // Profile fields
                foreach ($group['fields'] as $item) {
                    if (isset($profile[$item['name']])) {
                        unset($profile[$item['name']]);
                    }
                }
            }
        }

        return array(
            'profile'   => array_values($profile),
            'compounds' => array_values($compounds),
            'displays'  => $displays,
        );
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
        $displays = _post('displays');

        $displayGroupModel = $this->getModel('display_group');
        $displayFieldModel = $this->getModel('display_field');

        // Flush
        $displayGroupModel->delete(array());
        $displayFieldModel->delete(array());

        $groupOrder = 1;
        foreach ($displays as $group) {
            $groupData = array(
                'title'    => $group['title'],
                'order'    => $groupOrder,
                'compound' => $group['name'],
            );

            $row = $displayGroupModel->createRow($groupData);
            
            try {
                $row->save();
            } catch (\Exception $e) {
                return $result;
            }

            $groupId = (int) $row['id'];
            $fieldOrder = 1;
            // Save display field
            foreach ($group['fields'] as $field)  {
                if (empty($field['name'])) {
                    continue;
                }
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

        Pi::registry('display_group', 'user')->flush();
        Pi::registry('display_field', 'user')->flush();

        $result['status'] = 1;
        $result['message'] = _a('Profile dress-up data saved successfully.');

        return $result;

    }

    /**
     * Update field meta
     * For ajax
     *
     */
    public function updateFieldAction()
    {
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

            $row = $compoundModel->selectWith($select)->current();
            if ($row) {
                $compoundModel->update(
                    array('title' => $title),
                    array('id'    => $row['id'])
                );
                $result['status'] = 1;
            }

        }

        // Flush
        Pi::registry('compound_field', 'user')->flush();
        Pi::registry('field', 'user')->flush();

        return $result;

    }

    /**
     * Privacy manage
     */
    public function privacyAction()
    {
        $fieldList = Pi::registry('field', 'user')->read('', 'display');
        $privacy = Pi::registry('privacy', 'user')->read();
        $fields = array();
        foreach ($fieldList as $field => $data) {
            $pv = array(
                'field'     => $field,
                'title'     => $data['title'],
                'value'     => 0,
                'is_forced' => 0,
            );
            if (isset($privacy[$field])) {
                $pv['value'] = (int) $privacy[$field]['value'];
                $pv['is_forced'] = (int) $privacy[$field]['is_forced'];
            }
            $fields[] = $pv;
        }

        $levels = Pi::api('privacy', 'user')->getList(array(), true);
        $limits = array();
        foreach ($levels as $value => $label) {
            $limits[] = array(
                'text'  => $label,
                'value' => $value,
            );
        }
        $result = array(
            'fields'    => $fields,
            'limits'    => $limits,
        );

        return $result;
    }

    /**
     * Set field privacy
     *
     * @return array
     */
    public function setPrivacyAction()
    {
        $field    = _post('field');
        $value    = (int) _post('value');
        $isForced = _post('is_forced') ? 1 : 0;

        $result = array(
            'status' => 0,
            'message' => ''
        );
        $module = $this->getModule();

        // Check post data
        $fields = Pi::registry('field', 'user')->read('', 'display');
        if (!$field || !isset($fields[$field])) {
            $result['message'] = _a('Privacy set up failed: invalid field.');
            return $result;
        }

        if (null === Pi::api('privacy', $module)->transform($value)) {
            $result['message'] = _a('Privacy set up failed: invalid privacy.');

            return $result;
        }

        $model = $this->getModel('privacy');
        $row   = $model->find($field, 'field');
        if ($row) {
            $row->assign(array(
                'value'     => $value,
                'is_forced' => $isForced,
            ));
        } else {
            $row = $model->createRow(array(
                'field'     => $field,
                'value'     => $value,
                'is_forced' => $isForced,
            ));
        }
        try {
            $row->save();
        } catch (\Exception $e) {
            $result['message'] = _a('Privacy set up failed: update error.');
            return $result;
        }

        Pi::registry('privacy', $this->getModule())->flush();

        /*
        // Set user privacy field
        $userPrivacyModel = $this->getModel('privacy_user');
        if (!$isForced) {
            $userPrivacyModel->delete(array('field' => $row->field));
        }
        */

        $result['status']  = 1;
        $result['message'] = _a('Privacy set up successfully.');

        return $result;

    }

    /**
     * Get display group and display fields
     *
     * @return array
     */
    protected function getGroupDisplay()
    {
        $profileMeta = Pi::registry('field', 'user')->read();
        $result      = array();
        $groupModel  = $this->getModel('display_group');
        $select      = $groupModel->select()->where(array());
        $select->order('order');
        $rowset = $groupModel->selectWith($select);

        foreach ($rowset as $row) {
            $result[$row['id']] = array(
                'id'       => $row['id'],
                'title'    => $row['title'],
                'name'     => $row['compound'],
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
                $compoundMeta = Pi::registry('compound_field', 'user')->read(
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
