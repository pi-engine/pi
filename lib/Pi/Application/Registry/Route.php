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
 * Route list
 *
 * Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Route extends AbstractRegistry
{
    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $model = Pi::model('route');
        $select = $model->select()->columns(array('name', 'data'))
            ->order('priority ASC, id ASC');
        if (empty($options['exclude'])) {
            $select->where
                ->equalTo('active', 1)
                ->NEST
                    ->equalTo('section', $options['section'])
                    ->OR
                    ->equalTo('section', '')
                ->UNNEST;
        } else {
            $select->where(array(
                'active'        => 1,
                'section <> ?'  => $options['section'],
            ));
        }
        $rowset = $model->selectWith($select);

        $configs = array();
        foreach ($rowset as $row) {
            $configs[$row->name] = $row->data;
        }

        return $configs;
    }

    /**
     * {@inheritDoc}
     * @param string    $section
     * @param bool      $exclude    To exclude the specified section
     */
    public function read($section = 'front', $exclude = false)
    {
        $options = compact('section', 'exclude');
        $data = $this->loadData($options);

        return $data;
    }

    /**
     * {@inheritDoc}
     * @param string    $section
     * @param bool      $exclude    To exclude the specified section
     */
    public function create($section = 'front', $exclude = false)
    {
        $this->clear($section);
        $this->read($section, $exclude);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function setNamespace($meta = '')
    {
        if (is_string($meta)) {
            $namespace = $meta;
        } else {
            $namespace = $meta['section'];
        }

        return parent::setNamespace($namespace);
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        $this->flushBySections();

        return $this;
    }
}
