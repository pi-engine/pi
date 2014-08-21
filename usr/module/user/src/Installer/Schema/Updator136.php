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
class Updator136 extends AbstractUpdator
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
        $result = $this->from130($version);

        return $result;
    }

    /**
     * Upgrade from previous version
     *
     * @param string $version
     *
     * @return bool
     */
    protected function from130($version)
    {
        if (version_compare($version, '1.3.6', '<')) {

            $table = Pi::db()->prefix('activity', 'user');
            $sql =<<<'EOT'
ALTER TABLE  %s
ADD `url` TEXT NOT NULL;
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
