<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace   Module\Comment\Installer\Schema;

use Pi;
use Pi\Application\Installer\Schema\AbstractUpdator;

/**
 * System schema update handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Updator110 extends AbstractUpdator
{
    /**
     * Update module table schema
     *
     * @param string $version
     *
     * @return bool
     */
    public function upgrade($version)
    {
        $result = $this->from100($version);

        return $result;
    }

    /**
     * Upgrade from previous version
     *
     * @param string $version
     *
     * @return bool
     */
    protected function from100($version)
    {
        $status = true;
        if (version_compare($version, '1.1.0', '<')) {

            $tablesRename = array(
                'category'  => 'type',
            );
            foreach ($tablesRename as $old => $new) {
                $tableOld = Pi::db()->prefix($old, 'comment');
                $tableNew = Pi::db()->prefix($new, 'comment');
                $sql = sprintf('RENAME TABLE %s TO %s', $tableOld, $tableNew);
                $status = $this->queryTable($sql);
            }

            $table = Pi::db()->prefix('type', 'comment');
            $sql = sprintf(
                'ALTER TABLE %s
                DROP KEY `module_category`,
                ADD UNIQUE KEY `module_type` (`module`, `name`);',
                $table
            );
            $status = $this->queryTable($sql);

            $table = Pi::db()->prefix('root', 'comment');
            $sql = sprintf(
                'ALTER TABLE %s
                CHANGE `category` `type` varchar(64) NOT NULL default \'\',
                DROP KEY `module_item`,
                ADD UNIQUE KEY `module_item` (`module`, `type`, `item`);',
                $table
            );
            $status = $this->queryTable($sql);
        }

        return $status;
    }
}
