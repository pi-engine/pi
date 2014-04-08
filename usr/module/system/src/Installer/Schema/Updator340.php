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
class Updator340 extends AbstractUpdator
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
        if (version_compare($version, '3.3.0', '<')) {
            $updator = new Updator330($this->handler);
            $result = $updator->upgrade($version);
            if (false === $result) {
                return $result;
            }
        }
        $result = $this->from330($version);

        return $result;
    }

    /**
     * Upgrade from previous version
     *
     * @param string $version
     *
     * @return bool
     */
    protected function from330($version)
    {
        $status = true;

        if (version_compare($version, '3.4.2', '<')) {

            $table = Pi::db()->prefix('user_data');
            $sql =<<<'EOT'
ALTER TABLE %s
ADD `expire`          int(10)         unsigned    NOT NULL default '0' AFTER `time`;
EOT;
            $sql = sprintf($sql, $table);
            $status = $this->queryTable($sql);
        }

        if (version_compare($version, '3.4.1', '<')) {

            Pi::model('page')->update(array('block' => 1), array(
                'section'   => 'front',
                'controller'    => '',
                'action'        => '',
            ));

            $table = Pi::db()->prefix('user_account');
            $sql =<<<'EOT'
ALTER TABLE %s
MODIFY `gender` enum('male', 'female', 'unknown') default 'unknown';
EOT;
            $sql = sprintf($sql, $table);
            $status = $this->queryTable($sql);
        }

        if (version_compare($version, '3.4.0', '<')) {

            $table = Pi::db()->prefix('user_account');
            $sql =<<<'EOT'
ALTER TABLE %s
MODIFY `identity`        varchar(32)     default NULL;
EOT;
            $sql = sprintf($sql, $table);
            $status = $this->queryTable($sql);
        }

        if (version_compare($version, '3.3.1', '<')) {
            $sql =<<<'EOD'
CREATE TABLE `{category}` (
  `id`          int(10)         unsigned NOT NULL auto_increment,
  `title`       varchar(255)    default NULL,
  `icon`        varchar(255)    default '',
  `order`       int(5)          unsigned NOT NULL default '0',
  -- Json-encoded module list
  `modules`     text,

  PRIMARY KEY  (`id`)
);
EOD;
            $status = $this->querySchema($sql, $this->handler->getparam('module'));
            if (false === $status) {
                return $status;
            }

        }

        return $status;
    }
}
