<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Registry
 */

namespace Module\User\Registry;

use Pi;
use Pi\Application\Registry\AbstractRegistry;

/**
 * Profile display group fields
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class DisplayField extends AbstractRegistry
{
    /** @var string Module name */
    protected $module = 'user';

    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = [])
    {
        $list = [];
        $gids = [];
        if (!empty($options['group'])) {
            $gids = $options['group'];
        } else {
            $groups = Pi::registry('display_group', $this->module)->read();
            array_walk($groups, function ($group, $gid) use (&$gids) {
                if (!$group['compound']) {
                    $gids[] = $gid;
                }
            });
        }
        $where = ['group' => $gids];

        $model  = Pi::model('display_field', $this->module);
        $select = $model->select()->where($where);
        $select->order('order ASC');
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $list[] = $row['field'];
        }

        return $list;
    }

    /**
     * {@inheritDoc}
     * @param array
     */
    public function read($group = 0)
    {
        $options = compact('group');
        $result  = $this->loadData($options);

        return $result;
    }

    /**
     * {@inheritDoc}
     * @param bool $name
     */
    public function create()
    {
        $this->clear('');
        $this->read();

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
