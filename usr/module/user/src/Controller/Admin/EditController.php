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
        $fields = $this->getFields();
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

        $this->view()->assign(array(
            'form' => $form,
        ));

    }

    /**
     * Edit compound
     */
    public function editCompoundAction()
    {
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
        $result    = array();
        $compounds = $this->getFields('compound');

        foreach ($compounds as $compound) {
            $link = $this->url(
                '',
                array(
                    'controller' => 'edit',
                    'action'     => 'edit.compound',
                    'compound'   => $compound['name']
                )
            );
            $result[] = array(
                'title' => $compound['title'],
                'name'  => $compound['name'],
                'link'  => $link,
            );
        }

        return $result;

    }

}