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
  `status`          tinyint(3) UNSIGNED             NOT NULL DEFAULT 0,
  `active`          tinyint(1) UNSIGNED             NOT NULL DEFAULT 0,
  `time_submit`     int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `time_publish`    int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `time_update`     int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `user_update`     int(10) UNSIGNED                NOT NULL DEFAULT 0,

  PRIMARY KEY                     (`id`),
  KEY `uid`                       (`uid`),
  KEY `author`                    (`author`),
  KEY `publish_category`          (`time_publish`, `category`),
  KEY `submit_category`           (`time_submit`, `category`),
  KEY `subject`                   (`subject`)
);

CREATE TABLE `{extended}` (
  `id`              int(10) UNSIGNED                NOT NULL AUTO_INCREMENT,
  `article`         int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `seo_title`       varchar(255)                    NOT NULL DEFAULT '',
  `seo_keywords`    varchar(255)                    NOT NULL DEFAULT '',
  `seo_description` varchar(255)                    NOT NULL DEFAULT '',
  `slug`            varchar(255)                    DEFAULT NULL,

  PRIMARY KEY                     (`id`),
  UNIQUE KEY                      (`article`)
);

CREATE TABLE `{field}` (
  `id`              int(10) UNSIGNED                NOT NULL AUTO_INCREMENT,
  `name`            varchar(64)                     NOT NULL DEFAULT '',
  `title`           varchar(255)                    NOT NULL DEFAULT '',

  PRIMARY KEY                     (`id`),
  UNIQUE KEY                      (`name`)
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
  `author`          int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `category`        int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `image`           varchar(255)                    NOT NULL DEFAULT '',
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

CREATE TABLE `{related}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `article`         int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `related`         int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `order`           tinyint(3) UNSIGNED   NOT NULL DEFAULT 0,

  PRIMARY KEY          (`id`),
  KEY `article`        (`article`)
);

CREATE TABLE `{visit}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `article`         int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `time`            int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `ip`              varchar(255)          NOT NULL DEFAULT '',
  `uid`             int(10) UNSIGNED      NOT NULL DEFAULT 0,

  PRIMARY KEY                 (`id`),
  UNIQUE KEY `article_time`   (`article`,`time`),
  KEY        `time`           (`time`)
);

CREATE TABLE `{category}` (
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
  `visits`          int(10) UNSIGNED      NOT NULL DEFAULT 0,

  PRIMARY KEY           (`id`),
  UNIQUE KEY `article`  (`article`),
  KEY `article_visits`  (`article`, `visits`)
);

CREATE TABLE `{topic}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `name`            varchar(64)           NOT NULL DEFAULT '',
  `content`         text,
  `title`           varchar(255)          NOT NULL DEFAULT '',
  `image`           varchar(255)          NOT NULL DEFAULT '',
  `slug`            varchar(64)           DEFAULT NULL,
  `template`        varchar(64)           NOT NULL DEFAULT '',
  `description`     varchar(255)          NOT NULL DEFAULT '',
  `active`          tinyint(1)            NOT NULL DEFAULT 1,
  `time_create`     int(10) UNSIGNED      NOT NULL DEFAULT 0,

  PRIMARY KEY           (`id`),
  UNIQUE KEY `name`     (`name`),
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
  `name`            varchar(64)           NOT NULL DEFAULT '',
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
  UNIQUE KEY `name`     (`name`),
  KEY `type`            (`type`),
  KEY `uid`             (`uid`)
);

CREATE TABLE `{media_stats}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `media`           int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `download`        int(10) UNSIGNED      NOT NULL DEFAULT 0,

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

CREATE TABLE `{asset_draft}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `media`           int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `draft`           varchar(255)          NOT NULL DEFAULT '',
  `type`            enum('attachment', 'image') NOT NULL DEFAULT 'attachment',

  PRIMARY KEY                   (`id`),
  KEY `draft_type`              (`draft`, `type`)
);
