<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Installer\Schema;

use Pi;
use Pi\Application\Installer\Schema\AbstractUpdator;

/**
 * Module schema update handler
 *
 * @author Zongshu <lin40553024@163.com>
 */
class Updator110 extends AbstractUpdator
{
    /**
     * Update article table schema
     *
     * @param string $version
     *
     * @return bool
     */
    public function upgrade($version)
    {
        $result = $this->from101($version);

        return $result;
    }

    /**
     * Upgrade from previous version
     *
     * @param string $version
     *
     * @return bool
     */
    protected function from101($version)
    {
        $result = true;
        if (version_compare($version, '1.1.0', '<')) {
            $module = $this->handler->getParam('module');
            
            // Modify field type of article table
            $tableArticle = Pi::db()->prefix('article', $module);
            $sql =<<<EOD
ALTER TABLE {$tableArticle} MODIFY `summary` text;
ALTER TABLE {$tableArticle} MODIFY `content` longtext;
EOD;
            $result = $this->queryTable($sql);
            if (false === $result) {
                return $result;
            }

            // Modify field type of compiled table
            $tableCompiled = Pi::db()->prefix('compiled', $module);
            $sql =<<<EOD
ALTER TABLE {$tableCompiled} MODIFY `content` longtext;
EOD;
            $result = $this->queryTable($sql);
            if (false === $result) {
                return $result;
            }

            // Modify field type of draft table
            $tableDraft = Pi::db()->prefix('draft', $module);
            $sql =<<<EOD
ALTER TABLE {$tableDraft} MODIFY `detail` longtext;
EOD;
            $result = $this->queryTable($sql);
            if (false === $result) {
                return $result;
            }
            
            // Modify field type of author table
            $tableAuthor = Pi::db()->prefix('author', $module);
            $sql =<<<EOD
ALTER TABLE {$tableAuthor} MODIFY `description` text;
EOD;
            $result = $this->queryTable($sql);
            if (false === $result) {
                return $result;
            }
            
            // Modify field type of topic table
            $tableTopic = Pi::db()->prefix('topic', $module);
            $sql =<<<EOD
ALTER TABLE {$tableTopic} MODIFY `content` text;
EOD;
            $result = $this->queryTable($sql);
            if (false === $result) {
                return $result;
            }

            // Modify field type of media table
            $tableMedia = Pi::db()->prefix('media', $module);
            $sql =<<<EOD
ALTER TABLE {$tableMedia} MODIFY `meta` text;
EOD;
            $result = $this->queryTable($sql);
            if (false === $result) {
                return $result;
            }
        }

        return $result;
    }
}
