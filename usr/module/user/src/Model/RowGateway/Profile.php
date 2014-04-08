<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Model\RowGateway;

use Pi;
use Pi\Application\Model\User\RowGateway\AbstractFieldRowGateway;

/**
 * User custom profile row gateway
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Profile extends AbstractFieldRowGateway
{
    /**
     * {@inheritDoc}
     */
    protected function getMetaList()
    {
        return Pi::registry('field', 'user')->read('profile');
    }

    /**
     * {@inheritDoc}
     */
    protected function getMeta($key = null)
    {
        if (isset($this['field'])) {
            $key = $this['field'];
            return parent::getMeta($key);
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function filter($col = null)
    {
        if (isset($this['field'])) {
            $col = $this['field'];
            return parent::filter($col);
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    protected function filterField($field)
    {
        $field = 'value';
        return parent::filterField($field);
    }
}
