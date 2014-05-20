<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace   Module\Tag\Installer\Schema;

use Pi;
use Pi\Application\Installer\Schema\AbstractUpdator;

/**
 * Module schema update handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Updator110 extends AbstractUpdator
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
        $result = true;
        if (version_compare($version, '1.1.0', '<')) {

            // Add table of draft data
            $sql =<<<'EOD'
CREATE TABLE `{draft}` (
  `id`              int(10)                 unsigned NOT NULL auto_increment,
  `term`            varchar(255)            NOT NULL,
  `module`          varchar(64)             NOT NULL default '',
  `type`            varchar(64)             NOT NULL default '',
  `item`            int(10)                 unsigned NOT NULL default '0',
  `time`            int(10)                 unsigned NOT NULL default '0',
  `order`           int(10)                 unsigned NOT NULL default '0',

  PRIMARY KEY       (`id`),
  KEY `item`        (`module`, `type`, `item`),
  KEY `term`        (`term`)
);
EOD;
            $status = $this->querySchema($sql, $this->handler->getParam('module'));
            if (false === $status) {
                return $status;
            }

            $tableTag = Pi::db()->prefix('tag', 'tag');
            $sql =<<<'EOT'
ALTER TABLE %s
ADD KEY `term` (`term`);
EOT;
            $sql = sprintf($sql, $tableTag);
            $result = $this->queryTable($sql);
            if (false === $result) {
                return $result;
            }

            $tableLink = Pi::db()->prefix('link', 'tag');
            $sql =<<<'EOT'
ALTER TABLE %s
ADD `term` varchar(255) NOT NULL,
MODIFY `module` varchar(64) NOT NULL default '',
MODIFY `type` varchar(64) NOT NULL default '',
DROP key `item`,
ADD KEY `item` (`module`, `type`, `item`),
ADD KEY `term` (`term`),
DROP key `tag`,
DROP key `order`;
EOT;
            $sql = sprintf($sql, $tableLink);
            $result = $this->queryTable($sql);
            if (false === $result) {
                return $result;
            }

            $tableStats = Pi::db()->prefix('stats', 'tag');
            $sql =<<<'EOT'
ALTER TABLE %s
ADD `term` varchar(255) NOT NULL,
ADD KEY `count` (`module`, `type`, `count`),
MODIFY `module` varchar(64) NOT NULL default '',
MODIFY `type` varchar(64) NOT NULL default '',
DROP key `tag`;
EOT;
            $sql = sprintf($sql, $tableStats);
            $result = $this->queryTable($sql);
            if (false === $result) {
                return $result;
            }

            $sql =<<<'EOT'
UPDATE %s l, %s t
SET l.term = t.term
WHERE l.tag = t.id;
EOT;
            $sql = sprintf($sql, $tableLink, $tableTag);
            $result = $this->queryTable($sql);
            if (false === $result) {
                return $result;
            }

            $sql =<<<'EOT'
UPDATE %s s, %s t
SET s.term = t.term
WHERE s.tag = t.id;
EOT;
            $sql = sprintf($sql, $tableStats, $tableTag);
            $result = $this->queryTable($sql);
            if (false === $result) {
                return $result;
            }

            $tableLink = Pi::db()->prefix('link', 'tag');
            $sql =<<<'EOT'
ALTER TABLE %s
DROP `tag`;
EOT;
            $sql = sprintf($sql, $tableLink);
            $result = $this->queryTable($sql);
            if (false === $result) {
                return $result;
            }

            $tableStats = Pi::db()->prefix('stats', 'tag');
            $sql =<<<'EOT'
ALTER TABLE %s
DROP `tag`;
EOT;
            $sql = sprintf($sql, $tableStats);
            $result = $this->queryTable($sql);
            if (false === $result) {
                return $result;
            }

        }

        return $result;
    }
}
