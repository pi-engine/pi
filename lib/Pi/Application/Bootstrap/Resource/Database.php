<?php
/**
 * Bootstrap resource
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi;

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
