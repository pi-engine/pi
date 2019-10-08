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
 * @author Frédéric TISSOT <contact@espritdev.fr>
 */
class Updator361 extends AbstractUpdator
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
        if (version_compare($version, '3.5.13', '<')) {
            $updator = new Updator3513($this->handler);
            $result  = $updator->upgrade($version);
            if (false === $result) {
                return $result;
            }
        }
        $result = $this->from361($version);

        return $result;
    }

    /**
     * Upgrade from previous version
     *
     * @param string $version
     *
     * @return bool
     */
    protected function from361($version)
    {
        $status = true;

        if (version_compare($version, '3.6.1', '<')) {

            $table  = Pi::db()->prefix('user_account');
            $sql    = sprintf("ALTER TABLE %s CHANGE `identity` `identity` VARCHAR(64)", $table);
            $status = $this->queryTable($sql);

            if (false === $status) {
                return $status;
            }
        }

        return $status;
    }
}
