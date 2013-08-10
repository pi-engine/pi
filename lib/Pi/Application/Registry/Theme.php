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
 * Theme list with title and screenshot
 *
 * Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Theme extends AbstractRegistry
{
    /**
     * Load installed themes, indexed by dirname, sorted by order
     *
     * @param array $options
     * @return array    Keys: dirname => title, screenshot
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
            $themes[$row->name] = array(
                'title'         => $config['title'],
                'screenshot'    => !empty($config['screenshot'])
                    ? Pi::service('asset')->getAssetUrl(
                        'theme/' . $row->name,
                        $config['screenshot'],
                        false
                      )
                    : Pi::url('static/image/theme.png'),
            );
        }

        return $themes;
    }

    /**
     * {@inheritDoc}
     * @param string $type
     */
    public function read($type = 'front')
    {
        $options = compact('type');

        return $this->loadData($options);
    }

    /**
     * {@inheritDoc}
     * @param string $type
     */
    public function create($type = 'front')
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
