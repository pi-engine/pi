<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi;

/**
 * Database connection bootstrap
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Database extends AbstractResource
{
    /**
     * {@inheritDoc}
     * @return Pi\Application\Db
     */
    public function boot()
    {
        $db = Pi::service('database')->db($this->options);

        return $db;
    }
}
