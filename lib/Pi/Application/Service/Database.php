<?php
/**
 * Database service
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 * @subpackage      Service
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Service;
use Pi;
use Pi\Application\Db;

class Database extends AbstractService
{
    protected $fileIdentifier = 'database';
    /**
     * Database identifier
     * @var Db
     */
    protected $db;

    public function db($options = array())
    {
        // Specified Db
        if ($options) {
            $db = $this->loadDb($options);
            return $db;
        // Default Db, equal to Pi::db()
        } elseif (!$this->db) {
            $this->db = $this->loadDb();
        }
        return $this->db;
    }

    /**
     * Creates a database handler
     *
     * @see \Pi\Application\Db
     * @param array $options
     * @return Db
     */
    public function loadDb($options = array())
    {
        // Use system default options if no custom options
        $options = $options ?: $this->options;
        $db = new Db($options);

        return $db;
    }
}
