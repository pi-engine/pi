CREATE TABLE `{article}` (
  `id`              int(10) UNSIGNED                NOT NULL AUTO_INCREMENT,
  `subject`         varchar(255)                    NOT NULL DEFAULT '',
  `subtitle`        varchar(255)                    NOT NULL DEFAULT '',
  `summary`         text,
  `content`         longtext,
  `markup`          varchar(64)                     NOT NULL DEFAULT 'html',
  `image`           varchar(255)                    NOT NULL DEFAULT '',
  `uid`             int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `author`          int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `source`          varchar(255)                    NOT NULL DEFAULT '',
  `pages`           tinyint(3) UNSIGNED             NOT NULL DEFAULT 0,
  `category`        int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `cluster`         int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `status`          tinyint(3) UNSIGNED             NOT NULL DEFAULT 0,
  `active`          tinyint(1) UNSIGNED             NOT NULL DEFAULT 0,
  `time_submit`     int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `time_publish`    int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `time_update`     int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `user_update`     int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `seo_keywords`    varchar(255)                    NOT NULL DEFAULT '',
  `seo_description` varchar(255)                    NOT NULL DEFAULT '',

  PRIMARY KEY                     (`id`),
  KEY `uid`                       (`uid`),
  KEY `author`                    (`author`),
  KEY `publish_category`          (`time_publish`, `category`),
  KEY `submit_category`           (`time_submit`, `category`),
  KEY `subject`                   (`subject`),
  KEY `cluster`                   (`cluster`)
);

CREATE TABLE `{field}` (
  `id`              int(10) UNSIGNED                NOT NULL AUTO_INCREMENT,
  `name`            varchar(64)                     NOT NULL DEFAULT '',
  `title`           varchar(255)                    NOT NULL DEFAULT '',
  `edit`            text,
  `filter`          text,
  `handler`         text,
  `type`            enum('common', 'custom', 'compound') NOT NULL,
  `is_edit`         tinyint(1) UNSIGNED             NOT NULL DEFAULT '0',
  `is_display`      tinyint(1) UNSIGNED             NOT NULL DEFAULT '0',
  `is_required`     tinyint(1) UNSIGNED             NOT NULL DEFAULT '0',
  `active`          tinyint(1) UNSIGNED             NOT NULL DEFAULT '0',

  PRIMARY KEY                     (`id`),
  UNIQUE KEY                      (`name`)
);

CREATE TABLE `{compound_field}` (
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

CREATE TABLE `{compiled}` (
  `id`              int(10) UNSIGNED                NOT NULL AUTO_INCREMENT,
  `name`            varchar(64)                     NOT NULL DEFAULT '',
  `article`         int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `type`            varchar(64)                     NOT NULL DEFAULT '',
  `content`         longtext,

  PRIMARY KEY                     (`id`),
  UNIQUE KEY                      (`name`),
  KEY `article_type`              (`article`, `type`)
);

CREATE TABLE `{draft}` (
  `id`              int(10) UNSIGNED                NOT NULL AUTO_INCREMENT,
  `article`         int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `markup`          varchar(64)                     NOT NULL DEFAULT 'html',
  `detail`          LONGTEXT,
  `uid`             int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `category`        int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `time_submit`     int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `time_publish`    int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `time_update`     int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `status`          tinyint(3) UNSIGNED             NOT NULL DEFAULT 0,
  `time_save`       int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `reject_reason`   varchar(255)                    NOT NULL DEFAULT '',

  PRIMARY KEY           (`id`),
  KEY `article`         (`article`),
  KEY `uid`             (`uid`),
  KEY `time_save`       (`time_save`)
);

CREATE TABLE `{category}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `left`            int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `right`           int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `depth`           int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `slug`            varchar(64)           DEFAULT NULL,
  `title`           varchar(64)           NOT NULL DEFAULT '',
  `description`     varchar(255)          NOT NULL DEFAULT '',
  `image`           varchar(255)          NOT NULL DEFAULT '',
  `active`          tinyint(1)            NOT NULL DEFAULT 0,

  PRIMARY KEY           (`id`),
  UNIQUE KEY `slug`     (`slug`)
);

# Another axis which as same as category
CREATE TABLE `{cluster}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `left`            int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `right`           int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `depth`           int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `slug`            varchar(64)           DEFAULT NULL,
  `title`           varchar(64)           NOT NULL DEFAULT '',
  `description`     varchar(255)          NOT NULL DEFAULT '',
  `image`           varchar(255)          NOT NULL DEFAULT '',
  `active`          tinyint(1)            NOT NULL DEFAULT 0,
  `meta`            text,

  PRIMARY KEY           (`id`),
  UNIQUE KEY `slug`     (`slug`)
);

