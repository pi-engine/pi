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

    public function displayAction()
    {
        $fields = Pi::registry('profile', 'user')->read();

        foreach ($fields as $field) {
            if ($field['type'] == 'compound') {
                $compounds[$field['name']] = array();
            } else {
                $profile[$field['name']] = $field;
            }
        }

        // Get compiun
        foreach ($compounds as $name => &$compound) {
            $compound = Pi::registry('compound', 'user')->read($name);
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
                        unset($profile);
                    }
                }
            }
        }

        d($data);
        d($profile);
        d($compounds);

        $this->view()->assign(array(
            'profile'   => $profile,
            'compounds' => $compounds,
            'data'      => $data,
        ));
    }

    /**
     * Save display for ajax
     *
     */
    public function saveDisplayAction()
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
        $result = array();
        $groupModel = $this->getModel('display_group');
        $select = $groupModel->select()->where(array());

        $select->order('order');
        $rowset = $groupModel->selectWith($select);

        foreach ($rowset as $row) {
            $result[$row['name']] = array(
                'name'     => $row['name'],
                'title'    => $row['title'],
                'compound' => $row['compound'],
            );

            $displayFieldModel = $this->getModel('display_field');
            $select = $displayFieldModel->select()->where(array('group' => $row['name']));
            $select->order('order');
            $displayFieldRowset = $displayFieldModel->selectWith($select);

            $fields = array();
            foreach ($displayFieldRowset as $field) {
                $fields[$field['field']] = array();
            }

            $profileMeta = Pi::registry('profile', 'user')->read();
            if ($row['compound']) {
                $compoundMeta = Pi::registry('compound', 'user')->read(
                    $row['compound']
                );

                foreach ($compoundMeta as $name => $meta) {
                    if (isset($fields[$name])) {
                        $fields[$name] = array(
                            'name'  => $name,
                            'title' => $meta['title'],

                        );
                    }
                }
            } else {
                foreach ($profileMeta as $name => $meta) {
                    if (isset($fields[$name])) {
                        $fields[$name] = array(
                            'name' => $name,
                            'title' => $meta['title'],
                        );
                    }
                }
            }

            $result[$row['name']]['fields'] = $fields;
        }

        return $result;

    }

    protected function getNewData()
    {
        //$data =
    }

}
