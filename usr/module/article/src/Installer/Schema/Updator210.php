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
class Updator210 extends AbstractUpdator
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
        $result = $this->from111($version);

        return $result;
    }

    /**
     * Upgrade from previous version
     *
     * @param string $version
     *
     * @return bool
     */
    protected function from111($version)
    {
        $result = true;
        
        // Add cluster table
        if (version_compare($version, '1.2.1', '<')) {
            $module = $this->handler->getParam('module');
            
            // Create table cluster
            $table  = Pi::model('cluster', $module)->getTable();
            $sql =<<<EOD
CREATE TABLE `{$table}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `left`            int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `right`           int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `depth`           int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `name`            varchar(64)           NOT NULL DEFAULT '',
  `slug`            varchar(64)           DEFAULT NULL,
  `title`           varchar(64)           NOT NULL DEFAULT '',
  `description`     varchar(255)          NOT NULL DEFAULT '',
  `image`           varchar(255)          NOT NULL DEFAULT '',

  PRIMARY KEY           (`id`),
  UNIQUE KEY `name`     (`name`),
  UNIQUE KEY `slug`     (`slug`)
);
EOD;
            $result = $this->querySchema($sql, $module);
            if (false === $result) {
                return $result;
            }
            
            // Add field to article table
            $tableArticle = Pi::db()->prefix('article', $module);
            $sql =<<<EOD
ALTER TABLE {$tableArticle} ADD COLUMN `cluster` int(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `category`;
ALTER TABLE {$tableArticle} ADD INDEX `cluster` (cluster);
EOD;
            $result = $this->queryTable($sql);
            if (false === $result) {
                return $result;
            }
            
            // Add field to draft table
            $tableArticle = Pi::db()->prefix('draft', $module);
            $sql =<<<EOD
ALTER TABLE {$tableArticle} ADD COLUMN `cluster` int(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `category`;
EOD;
            $result = $this->queryTable($sql);
            if (false === $result) {
                return $result;
            }
        }
        
        // Add tables for customizing fields
        if (version_compare($version, '1.3.1', '<')) {
            $module = $this->handler->getParam('module');
            
            // Add fields for table field
            $table  = Pi::db()->prefix('field', $module);
            $addSql =<<<EOD
ALTER TABLE {$table} ADD COLUMN `edit` text;
ALTER TABLE {$table} ADD COLUMN `filter` text;
ALTER TABLE {$table} ADD COLUMN `handler` text;
ALTER TABLE {$table} ADD COLUMN `type` enum('common', 'custom', 'compound') NOT NULL;
ALTER TABLE {$table} ADD COLUMN `is_edit` tinyint(1) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE {$table} ADD COLUMN `is_display` tinyint(1) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE {$table} ADD COLUMN `is_required` tinyint(1) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE {$table} ADD COLUMN `active` tinyint(1) UNSIGNED NOT NULL DEFAULT '0';
EOD;
            $result = $this->querySchema($addSql, $module);
            if (false === $result) {
                return $result;
            }
            
            // Create `compound_field` table
            $table     = Pi::model('compound_field', $module)->getTable();
            $createSql =<<<EOD
CREATE TABLE `{$table}` (
  `id`              int(10) UNSIGNED                NOT NULL AUTO_INCREMENT,
  `name`            varchar(64)                     NOT NULL,
  `compound`        varchar(64)                     NOT NULL,
  `title`           varchar(255)                    NOT NULL DEFAULT '',
  `edit`            text,
  `filter`          text,
  `is_required`     tinyint(1) UNSIGNED             NOT NULL DEFAULT '0',

  PRIMARY KEY       (`id`),
  UNIQUE KEY `name` (`compound`, `name`)
);
EOD;
            $result = $this->queryTable($createSql);
            if (false === $result) {
                return $result;
            }
        }
        
        if (version_compare($version, '1.4.2', '<')) {
            $module = $this->handler->getParam('module');
            
            // Drop `asset_draft` table
            $table  = Pi::model('asset_draft', $module)->getTable();
            $sql    =<<<EOD
DROP TABLE IF EXISTS `{$table}`;
EOD;
            $result = $this->querySchema($sql, $module);
            if (false === $result) {
                return $result;
            }
            
            // Add `cluster_article` table
            $table     = Pi::model('cluster_article', $module)->getTable();
            $createSql =<<<EOD
CREATE TABLE `{$table}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `article`         int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `cluster`         int(10) UNSIGNED      NOT NULL DEFAULT 0,

  PRIMARY KEY           (`id`),
  KEY `cluster_item`    (`cluster`, `article`)
);
EOD;
            $result = $this->queryTable($createSql);
            if (false === $result) {
                return $result;
            }
        }
        
        if (version_compare($version, '1.5.2', '<')) {
            $module = $this->handler->getParam('module');
            
            // Add `page` table
            $table     = Pi::model('page', $module)->getTable();
            $createSql =<<<EOD
CREATE TABLE `{$table}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `left`            int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `right`           int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `depth`           int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `title`           varchar(255)          NOT NULL DEFAULT '',
  `name`            varchar(64)           NOT NULL DEFAULT '',
  `controller`      varchar(32)           NOT NULL DEFAULT '',
  `action`          varchar(32)           NOT NULL DEFAULT '',
  `seo_title`       text                  DEFAULT NULL,
  `seo_keywords`    text                  DEFAULT NULL,
  `seo_description` text                  DEFAULT NULL,
  `active`          tinyint(1)            NOT NULL DEFAULT '0',
  `meta`            text                  DEFAULT NULL,

  PRIMARY KEY                   (`id`),
  UNIQUE KEY                    (`name`),
  KEY                           (`active`)
);
EOD;
            $result = $this->queryTable($createSql);
            if (false === $result) {
                return $result;
            }
            
            // Add root page
            $model  = Pi::model('page', $module);
            $data   = array(
                'id'          => null,
                'name'        => 'root',
                'title'       => _a('Null'),
            );
            $result = $model->add($data);
            if (false === $result) {
                return $result;
            }
        }
        
        // Add `active` field for category and cluster
        if (version_compare($version, '1.6.2', '<')) {
            $module = $this->handler->getParam('module');
            
            // Add fields for category table
            $table  = Pi::db()->prefix('category', $module);
            $addSql =<<<EOD
ALTER TABLE {$table} ADD COLUMN `active` tinyint(1) NOT NULL DEFAULT 0;
EOD;
            $result = $this->querySchema($addSql, $module);
            if (false === $result) {
                return $result;
            }
            
            // Update field value
            $updateSql =<<<EOD
UPDATE `{$table}` SET `active` = 1;
EOD;
            $result = $this->queryTable($updateSql);
            if (false === $result) {
                return $result;
            }
            
            // Add fields for cluster table
            $table  = Pi::db()->prefix('cluster', $module);
            $addSql =<<<EOD
ALTER TABLE {$table} ADD COLUMN `active` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE {$table} ADD COLUMN `meta` text;
EOD;
            $result = $this->querySchema($addSql, $module);
            if (false === $result) {
                return $result;
            }
            
            // Update field value
            $updateSql =<<<EOD
UPDATE `{$table}` SET `active` = 1;
EOD;
            $result = $this->queryTable($updateSql);
            if (false === $result) {
                return $result;
            }
        }
        
        // Add fields for stats and media_stats tables
        if (version_compare($version, '1.7.2', '<')) {
            $module = $this->handler->getParam('module');
            
            // Add fields for stats table
            $table  = Pi::db()->prefix('stats', $module);
            $addSql =<<<EOD
ALTER TABLE {$table} ADD COLUMN `date` enum('D','W','M','A') NOT NULL DEFAULT 'D';
ALTER TABLE {$table} ADD COLUMN `time_updated` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE {$table} DROP INDEX `article`;
ALTER TABLE {$table} ADD UNIQUE `a_date` (`article`, `date`);
EOD;
            $result = $this->querySchema($addSql, $module);
            if (false === $result) {
                return $result;
            }
            
            // Update field value
            $updateSql =<<<EOD
UPDATE `{$table}` SET `date` = 'A';
EOD;
            $result = $this->queryTable($updateSql);
            if (false === $result) {
                return $result;
            }
            
            // Add fields for media_stats table
            $table  = Pi::db()->prefix('media_stats', $module);
            $addSql =<<<EOD
ALTER TABLE {$table} ADD COLUMN `browse` int(10) UNSIGNED NOT NULL DEFAULT 0;
EOD;
            $result = $this->querySchema($addSql, $module);
            if (false === $result) {
                return $result;
            }
        }
        
        // Add SEO related fields into article table
        if (version_compare($version, '1.8.3', '<')) {
            $module = $this->handler->getParam('module');
            
            $table  = Pi::db()->prefix('article', $module);
            $addSql =<<<EOD
ALTER TABLE {$table} ADD COLUMN `seo_keywords` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE {$table} ADD COLUMN `seo_description` varchar(255) NOT NULL DEFAULT '';
EOD;
            $result = $this->querySchema($addSql, $module);
            if (false === $result) {
                return $result;
            }
        }
        
        // Removed `name` field for some table
        if (version_compare($version, '1.9.3', '<')) {
            $module = $this->handler->getParam('module');
            
            $category  = Pi::db()->prefix('category', $module);
            $cluster   = Pi::db()->prefix('cluster', $module);
            $topic     = Pi::db()->prefix('topic', $module);
            $media     = Pi::db()->prefix('media', $module);
            $sql    =<<<EOD
ALTER TABLE {$category} DROP INDEX `name`;
ALTER TABLE {$category} DROP COLUMN `name`;
ALTER TABLE {$cluster} DROP INDEX `name`;
ALTER TABLE {$cluster} DROP COLUMN `name`;
ALTER TABLE {$topic} DROP INDEX `name`;
ALTER TABLE {$topic} DROP COLUMN `name`;
ALTER TABLE {$media} DROP INDEX `name`;
ALTER TABLE {$media} DROP COLUMN `name`;
EOD;
            $result = $this->querySchema($sql, $module);
            if (false === $result) {
                return $result;
            }
        }
        
        // Add index for article_cluster_article
        if (version_compare($version, '1.9.4', '<')) {
            $module = $this->handler->getParam('module');
            
            $clusterArticle = Pi::db()->prefix('cluster_article', $module);
            $sql    =<<<EOD
ALTER TABLE {$clusterArticle} ADD INDEX (`article`);
EOD;
            $result = $this->querySchema($sql, $module);
            if (false === $result) {
                return $result;
            }
        }

        return $result;
    }
}
