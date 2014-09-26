<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Field;

use Pi;

/**
 * Abstract class for common field handling
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
abstract class AbstractCommonHandler
{
    /** @var string Field name and table name */
    protected $name = '';
    
    protected $type = 'common';
    
    protected $module;

    /**
     * Constructor
     *
     * @param string $name
     */
    public function __construct($module, $name = '')
    {
        $name = $name ?: $this->getName();
        $this->name = $name;
        $this->module = $module;
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
     * Get model instance
     * 
     * @return Pi\Application\Model\Model
     */
    public function getModel()
    {
        return Pi::model('article', $this->module);
    }

    /**
     * Get field meta
     *
     * @return array
     */
    public function getMeta()
    {
        $meta = Pi::registry('field', $this->module)->read($this->getName());

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
            if ($data[$key] === null) {
                $data[$key] = '';
            }
            if (!isset($meta[$key]) ) {
                unset($data[$key]);
            }
        }
        $data['uid'] = $uid;

        return $data;
    }

    /**
     * Add article data to common field
     *
     * @param int   $id     Article ID
     * @param mixed $data
     *
     * @return bool
     */
    public function add($id, $data)
    {
        return $this->update($id, $data);
    }

    /**
     * Update article common field
     *
     * @param int   $id    Article ID
     * @param mixed $data
     *
     * @return bool
     */
    public function update($id, $data)
    {
        if (is_array($data)) {
            $data = json_encode($data);
        }
        if (!is_string($data) || !is_numeric($data)) {
            return false;
        }
        $column = $this->getName();
        $result = $this->getModel()->update(
            array($column => $data),
            array('id' => $id)
        );

        return (bool) $result;
    }

    /**
     * Delete article common field
     *
     * @param int   $id
     *
     * @return bool
     */
    public function delete($id)
    {
        return $this->update($id, null);
    }
    
    /**
     * Get article common field data
     *
     * @param int   $id
     * @param bool  $filter     To filter for display
     *
     * @return array
     */
    abstract public function get($id, $filter = false);

    /**
     * Get multiple article common fields data
     *
     * @param int[] $ids
     * @param bool  $filter     To filter for display
     *
     * @return array
     */
    abstract public function mget($ids, $filter = false);

    /**
     * Get article custom compound/field read for display
     *
     * @param int|int[]   $id
     * @param array|null $data
     *
     * @return array
     */
    abstract public function display($id, $data = null);
}
