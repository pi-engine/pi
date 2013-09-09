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

        foreach ($compounds as $name => &$compound) {
            $compound = Pi::registry('compound', 'user')->read($name);
        }




        //vd($profile);
        //vd($compounds);

        $this->view()->assign(array(
            'profile'   => $profile,
            'compounds' => $compounds,
        ));

    }

    public function privacyAction()
    {

    }

    public function testAction()
    {
        $this->view()->setTemplate(false);
        vd($this->getGroupDisplay());
    }

    protected function getGroupDisplay()
    {
        $result = array();
        $groupModel = $this->getModel('display_group');
        $select = $groupModel->select(array());
        $rowset = $groupModel->selectWith($select);

        foreach ($rowset as $row) {
            $result[$row['name']] = array(
                'name'     => $row['name'],
                'title'    => $row['title'],
                'compound' => $row['compound'],
            );

            $displayFieldModel = $this->getModel('display_filed');
            $select = $displayFieldModel->select(array('group' => $row['name']));
            $displayFieldRowset = $displayFieldModel->selectWith($select);

            $fields = array();
            foreach ($displayFieldRowset as $field) {
                $fields[$field['field']] = array();
            }
            if ($row['compound']) {
                $compoundMeta = Pi::registry('compound', 'user')->read(
                    $row['compound']
                );

                foreach ($compoundMeta as $name => $meta) {
                    if (isset($fields[$name])) {
                        $fields[$name] = array(
                            'name'
                        );
                    }
                }
            }
        }
    }

}
