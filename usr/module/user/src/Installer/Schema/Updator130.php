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
class Updator130 extends AbstractUpdator
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

        if (version_compare($version, '1.3.3', '<')) {
            Pi::model('user_data')->update(
                array('name' => 'register_activation'),
                array('module' => 'user', 'name' => 'register-activation')
            );
        }

        if (version_compare($version, '1.3.2', '<')) {
            $table = Pi::db()->prefix('compound_field', 'user');
            $sql =<<<'EOT'
ALTER TABLE %s
ADD `is_required` tinyint(1) unsigned NOT NULL default '0';
EOT;
            $sql = sprintf($sql, $table);
            $status = $this->queryTable($sql);

            foreach (array('field', 'compound_field') as $table) {
                $rowset = Pi::model($table, 'user')->select(array());
                foreach ($rowset as $row) {
                    if (isset($row['edit']['required'])) {
                        $row['is_required'] = $row['edit']['required'] ? 1 : 0;
                        unset($row['edit']['required']);
                        $row->save();
                    }
                }
            }
        }

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
