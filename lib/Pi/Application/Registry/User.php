<?php
/**
 * Pi user meta registry
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * Different actions -
 *
 * For edit:
 *  null method - use default form element 'Text'
 *  empty method - hide the element
 *  method is string - use system form element
 *  method is array([module], element, [options]) - use module form element
 *
 * For admin:
 *  null method - inherite from edit
 *  otherwise - same mehtods as edit
 *
 * For view:
 *  null method - use raw data
 *  empty method - hide the data
 *  method is array(module, element) - transform raw data via the module_profile::method
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Pi\Application
 * @subpackage      Registry
 * @version         $Id$
 */

namespace Pi\Application\Registry;
use Pi;

class User extends AbstractRegistry
{
    protected function loadDynamic($options = array())
    {
        $parseView = function ($row)
        {
            $view = array();
            if (!empty($row->view)) {
                $view['method'] = array($row->module . '_profile', $row->view);
            } elseif (!empty($row->options)) {
                $view['options'] = unserialize($row->options);
            }

            return $view;
        };

        $parseEdit = function ($row, $action)
        {
            if ($action == 'admin') {
                $input = is_null($row->admin) ? $row->edit : $row->admin;
            } else {
                $input = $row->edit;
            }
            if (!empty($input)) {
                $input = unserialize($input);
                if (is_string($input) && !empty($input)) {
                    $input = array('type' => $input);
                }
                if (empty($input['options']['multiOptions'])) {
                    if (!empty($row->options)) {
                        $input['options']['multiOptions'] = unserialize($row->options);
                    }
                }
                if (!empty($input['module'])) {
                    $input['module'] = $row->module;
                }
            }
            if (!empty($input) || is_null($input)) {
                $input['options']['label'] = $row->title;
                if ($row->required) {
                    $meta['options']['required'] = 1;
                }
            }

            return $input;
        };

        $parseSearch = function ($row)
        {
            if (!is_null($row->search)) {
                $input = $row->search;
            } elseif (!empty($row->edit)) {
                $input = $row->edit;
            } elseif (!empty($row->admin)) {
                $input = $row->admin;
            }
            if (!empty($input)) {
                $input = unserialize($input);
                if (is_string($input) && !empty($input)) {
                    $input = array('type' => $input);
                }
                if (empty($input['options']['multiOptions'])) {
                    if (!empty($row->options)) {
                        $input['options']['multiOptions'] = unserialize($row->options);
                    }
                }
                if (!empty($input['module'])) {
                    $input['module'] = $row->module;
                }
            }
            if (!empty($input) || is_null($input)) {
                $input['options']['label'] = $row->title;
            }

            return $input;
        };

        $model = Pi::model('user_meta');
        $select = $model->select()->where(array('active' => 1))->order('id ASC');
        $rowset = $model->selectWith($select);
        $data = array();
        foreach ($rowset as $row) {
            if ($options['action'] == 'edit') {
                $meta = $parseEdit($row, 'edit');
                if ($meta) {
                    $data[$row->key] = $meta;
                }
                continue;
            }
            if ($options['action'] == 'admin') {
                $meta = $parseEdit($row, 'admin');
                if ($meta) {
                    $data[$row->key] = $meta;
                }
                continue;
            }
            if ($options['action'] == 'search') {
                $meta = $parseSearch($row);
                if ($meta) {
                    $data[$row->key] = $meta;
                }
                continue;
            }
            if (isset($row->view)) {
                $data[$row->key] = $parseView($row);
            }
            $data[$row->key]['title'] = $row->title;
        }
        return $data;
    }

    public function read($action = 'view', $meta = null)
    {
        $options = compact('action');
        $data = $this->loadData($options);
        if (isset($meta)) {
            $result = isset($data[$meta]) ? $data[$meta] : false;
        } else {
            $result = $data;
        }

        return $result;
    }

    public function create($action = 'view')
    {
        $this->clear('');
        $this->read($action);
        return true;
    }

    public function setNamespace($meta)
    {
        return parent::setNamespace('');
    }


    public function flush()
    {
        return $this->clear('');
    }
}
