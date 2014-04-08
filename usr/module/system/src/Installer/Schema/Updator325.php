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
        if (version_compare($version, '3.2.4', '<')) {

            Pi::model('route')->update(array('name' => 'sysuser'), array('name' => 'user'));
            Pi::registry('route')->flush();

            $tablesDrop = array(
                'acl_edge',
                'acl_inherit',
                'acl_privilege',
                'acl_resource',
                'acl_rule',
                'user_meta',
                'user_profile'
            );
            foreach ($tablesDrop as $model) {
                $table = Pi::db()->prefix($model);
                $sql = sprintf('DROP TABLE IF EXISTS %s', $table);
                $status = $this->queryTable($sql);
            }

            $tablesRename = array(
                'acl_role'  => 'role',
                'user_repo' => 'user_data',
            );
            foreach ($tablesRename as $old => $new) {
                $tableOld = Pi::db()->prefix($old);
                $tableNew = Pi::db()->prefix($new);
                $sql = sprintf('RENAME TABLE %s TO %s', $tableOld, $tableNew);
                $status = $this->queryTable($sql);
            }

            $table = Pi::db()->prefix('navigation_node');
            $sql = sprintf(
                'ALTER TABLE %s ADD KEY `nav_name` UNIQUE KEY (`navigation`)',
                $table
            );
            $status = $this->queryTable($sql);

            $table = Pi::db()->prefix('page');
            $sql =<<<'EOT'
ALTER TABLE %s
ADD `permission` varchar(64) NOT NULL default '' AFTER `action`,
ADD `cache_type` enum('page', 'action') NOT NULL AFTER `permission`;
EOT;
            $sql = sprintf($sql, $table);
            $status = $this->queryTable($sql);

            $table = Pi::db()->prefix('session');
            $sql = sprintf(
                'ALTER TABLE %s '
                . 'ADD `uid` int(10) unsigned NOT NULL default \'0\' AFTER `lifetime`',
                $table
            );
            $status = $this->queryTable($sql);

            $table = Pi::db()->prefix('user_account');
            $sql =<<<'EOT'
ALTER TABLE %s
MODIFY `email` varchar(64) default NULL,
MODIFY `name` varchar(255) default NULL,
MODIFY `active` tinyint(1) unsigned NOT NULL default '0',
ADD `avatar` varchar(255) NOT NULL default '' AFTER `name`,
ADD `gender` enum('male', 'female', 'unknown') NOT NULL AFTER `avatar`,
ADD `birthdate` varchar(10) NOT NULL default '' AFTER `gender`,
ADD `time_created` int(10) unsigned NOT NULL default '0' AFTER `active`,
ADD `time_activated` int(10) unsigned NOT NULL default '0' AFTER `time_created`,
ADD `time_disabled` int(10) unsigned NOT NULL default '0' AFTER `time_activated`,
ADD `time_deleted` int(10) unsigned NOT NULL default '0' AFTER `time_disabled`,
ADD KEY `name` UNIQUE KEY (`name`),
ADD KEY `status` KEY (`active`);
EOT;
            $sql = sprintf($sql, $table);
            $status = $this->queryTable($sql);

            $table = Pi::db()->prefix('user_data');
            $sql =<<<'EOT'
ALTER TABLE %s
CHANGE `user` `uid` int(10) unsigned NOT NULL default '0',
CHANGE `type` `name` varchar(64) NOT NULL,
CHANGE `content` `value` text default NULL,
ADD `time` int(10) unsigned NOT NULL default '0' AFTER `name`,
ADD `value_int` int(10) default NULL AFTER `value`,
ADD `value_multi` text default NULL AFTER `value_int`,
ADD KEY `user_data_name` UNIQUE KEY (`uid`, `module`, `name`),
DROP KEY `key`;
EOT;
            $sql = sprintf($sql, $table);
            $status = $this->queryTable($sql);

            $table = Pi::db()->prefix('user_role');
            $sql =<<<'EOT'
ALTER TABLE %s
CHANGE `user` `uid` int(10) unsigned NOT NULL default '0',
MODIFY `role` varchar(64) NOT NULL,
ADD `section` enum('front', 'admin') NOT NULL AFTER `role`,
ADD KEY `section_user` UNIQUE KEY (`section`, `uid`, `role`),
DROP KEY `user`;
EOT;
            $sql = sprintf($sql, $table);
            $status = $this->queryTable($sql);
            Pi::model('user_role')->update(array('section' => 'front'));
            $tableStaff = Pi::db()->prefix('user_staff');
            $sql = sprintf(
                'INSERT INTO %s (`uid`, `role`, `section`) '
                . 'SELECT (`user`, `role`, \'admin\') FROM `%s`',
                $table,
                $tableStaff
            );
            $status = $this->queryTable($sql);
            $sql = sprintf('DROP TABLE IF EXISTS %s', $tableStaff);
            $status = $this->queryTable($sql);


            $sql =<<<'EOD'
# Permission resources
CREATE TABLE IF NOT EXISTS `{core.permission_resource}` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `section`         varchar(64)     NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',
  -- Resource name: page - <module-controller>; specific - <module-resource>
  `name`            varchar(64)     NOT NULL    default '',
  `title`           varchar(255)    NOT NULL    default '',
  -- system - created on module installation; custom
  `type`            varchar(64)     NOT NULL    default '',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `resource_name` (`section`, `module`, `name`, `type`)
);

# Permission rules
CREATE TABLE IF NOT EXISTS `{core.permission_rule}` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  -- Resource name or id
  `resource`        varchar(64)     NOT NULL    default '',
  -- Resource item name or id, optional
  #`item`            varchar(64)     default NULL,
  `module`          varchar(64)     NOT NULL    default '',
  `section`         enum('front', 'admin')      NOT NULL,
  `role`            varchar(64)     NOT NULL,
  -- Permission value: 0 - allowed; 1 - denied
  #`deny`            tinyint(1)      unsigned NOT NULL default '0',

  PRIMARY KEY  (`id`),
  #KEY `item` (`item`),
  #KEY `role` (`role`),
  UNIQUE KEY `section_module_perm` (`section`, `module`, `resource`, `role`)
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
