<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
        return Pi::registry('profile', 'user')->read('profile');
    }

    /**
     * {@inheritDoc}
     */
    protected function getMeta($key = null)
    {
        $key = $this['field'];
        return parent::getMeta($key);
    }

    /**
     * {@inheritDoc}
     */
    public function filter($col = null)
    {
        $col = $this['field'];
        return parent::filter($col);
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
