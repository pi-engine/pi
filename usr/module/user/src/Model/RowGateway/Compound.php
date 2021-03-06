<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Model\RowGateway;

use Pi;
use Pi\Application\Model\User\RowGateway\AbstractFieldRowGateway;

/**
 * User compound profile row gateway
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Compound extends AbstractFieldRowGateway
{
    /**
     * {@inheritDoc}
     */
    protected function getMetaList()
    {
        return Pi::registry('compound_field', 'user')->read($this['compound']);
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
