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
use Pi\Application\Installer\Resource\Config as ConfigResource;

/**
 * System schema update handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Updator354 extends AbstractUpdator
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
        if (version_compare($version, '3.5.1', '<')) {
            $updator = new Updator351($this->handler);
            $result = $updator->upgrade($version);
            if (false === $result) {
                return $result;
            }
        }
        $result = $this->from350($version);

        return $result;
    }

    /**
     * Upgrade from previous version
     *
     * @param string $version
     *
     * @return bool
     */
    protected function from350($version)
    {
        $status = true;

        if (version_compare($version, '3.5.4', '<')) {

            $table = Pi::db()->prefix('navigation_node');
            $sql =<<<'EOT'
ALTER TABLE %s 
CHANGE `data` `data` MEDIUMTEXT;
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
