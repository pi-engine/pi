<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Db\Sql;

use Zend\Db\Sql\Sql as ZendSql;
use Zend\Db\Sql\Exception;

/**
 * Sql class
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Sql extends ZendSql
{
    /**
     * {@inheritdoc}
     */
    public function select($table = null)
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(sprintf(
                'This Sql object is intended to work with only the table "%s" provided at construction time.',
                $this->table
            ));
        }
        return new Select(($table) ?: $this->table);
    }
}
