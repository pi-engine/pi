<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Comment\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;

class CategoryController extends ActionController
{
    /**
     * Comment categories
     */
    public function indexAction()
    {
        $title = _a('Comment categories');

        $modulelist = Pi::registry('modulelist')->read('active');
        $rowset = Pi::model('category', 'comment')->select(array(
            'module'    => array_keys($modulelist),
        ));
        $categories = array();
        foreach ($rowset as $row) {
            $category = $row['name'];
            $categories[$row['module']][$category] = array(
                'title'     => $row['title'],
                'active'    => $row['active'],
                'status'    => $row['active'] ? _a('Active') : _a('Disabled'),
                'url'       => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'module',
                    'name'          => $row['module'],
                    'category'      => $category,
                )),
                'enable'    => array(
                    'title' => $row['active'] ? _a('Disable') : _a('Enable'),
                    'url'   => $this->url('', array(
                        'controller'    => 'category',
                        'action'        => 'enable',
                        'id'            => $row['id'],
                        'flag'          => $row['active'] ? 0 : 1,
                    )),
                ),
            );
        }
        $modules = array();
        foreach ($modulelist as $name => $data) {
            if (!isset($categories[$name])) {
                continue;
            }
            $modules[$name] = array(
                'title'         => $data['title'],
                'url'           => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'module',
                    'name'          => $name,
                )),
                'categories'    => $categories[$name],
            );
        }

        //d($modules);
        $this->view()->assign(array(
            'title'     => $title,
            'modules'   => $modules,
        ));

        $this->view()->setTemplate('comment-category');
    }


    /**
     * Enable/disable a category
     *
     * @return bool
     */
    public function enableAction()
    {
        $id = _get('id', 'int') ?: 1;
        $flag = _get('flag', 'int');
        $return = _get('return');

        $row = Pi::model('category', 'comment')->find($id);
        if (!$row) {
            $status = -1;
            $message = _a('Category was not found.');
        } else {
            if ($flag == (int) $row['active']) {
                $status = 0;
                $message = _a('Invalid operation.');
            } else {
                $row['active'] = $flag;
                try {
                    $row->save();
                    $status = 1;
                    $message = _a('Operation succeeded.');
                } catch (\Exception $e) {
                    $status = 0;
                    $message = _a('Operation failed.');
                }
            }
        }

        if (!$return) {
            $this->jump(array('action' => 'index'), $message);
        } else {
            $result = array(
                'status'    => (int) $status,
                'message'   => $message,
                'data'      => $id,
            );

            return $result;
        }
    }
}
