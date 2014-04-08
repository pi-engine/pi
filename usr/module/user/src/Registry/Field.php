<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Registry
 */

namespace Module\User\Registry;

use Pi;
use Pi\Application\Registry\AbstractRegistry;

/**
 * Pi user profile field registry
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Field extends AbstractRegistry
{
    /** @var string Module name */
    protected $module = 'user';

    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $fields = array();

        $where = array('active' => 1);
        $columns = array();
        switch ($options['action']) {
            case 'edit':
                $columns[] = 'edit';
                $where['is_edit'] = 1;
                break;

            case 'search':
                //$columns = array('name', 'title');
                $where['is_search'] = 1;
                break;

            case 'display':
                $columns[] = 'filter';
                $where['is_display'] = 1;
                break;

            case 'all':
                $options['action'] = '';
                break;

            default:
                break;
        }
        if ('all' == $options['type']) {
            $options['type'] = '';
        }
        if (!empty($options['type'])) {
            $where['type'] = $options['type'];
        } elseif ($columns) {
            $columns[] = 'type';
        }
        if ($columns) {
            $columns[] = 'handler';
            $columns[] = 'name';
            $columns[] = 'title';
            $columns[] = 'is_required';
            $columns = array_unique($columns);
        }

        $model = Pi::model('field', $this->module);
        $select = $model->select()->where($where);
        if ($columns) {
            $select->columns($columns);
        }
        $select->order('id');
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $fields[$row->name] = $row->toArray();
        }

        return $fields;
    }

    /**
     * {@inheritDoc}
     * @param string $type Field types: account, profile, compound
     * @param string $action Actions: display, edit, search, all
     * @param array
     */
    public function read($type = '', $action = '')
    {
        $options = compact('type', 'action');
        $data = $this->loadData($options);

        return $data;
    }

    /**
     * {@inheritDoc}
     * @param string $action
     */
    public function create($action = '')
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
