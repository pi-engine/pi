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
 * Theme list with full meta
 *
 * Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Themelist extends AbstractRegistry
{
    /**
     * Load raw data
     *
     * @param   array   $options potential values for type: front, admin, both
     * @return  array   keys: dirname => name, image, author, version
     */
    protected function loadDynamic($options = array())
    {
        $model = Pi::model('theme');
        $type = empty($options['type']) ? 'front' : $options['type'];

        $select = $model->select();
        $select->where->in('type', array('both', $type));
        $rowset = $model->selectWith($select);

        $themes = array();
        foreach ($rowset as $row) {
            $config = Pi::service('theme')->loadConfig($row->name);
            $config['screenshot'] = !empty($config['screenshot'])
                ? Pi::service('asset')->getAssetUrl(
                    'theme/' . $row->name,
                    $config['screenshot'],
                    false
                  )
                : Pi::url('static/image/theme.png');
            $themes[$row->name] = array_merge($config, $row->toArray());
        }

        return $themes;
    }

    /**
     * {@inheritDoc}
     * @param string $type
     */
    public function read($type = '')
    {
        $options = compact('type');

        return $this->loadData($options);
    }

    /**
     * {@inheritDoc}
     * @param string $type
     */
    public function create($type = '')
    {
        $this->clear();
        $this->read($type);

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
