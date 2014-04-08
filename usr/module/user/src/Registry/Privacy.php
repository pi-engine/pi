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
 * Pi user profile field privacy registry
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Privacy extends AbstractRegistry
{
    /** @var string Module name */
    protected $module = 'user';

    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $list = array();

        $model  = Pi::model('privacy', $this->module);
        $where  = array();
        if (isset($options['forced'])) {
            $where['is_forced'] = $options['forced'] ? 1 : 0;
        }
        $rowset = $model->select($where);
        foreach ($rowset as $row) {
            $list[$row['field']] =  $row->toArray();
        }

        return $list;
    }

    /**
     * {@inheritDoc}
     * @param null|bool $forced Type: null: all; true - forced privacy; false - not forced privacy
     * @param array
     */
    public function read($forced = null)
    {
        $options = array();
        if (null !== $forced) {
            $options = array('forced' => $forced);
        }
        $result = $this->loadData($options);

        return $result;
    }

    /**
     * {@inheritDoc}
     * @param bool $name
     */
    public function create($forced = null)
    {
        $this->clear('');
        $this->read($forced);

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
