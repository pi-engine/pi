<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
class Profile extends AbstractRegistry
{
    /** @var string Module name */
    protected $module = 'user';

    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $fields = array();

        $columns = array();
        $where = array('active' => 1);
        if ($options['type']) {
            $where['type'] = $options['type'];
        }

        switch ($options['action']) {
            case 'display':
                $columns = array('name', 'title', 'filter');
                $where['is_display'] = 1;
                break;
            case 'edit':
                $columns = array('name', 'title', 'edit');
                $where['is_edit'] = 1;
                break;
            case 'search':
                $columns = array('name', 'title');
                $where['is_search'] = 1;
                break;
            default:
                break;
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
     * @param string $action Actions: display, edit, search
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
