<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Installer\Schema;

use Pi;
use Pi\Application\Installer\Schema\AbstractUpdator;

/**
 * System schema update handler
 *
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
class Updator372 extends AbstractUpdator
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
        if (version_compare($version, '3.7.2', '<')) {
            $updator = new Updator361($this->handler);
            $result  = $updator->upgrade($version);
            if (false === $result) {
                return $result;
            }
        }
        $result = $this->from371($version);

        return $result;
    }

    /**
     * Upgrade from previous version
     *
     * @param string $version
     *
     * @return bool
     */
    protected function from371($version)
    {
        $status = true;

        if (version_compare($version, '3.7.2', '<')) {
            // Set table
            $table  = Pi::db()->prefix('user_account');

            // Add two_factor
            $sql    = sprintf("ALTER TABLE %s ADD `two_factor` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `active`", $table);
            $status = $this->queryTable($sql);
            if (false === $status) {
                return $status;
            }

            // Add
            $sql    = sprintf("ALTER TABLE %s ADD `two_factor_secret` VARCHAR(255) NOT NULL DEFAULT '' AFTER `two_factor`", $table);
            $status = $this->queryTable($sql);
            if (false === $status) {
                return $status;
            }
        }

        return $status;
    }
}
