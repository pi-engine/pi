<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace   Module\System\Installer\Schema;

use Pi;
use Pi\Application\Installer\Schema\AbstractUpdator;

/**
 * System schema update handler
 *
 * @author Mickaël STAMM <contact@sta2m.com>
 */
class Updator3513 extends AbstractUpdator
{
    /**
     * Update system table schema
     *
     * @param string $version
     *
     * @return bool
     */
    public function upgrade($version)
    {
        if (version_compare($version, '3.5.8', '<')) {
            $updator = new Updator358($this->handler);
            $result = $updator->upgrade($version);
            if (false === $result) {
                return $result;
            }
        }
        
        $result = $this->from3513($version);

        return $result;
    }

    /**
     * Upgrade from previous version
     *
     * @param string $version
     *
     * @return bool
     */
    protected function from3513($version)
    {
        $status = true;

        if (version_compare($version, '3.5.13', '<')) {
            $table = Pi::db()->prefix('config');
            $sql =<<<'EOT'
ALTER TABLE %s CHANGE `description` `description` TEXT NOT NULL DEFAULT '';
EOT;

            $sql = sprintf($sql, $table);
            $status = $this->queryTable($sql);

            if (false === $status) {
                return $status;
            }
        }

        return $status;
    }
}
