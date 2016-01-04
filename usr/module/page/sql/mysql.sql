# Quote all table names with '{' and '}', and prefix all system tables with 'core.'

CREATE TABLE `{page}` (
  `id`              INT(10) UNSIGNED                 NOT NULL AUTO_INCREMENT,
  `title`           VARCHAR(255)                     NOT NULL DEFAULT '',
  `name`            VARCHAR(64)                               DEFAULT NULL,
  `user`            INT(10)                 UNSIGNED NOT NULL DEFAULT '0',
  `time_created`    INT(10)                 UNSIGNED NOT NULL DEFAULT '0',
  `time_updated`    INT(10)                 UNSIGNED NOT NULL DEFAULT '0',
  `active`          TINYINT(1)                       NOT NULL DEFAULT '1',
  -- Page content, or template name for phtml type
  `content`         MEDIUMTEXT,
  -- Markup type: text, html, markdown, phtml
  `markup`          VARCHAR(64)                      NOT NULL DEFAULT 'text',

  -- SEO slug for URL
  `slug`            VARCHAR(64)                               DEFAULT NULL,
  `clicks`          INT(10)                 UNSIGNED NOT NULL DEFAULT '0',
  `seo_title`       VARCHAR(255)                     NOT NULL DEFAULT '',
  `seo_keywords`    VARCHAR(255)                     NOT NULL DEFAULT '',
  `seo_description` VARCHAR(255)                     NOT NULL DEFAULT '',

  -- Order in navigation, '0' for not navigated
  `nav_order`       SMALLINT(5)             UNSIGNED NOT NULL DEFAULT '0',

  -- For rendering
  `theme`           VARCHAR(64)                      NOT NULL DEFAULT '',
  `layout`          VARCHAR(64)                      NOT NULL DEFAULT '',
  `template`        VARCHAR(64)                      NOT NULL DEFAULT '',

  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`)
);


# NOT used yet. Solely for demonstration, will be dropped
CREATE TABLE `{stats}` (
  `id`     INT(10) UNSIGNED                 NOT NULL AUTO_INCREMENT,
  `page`   INT(10)                 UNSIGNED NOT NULL DEFAULT '0',
  `clicks` INT(10)                 UNSIGNED NOT NULL DEFAULT '0',

  PRIMARY KEY (`id`),
  UNIQUE KEY `page` (`page`)
);