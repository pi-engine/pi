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
        $this->view()->setTemplate('profile');
    }
    
    public function fieldAction()
    {
        //$fields = Pi::registry('profile_field', 'user')->read();
        $fields = $this->getModel('field')->select(
            array(
                'is_display' => 1,
                'is_edit'    => 1,
                'active'     => 1,
            )
        );
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

        return array(
            'profile'   => $profile,
            'compounds' => $compounds,
        );
    }

    /**
     * Profile field dress up
     */
    public function dressUpAction()
    {
//        $fields = Pi::registry('profile_field', 'user')->read();
        $fields = $this->getModel('field')->select(
            array(
                'is_display' => 1,
                'is_edit'    => 1,
                'active'     => 1,
            )
        );

        foreach ($fields as $field) {
            if ($field['is_display']) {
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
            'displays'   => $displays,
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
        $result['message'] = __('Profile dressup data save successfully');

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
        Pi::registry('profile_field', 'user')->flush();

        return $result;

    }

    /**
     * Privacy manage
     */
    public function privacyAction()
    {

        $privacy = Pi::api('user', 'privacy')->getPrivacy();

        return array_values($privacy);
    }

    /**
     * Set field privacy
     *
     * @return array
     */
    public function setPrivacyAction()
    {
        $id       = (int) _post('id');
        $value    = (int) _post('value');
        $isForced = (int) _post('is_forced');

        $result = array(
            'status' => 0,
            'message' => ''
        );

        // Check post data
        if (!$id) {
            $result['message'] = __('Set privacy failed: invalid id.');
            return $result;
        }

        if (!in_array($value, array(0, 1, 2, 4, 255))) {
            $result['message'] = __('Set privacy failed: invalid value.');
            return $result;
        }

        if ($isForced != 0 && $isForced != 1) {
            $result['message'] = __('Set privacy failed: invalid force flag.');
            return $result;
        }

        // Check post id
        $model = $this->getModel('privacy');
        $row   = $model->find($id, 'id');
        if (!$row) {
            return $result;
        }

        // Update privacy setting
        $row->assign(array(
            'value'     => $value,
            'is_forced' => $isForced,
        ));
        try {
            $row->save();
        } catch (\Exception $e) {
            $result['message'] = __('Set privacy failed: update error.');
            return $result;
        }

        // Set user privacy field
        $userPrivacyModel = $this->getModel('privacy_user');
        if (!$isForced) {
            $currentPrivacyValue = $row->value;
            $userPrivacyModel->update(
                array(
                    'value'     => $currentPrivacyValue,
                    'is_forced' => 0,
                ),
                array(
                    'field' => $row->field,
                )
            );
        } else {
            $userPrivacyModel->update(
                array('is_forced' => 1),
                array('field' => $row->field)
            );
        }

        $result['status']  = 1;
        $result['message'] = __('Set privacy successfully');

        return $result;

    }

    /**
     * Get display group and display fields
     *
     * @return array
     */
    protected function getGroupDisplay()
    {
        $profileMeta = Pi::registry('profile_field', 'user')->read();
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
