<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Registry
 */

namespace Pi\Application\Registry;

use Pi;

/**
 * Pi user meta registry
 *
 * Different operations
 *
 * For edit:
 *
 *  - null method: use default form element 'Text'
 *  - empty method: hide the element
 *  - method is string: use system form element
 *  - method is array (<module>, <element>, <options>): use module form element
 *
 * For admin:
 *
 *  - null method: inherite from edit
 *  - otherwise: same mehtods as edit
 *
 * For view:
 *
 *  - null method: use raw data
 *  - empty method: hide the data
 *  - method is array(<module>, <element>):
 *      transform raw data via Module\Profile::method
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class User extends AbstractRegistry
{
    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $parseView = function ($row) {
            $view = array();
            if (!empty($row->view)) {
                $view['method'] = array($row->module . '_profile', $row->view);
            } elseif (!empty($row->options)) {
                $view['options'] = unserialize($row->options);
            }

            return $view;
        };

        $parseEdit = function ($row, $action) {
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
                        $input['options']['multiOptions'] =
                            unserialize($row->options);
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

        $parseSearch = function ($row) {
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
                        $input['options']['multiOptions'] =
                            unserialize($row->options);
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
        $select = $model->select()->where(array('active' => 1))
            ->order('id ASC');
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

    /**
     * {@inheritDoc}
     * @param string $action
     * @param string|null $meta
     */
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

    /**
     * {@inheritDoc}
     * @param string $action
     */
    public function create($action = 'view')
    {
        $this->clear('');
        $this->read($action);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function setNamespace($meta = '')
    {
        return parent::setNamespace('');
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        return $this->clear('');
    }
}
