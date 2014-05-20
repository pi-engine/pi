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
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Updator322 extends AbstractUpdator
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
        $result = $this->from300($version);
        if (false === $result) {
            return $result;
        }
        $result = $this->from301($version);
        if (false === $result) {
            return $result;
        }
        $result = $this->from310($version);
        if (false === $result) {
            return $result;
        }
        $result = $this->from311($version);
        if (false === $result) {
            return $result;
        }
    }

    /**
     * Upgrade from previous version
     *
     * @param string $version
     *
     * @return bool
     */
    protected function from300($version)
    {
        $status = true;

        if (version_compare($version, '3.0.0-beta.3', '<')) {

            // Add table of navigation data
            $sql =<<<'EOD'
CREATE TABLE IF NOT EXISTS `{core.navigation_node}` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `navigation`      varchar(64)     NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',
  `data`            text,

  PRIMARY KEY  (`id`)
);
EOD;
            $status = $this->querySchema($sql);
            if (false === $status) {
                return $status;
            }

            // Drop not used table
            $tables = array('monitor', 'navigation_page');
            foreach ($tables as $name) {
                $table = Pi::db()->prefix($name);
                $sql = sprintf('DROP TABLE IF EXISTS %s', $table);
                $status = $this->queryTable($sql);
                if (false === $status) {
                    return $status;
                }
            }
        }

        return $status;
    }

    /**
     * Upgrade from previous version
     *
     * @param string $version
     *
     * @return bool
     */
    protected function from301($version)
    {
        $status = true;

        if (version_compare($version, '3.0.1', '<')) {
            // Add table field `section` to table acl_role
            $table = Pi::db()->prefix('acl_role');
            $sql = sprintf(
                'ALTER TABLE %s ADD `section` varchar(64) NOT NULL'
                    . ' default \'front\' AFTER `module`',
                $table
            );
            $status = $this->queryTable($sql);
            if (false === $status) {
                return $status;
            }

            // Update table acl_resource
            $table = Pi::db()->prefix('acl_resource');
            $sql = sprintf('ALTER TABLE %s DROP `item`', $table);
            $status = $this->queryTable($sql);
            if (false === $status) {
                return $status;
            }
            $sql = sprintf('ALTER TABLE %s DROP KEY `pair`', $table);
            $status = $this->queryTable($sql);
            if (false === $status) {
                return $status;
            }
            $sql = sprintf(
                'ALTER TABLE %s '
                . 'ADD KEY `pair` UNIQUE KEY (`section`, `module`, `name`)',
                $table
            );
            $status = $this->queryTable($sql);
            if (false === $status) {
                return $status;
            }

            // Update table for audit
            $table = Pi::db()->prefix('audit');
            $sql = sprintf('DROP TABLE IF EXISTS %s', $table);
            $status = $this->queryTable($sql);
            if (false === $status) {
                return $status;
            }

            $sql =<<<'EOD'
CREATE TABLE IF NOT EXISTS `{core.audit}` (
  `id`              int(10)         unsigned NOT NULL auto_increment,
  `user`            int(10)         unsigned NOT NULL    default '0',
  `ip`              varchar(15)     NOT NULL    default '',
  `section`         varchar(64)     NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',
  `controller`      varchar(64)     NOT NULL    default '',
  `action`          varchar(64)     NOT NULL    default '',
  `method`          varchar(64)     NOT NULL    default '',
  `message`         text,
  `extra`           text,
  `time`            int(10)         unsigned NOT NULL   default '0',

  PRIMARY KEY  (`id`)
);
CREATE TABLE IF NOT EXISTS `{core.user_staff}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `user`            int(10)         unsigned    NOT NULL,
  `role`            varchar(64)     NOT NULL    default '',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `user` (`user`)
);
EOD;
            $status = $this->querySchema($sql);
            if (false === $status) {
                return $status;
            }

            $rowset = Pi::model('user_role')->select(
                array('role <> ?' => 'member')
            );
            $modelStaff = Pi::model('user_staff');
            foreach ($rowset as $row) {
                $modelStaff->insert(array(
                    'user'  => $row->user,
                    'role'  => $row->role,
                ));
                $row->delete();
            }
        }

        return $status;
    }

    /**
     * Upgrade from previous version
     *
     * @param string $version
     *
     * @return bool
     */
    protected function from310($version)
    {
        $status = true;

        if (version_compare($version, '3.1.0', '<')) {
            $table = Pi::model('config')->getTable();
            $sql = sprintf('ALTER TABLE %s MODIFY `edit` text', $table);
            $status = $this->queryTable($sql);
            if (false === $status) {
                return $status;
            }

            $table = Pi::model('user_meta')->getTable();
            foreach (array('edit', 'admin', 'search', 'options') as $field) {
                $sql = sprintf('ALTER TABLE %s MODIFY `%s` text', $table, $field);
                $status = $this->queryTable($sql);
                if (false === $status) {
                    return $status;
                }
            }
        }

        return $status;
    }

    /**
     * Upgrade from previous version
     *
     * @param string $version
     *
     * @return bool
     */
    protected function from311($version)
    {
        $status = true;
        if (version_compare($version, '3.1.1', '<')) {
            // Add table of navigation data
            $sql =<<<'EOD'
CREATE TABLE IF NOT EXISTS `{core.module_dependency}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `dependent`       varchar(64)     NOT NULL,
  `independent`     varchar(64)     NOT NULL,

  PRIMARY KEY  (`id`)
);
EOD;
            $status = $this->querySchema($sql);
            if (false === $status) {
                return $status;
            }
        }

        return $status;
    }
}
