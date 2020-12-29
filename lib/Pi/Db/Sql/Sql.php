<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Db\Sql;

use Laminas\Db\Sql\Exception;
use Laminas\Db\Sql\Sql as LaminasSql;

/**
 * Sql class
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Sql extends LaminasSql
{
    /**
     * {@inheritdoc}
     */
    public function select($table = null)
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'This Sql object is intended to work with only the table "%s" provided at construction time.',
                    $this->table
                )
            );
        }
        return new Select(($table) ?: $this->table);
    }
}
