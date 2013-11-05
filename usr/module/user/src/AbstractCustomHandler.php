<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User;

use Pi;
use Pi\Application\Installer\SqlSchema;
use Pi\Db\Table\AbstractTableGateway;
use Pi\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\InputFilter;

/**
 * Abstract class for custom field handling
 *
 *
 * Skeleton
 *
 * - Specs: usr/custom/user/config/user.php
 * - Handler: usr/custom/user/src/Field/<FieldName>.php
 * - Form/Filter: usr/custom/user/src/Form/FieldNameForm.php
 * - schema: usr/custom/user/sql/<field>.sql
 * - locale: usr/custom/user/locale/en/main.csv
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractCustomHandler
{
    /** @var bool Is multi-record per user? */
    protected $isMultiple = true;

    /** @var string Field name and table name */
    protected $name = '';

    /** @var string SQL schema content */
    protected $sql = '';

    /** @var string File for SQL schema */
    protected $sqlFile = '';

    /** @var string Form class */
    protected $form = '';

    /** @var string File to form template */
    protected $template = '';

    /** @var string Form filter class */
    protected $filter = '';

    /**
     * Constructor
     *
     * @param string $name
     */
    public function __construct($name = '')
    {
        if ($name) {
            $this->name = $name;
        }
    }

    /**
     * Is multiple
     *
     * @return bool
     */
    public function isMultiple()
    {
        return $this->isMultiple;
    }

    /**
     * Get name, retrieve from class name if not specified
     *
     * @return string
     */
    public function getName()
    {
        if (!$this->name) {
            $class = get_class($this);
            $className = substr($class, -1 * strrpos($class, '\\'));
            $this->name = strtolower($className);
        }

        return $this->name;
    }

    /**
     * Set sql schema
     *
     * @param string $sql
     *
     * @return $this
     */
    public function setSql($sql)
    {
        $this->sql = $sql;

        return $this;
    }

    /**
     * Set sql schema file
     *
     * @param string $file
     *
     * @return $this
     */
    public function setSqlFile($file)
    {
        $this->sqlFile = $file;

        return $this;
    }

    /**
     * Install a field
     *
     * - Create schema
     *
     * @return bool
     */
    public function install()
    {
        if ($this->sql) {
            $sqlHandler = new SqlSchema;
            $sqlHandler->queryContent($this->sql, 'user_custom');
        } else {
            $file = $this->sqlFile;
            if (!$file) {
                $file = sprintf(
                    '%s/user/sql/%s.sql',
                    Pi::path('custom_module'),
                    $this->getName()
                );
                if (!file_exists($file)) {
                    $file = '';
                }
            }
            if ($file) {
                $sqlHandler = new SqlSchema;
                $sqlHandler->queryFile($file, 'user_custom');
            }
        }

        return true;
    }

    /**
     * Drop schema
     *
     * @return bool
     */
    public function uninstall()
    {
        $sql = sprintf(
            'DROP TABLE IF EXISTS %s',
            $this->getTable()
        );
        Pi::db()->query($sql);

        return true;
    }

    /**
     * Modify schema
     *
     * @return bool
     */
    public function modify()
    {
        return true;
    }

    /**
     * Get model
     *
     * @return AbstractTableGateway
     */
    public function getModel()
    {
        $model = Pi::model('custom_' . $this->getName(), 'user');

        return $model;
    }

    /**
     * Get full table name
     *
     * @return string
     */
    public function getTable()
    {
        return $this->getModel()->getTable();
    }

    /**
     * Get field meta
     *
     * @return array
     */
    public function getMeta()
    {
        $meta = Pi::registry('compound_field', 'user')->read($this->getName());

        return $meta;
    }

    /**
     * Canonize field data
     *
     * @param int $uid
     * @param mixed $data
     *
     * @return array
     */
    protected function canonize($uid, $data)
    {
        $meta = $this->getMeta();
        foreach (array_keys($data) as $key) {
            if (!isset($meta[$key]) ) {
                unset($data[$key]);
            }
        }
        $data['uid'] = $uid;

        return $data;
    }

    /**
     * Add user custom compound
     *
     * @param int   $uid
     * @param mixed $data
     *
     * @return int
     */
    public function add($uid, $data)
    {
        if ($this->isMultiple) {
            $order = 0;
            foreach ((array) $data as $set) {
                $set = $this->canonize($uid, $set);
                $set['order'] = $order++;
                $row = $this->getModel()->createRow($set);
                $row->save();
            }
        } else {
            $row = $this->getModel()->createRow($this->canonize($uid, $data));
            $row->save();
        }

        return (int) $row['id'];
    }

    /**
     * Update user custom compound
     *
     * @param int   $uid
     * @param mixed $data
     *
     * @return int
     */
    public function update($uid, $data)
    {
        $this->delete($uid);
        $id = $this->add($uid, $data);

        return $id;
    }

    /**
     * Delete user custom compound
     *
     * @param int   $uid
     *
     * @return bool
     */
    public function delete($uid)
    {
        $row = $this->getModel()->find($uid, 'uid');
        if ($row->id) {
            $row->delete();
        }

        return true;
    }

    /**
     * Get user custom compound
     *
     * @param int   $uid
     * @param bool  $filter     To filter for display
     *
     * @return array
     */
    public function get($uid, $filter = false)
    {
        $result = array();
        if ($this->isMultiple) {
            $select = $this->getModel()->select();
            $select->order('order ASC');
            $select->where(array('uid' => $uid));
            $rowset = $this->getModel()->selectWith($select);
            foreach ($rowset as $row) {
                $result[] = $row->toArray();
            }
        } else {
            $row = $this->getModel()->find($uid, 'uid');
            $result = $row ? $row->toArray() : array();
        }

        return $result;
    }

    /**
     * Get multiple user custom compound fields
     *
     * @param int[]   $uids
     * @param bool  $filter     To filter for display
     *
     * @return array
     */
    public function mget($uids, $filter = false)
    {
        $result = array();
        $select = $this->getModel()->select();
        $select->where(array('uid' => $uids));
        if ($this->isMultiple) {
            $select->order('order ASC');
            $rowset = $this->getModel()->selectWith($select);
            foreach ($rowset as $row) {
                $result[(int) $row['uid']][] = $row->toArray();
            }
        } else {
            $rowset = $this->getModel()->selectWith($select);
            foreach ($rowset as $row) {
                $result[(int) $row['uid']] = $row->toArray();
            }
        }

        return $result;
    }

    /**
     * Get form for the compound
     *
     * @param string $name
     * @param string $action
     * @param array  $data
     * @param array  $message
     *
     * @return Form|string
     */
    public function getForm(
        $name = '',
        $action = '',
        array $data = array(),
        array $message = array()
    ) {
        $form = null;
        $formClass = $this->form
            ?: 'Custom\User\Form\Form' . ucfirst($this->getName());
        if (class_exists($formClass)) {
            $form = new $formClass($name);
            if ($action) {
                $form->setAttribute('action', $action);
            }
            if ($data) {
                $form->setData($data);
            }
            if ($message) {
                $form->setMessages($message);
            }
        } else {
            $template = $this->template
                ?: sprintf(
                    '%s/user/template/field/%s.phtml',
                    Pi::path('custom_module'),
                    $this->getName()
                );
            if (file_eixsts($template)) {
                $form = Pi::service('template')->render($template, array(
                    'name'  => $name,
                    'action'    => $action,
                    'data'      => $data,
                    'message'   => $message,
                ));
            }
        }

        return $form;
    }

    /**
     * Get form filter for the compound
     *
     * @return InputFilter
     */
    public function getFilter()
    {
        $filter = null;
        $filterClass = $this->filter
            ?: 'Custom\User\Form\Filter' . ucfirst($this->getName());
        if (class_exists($filterClass)) {
            $filter = new $filterClass();
        }

        return $filter;
    }
}
