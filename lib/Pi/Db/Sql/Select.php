<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Db\Sql;

use Zend\Db\Sql\Select as ZendSelect;

/**
 * Select class
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Select extends ZendSelect
{
    /**
     * {@inheritdoc}
     */
    public function limit($limit)
    {
        parent::limit((int) $limit);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function offset($offset)
    {
        parent::offset((int) $offset);

        return $this;
    }
}
