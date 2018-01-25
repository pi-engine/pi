<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Registry
 */

namespace Pi\Application\Registry;

use Pi;

/**
 * Theme list with title and screenshot
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Theme extends AbstractRegistry
{
    /**
     * Load installed themes, indexed by dirname, sorted by order
     *
     * @param array $options
     * @return array    Keys: dirname => title, screenshot
     */
    protected function loadDynamic($options = [])
    {
        $model = Pi::model('theme');
        $type  = empty($options['type']) ? 'front' : $options['type'];

        $select = $model->select();
        $select->where->in('type', ['both', $type]);
        $rowset = $model->selectWith($select);

        $themes = [];
        foreach ($rowset as $row) {
            $config             = Pi::service('theme')->loadConfig($row->name);
            $themes[$row->name] = [
                'title'      => $config['title'],
                'screenshot' => !empty($config['screenshot'])
                    ? Pi::service('asset')->getAssetUrl(
                        'theme/' . $row->name,
                        $config['screenshot']
                    )
                    : Pi::url('static/image/theme.png'),
            ];
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
