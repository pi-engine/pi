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

use PDO;
use Pi\Db\DbGateway;

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
     * Database instance and gateway
     *
     * @var DbGateway
     */
    protected $db;

    /**
     * Get database gateway handler
     *
     * @param array $options
     * @return DbGateway
     */
    public function db($options = array())
    {
        // Specified DbGateway
        if ($options) {
            $db = $this->loadDb($options);
            return $db;
        // Default DbGateway, equal to Pi::db()
        } elseif (!$this->db) {
            $this->db = $this->loadDb();
        }

        return $this->db;
    }

    /**
     * Creates a database handler
     *
     * @param array $options
     *
     * @return DbGateway
     */
    public function loadDb(array $options = array())
    {
        // Use system default options if no custom options
        $options = $options ?: $this->options;
        $db = new DbGateway($options);

        return $db;
    }

    /**
     * Build database connection of current DB instance
     *
     * @param DbGateway        $db
     *
     * @throws \Exception
     * @return PDO
     */
    public function connect(DbGateway $db = null)
    {
        $db = $db ?: $this->db();
        $connection = $db->getAdapter()->getDriver()->getConnection();
        if (!$connection->isConnected()) {
            try {
                $connection->connect();
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return $connection->getResource();
    }
}
