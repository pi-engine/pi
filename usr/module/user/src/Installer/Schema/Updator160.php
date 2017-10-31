<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace   Module\User\Installer\Schema;

use Pi;
use Pi\Application\Installer\Schema\AbstractUpdator;

/**
 * Module schema update handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Updator160 extends AbstractUpdator
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
        if (version_compare($version, '1.3.0', '<')) {
            $updator = new Updator130($this->handler);
            $result = $updator->upgrade($version);
            if (false === $result) {
                return $result;
            }
        }
         
        $result = $this->from160($version);

        return $result;
    }

    /**
     * Upgrade from previous version
     *
     * @param string $version
     *
     * @return bool
     */
    protected function from160($version)
    {
        if (version_compare($version, '1.6.0', '<')) {
            $table = Pi::db()->prefix('profile', 'user');

            $sql = sprintf('UPDATE %s SET city=location_city where city IS NULL OR city = ""', $table);
            $status = $this->queryTable($sql);

            if (false === $status) {
                return $status;
            }
            $sql = sprintf('UPDATE %s SET country=location_country where country IS NULL OR country = ""', $table);
            $status = $this->queryTable($sql);

            if (false === $status) {
                return $status;
            }
        }
        
        return $status;
    }
}
