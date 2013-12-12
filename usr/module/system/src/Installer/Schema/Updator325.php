<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace   Module\System\Installer\Schema;

use Pi;

/**
 * System schema update handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Updator325 extends AbstractUpdator
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
        if (version_compare($version, '3.2.2', '<')) {
            $updator = new Updator322($this->handler);
            $result = $updator->upgrade($version);
            if (false === $result) {
                return $result;
            }
        }
        $result = $this->from322($version);

        return $result;
    }

    /**
     * Upgrade from previous version
     *
     * @param string $version
     *
     * @return bool
     */
    protected function from322($version)
    {
        $status = true;
        if (version_compare($version, '3.2.5', '<')) {
            $table = Pi::model('page')->getTable();
            $sql = sprintf('ALTER TABLE %s ADD `cache_type` enum(\'page\', \'action\') NOT NULL AFTER `permission`', $table);
            $status = $this->queryTable($sql);
        }

        return $status;
    }
}
