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
 * User manage cases controller
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
                $compounds[$field['name']] = array();
            } else {
                $profile[$field['name']] = $field;
            }
        }

        foreach ($compounds as $name => &$compound) {
            $compound = Pi::registry('compound', 'user')->read($name);
        }

        vd($profile);
        vd($compounds);

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
                    'title'  => $field['title'],
                    'module' => $field['module'],
                );
            } else {
                $profile[$field['name']] = array(
                    'name' => $field['name'],
                    'module' => $field['module'],
                    'title'  => $field['title'],

                );
            }
        }

        // Get compound
        foreach ($compounds as $name => &$compound) {
            $compoundMeta = Pi::registry('compound', 'user')->read($name);
            foreach ($compoundMeta as $meta) {
                $compound['fields'] = array(
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

        if (!$data) {
            return $result;
        }



        //$data = ;




    }

    public function privacyAction()
    {

    }

    public function testAction()
    {
        $this->view()->setTemplate(false);
        d($this->getGroupDisplay());
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

    protected function canonizeDressUp($data)
    {
        $displayGroup = array();
        $displayField = array();

        // Set group
        foreach ($data as $group) {
            $displayGroup[] = array(
                'title'    => $group['title'],
                'order'    => $group['order'],
                'compound' => $group['compound'],
            );
            foreach ($group['fields'] as $field) {
                $displayField[] = array(
                    'field' => $field['name'],
                    'group' => $group['id'],
                    'order' => $field['order'],
                );
            }
        }

        return array($displayGroup, $displayField);

    }

}
