<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Pi\Application\Db;

/**
 * Database handler service
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Database extends AbstractService
{
    /** {@inheritDoc} */
    protected $fileIdentifier = 'database';

    /**
     * Database identifier
     *
     * @var Db
     */
    protected $db;

    /**
     * Get Pi Db handler
     *
     * @param array $options
     * @return Db
     */
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
     * @see Pi\Application\Db
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