CREATE TABLE `{cluster_article}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `article`         int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `cluster`         int(10) UNSIGNED      NOT NULL DEFAULT 0,

  PRIMARY KEY           (`id`),
  KEY                   (`article`),
  KEY `cluster_item`    (`cluster`, `article`)
);

CREATE TABLE `{author}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `name`            varchar(64)           NOT NULL DEFAULT '',
  `photo`           varchar(255)          NOT NULL DEFAULT '',
  `description`     text,

  PRIMARY KEY           (`id`),
  KEY `name`            (`name`)
);

CREATE TABLE `{stats}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `article`         int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `date`            enum('D','W','M','A') NOT NULL DEFAULT 'D',
  `visits`          int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `time_updated`    int(10) UNSIGNED      NOT NULL DEFAULT 0,

  PRIMARY KEY           (`id`),
  UNIQUE KEY `a_date`   (`article`, `date`),
  KEY `article_visits`  (`article`, `visits`)
);

CREATE TABLE `{topic}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `content`         text,
  `title`           varchar(255)          NOT NULL DEFAULT '',
  `image`           varchar(255)          NOT NULL DEFAULT '',
  `slug`            varchar(64)           DEFAULT NULL,
  `template`        varchar(64)           NOT NULL DEFAULT '',
  `description`     varchar(255)          NOT NULL DEFAULT '',
  `active`          tinyint(1)            NOT NULL DEFAULT 1,
  `time_create`     int(10) UNSIGNED      NOT NULL DEFAULT 0,

  PRIMARY KEY           (`id`),
  UNIQUE KEY `slug`     (`slug`)
);

CREATE TABLE `{article_topic}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `article`         int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `topic`           int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `time`            int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `user_pull`       int(10) UNSIGNED      NOT NULL DEFAULT 0,

  PRIMARY KEY           (`id`),
  KEY `article`         (`article`),
  KEY `topic`           (`topic`)
);

CREATE TABLE `{media}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `title`           varchar(255)          NOT NULL DEFAULT '',
  `type`            varchar(64)           NOT NULL DEFAULT '',
  `description`     varchar(255)          NOT NULL DEFAULT '',
  `url`             varchar(255)          NOT NULL DEFAULT '',
  `size`            int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `uid`             int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `time_upload`     int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `time_update`     int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `meta`            text,

  PRIMARY KEY           (`id`),
  KEY `type`            (`type`),
  KEY `uid`             (`uid`)
);

CREATE TABLE `{media_stats}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `media`           int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `download`        int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `browse`          int(10) UNSIGNED      NOT NULL DEFAULT 0,

  PRIMARY KEY           (`id`),
  UNIQUE KEY `media`    (`media`)
);

CREATE TABLE `{asset}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `media`           int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `article`         int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `type`            enum('attachment', 'image') NOT NULL DEFAULT 'attachment',

  PRIMARY KEY                   (`id`),
  UNIQUE KEY `media_article`    (`media`, `article`),
  KEY `article_type`            (`article`, `type`),
  KEY `media`                   (`media`)
);

CREATE TABLE `{page}` (
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
