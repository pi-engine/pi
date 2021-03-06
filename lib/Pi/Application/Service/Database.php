<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
     * @param array|DbGateway $options
     *
     * @return DbGateway
     */
    public function db($options = [])
    {
        $result = null;
        // Set DbGateway for Pi
        if ($options instanceof DbGateway) {
            $this->db = $options;
            $result   = $this->db;
        // Load DbGateway
        } elseif ($options && is_array($options)) {
            $db     = $this->loadDb($options);
            $result = $db;
        // Default DbGateway
        } elseif (!$options) {
            if (!$this->db) {
                $this->db = $this->loadDb();
            }
            $result = $this->db;
        }

        return $result;
    }

    /**
     * Creates a database handler
     *
     * @param array $options
     *
     * @return DbGateway
     */
    public function loadDb(array $options = [])
    {
        // Use system default options if no custom options
        $options = $options ?: $this->options;
        $db      = new DbGateway($options);

        return $db;
    }

    /**
     * Build database connection of current DB instance
     *
     * @param DbGateway $db
     *
     * @return PDO
     * @throws \Exception
     */
    public function connect(DbGateway $db = null)
    {
        $db         = $db ?: $this->db();
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
