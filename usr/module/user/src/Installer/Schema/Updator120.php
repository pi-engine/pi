<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace   Module\User\Installer\Schema;

use Pi;
use Pi\Application\Installer\Schema\AbstractUpdator;

/**
 * Module schema update handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Updator120 extends AbstractUpdator
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
        $result = $this->from110($version);

        return $result;
    }

    /**
     * Upgrade from previous version
     *
     * @param string $version
     *
     * @return bool
     */
    protected function from110($version)
    {
        $status = true;
        if (version_compare($version, '1.2.0', '<')) {

            Pi::model('field', 'user')->update(
                array('module' => 'user'),
                array('module' => '')
            );

            Pi::model('compound_field', 'user')->update(
                array('module' => 'user'),
                array('module' => '')
            );

            $table = Pi::db()->prefix('privacy', 'user');
            $sql =<<<'EOT'
ALTER TABLE %s
MODIFY `is_forced` tinyint(1) unsigned NOT NULL default '0';
EOT;
            $sql = sprintf($sql, $table);
            $status = $this->queryTable($sql);

            $table = Pi::db()->prefix('privacy_user', 'user');
            $sql =<<<'EOT'
ALTER TABLE %s
DROP `is_forced`;
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
