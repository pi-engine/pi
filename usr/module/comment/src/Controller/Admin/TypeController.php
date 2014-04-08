<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Comment\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;

class TypeController extends ActionController
{
    /**
     * Comment types
     */
    public function indexAction()
    {
        $title = _a('Comment types');

        $modulelist = Pi::registry('modulelist')->read('active');
        $rowset = Pi::model('type', 'comment')->select(array(
            'module'    => array_keys($modulelist),
        ));
        $types = array();
        foreach ($rowset as $row) {
            $type = $row['name'];
            $types[$row['module']][$type] = array(
                'title'     => $row['title'],
                'active'    => $row['active'],
                'status'    => $row['active'] ? _a('Active') : _a('Disabled'),
                'url'       => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'module',
                    'name'          => $row['module'],
                    'type'          => $type,
                )),
                'enable'    => array(
                    'title' => $row['active'] ? _a('Disable') : _a('Enable'),
                    'url'   => $this->url('', array(
                        'controller'    => 'type',
                        'action'        => 'enable',
                        'id'            => $row['id'],
                        'flag'          => $row['active'] ? 0 : 1,
                    )),
                ),
            );
        }
        $modules = array();
        foreach ($modulelist as $name => $data) {
            if (!isset($types[$name])) {
                continue;
            }
            $modules[$name] = array(
                'title'         => $data['title'],
                'url'           => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'module',
                    'name'          => $name,
                )),
                'types'         => $types[$name],
            );
        }

        //d($modules);
        $this->view()->assign(array(
            'title'     => $title,
            'modules'   => $modules,
        ));

        $this->view()->setTemplate('comment-type');
    }


    /**
     * Enable/disable a type
     *
     * @return bool
     */
    public function enableAction()
    {
        $id = _get('id', 'int') ?: 1;
        $flag = _get('flag', 'int');
        $return = _get('return');

        $row = Pi::model('type', 'comment')->find($id);
        if (!$row) {
            $status = -1;
            $message = _a('Type was not found.');
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

        // Clear cache
        if ($status > 0) {
            Pi::registry('type', 'comment')->flush();
        }

        if (!$return) {
            $this->jump(array('action' => 'index'), $message, $status == 1 ? 'success' : 'error');
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
