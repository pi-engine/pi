CREATE TABLE `{article}` (
  `id`           INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `subject`      VARCHAR(255)        NOT NULL DEFAULT '',
  `subtitle`     VARCHAR(255)        NOT NULL DEFAULT '',
  `summary`      TEXT,
  `content`      LONGTEXT,
  `markup`       VARCHAR(64)         NOT NULL DEFAULT 'html',
  `image`        VARCHAR(255)        NOT NULL DEFAULT '',
  `uid`          INT(10) UNSIGNED    NOT NULL DEFAULT 0,
  `author`       INT(10) UNSIGNED    NOT NULL DEFAULT 0,
  `source`       VARCHAR(255)        NOT NULL DEFAULT '',
  `pages`        TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
  `category`     INT(10) UNSIGNED    NOT NULL DEFAULT 0,
  `status`       TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
  `active`       TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  `time_submit`  INT(10) UNSIGNED    NOT NULL DEFAULT 0,
  `time_publish` INT(10) UNSIGNED    NOT NULL DEFAULT 0,
  `time_update`  INT(10) UNSIGNED    NOT NULL DEFAULT 0,
  `user_update`  INT(10) UNSIGNED    NOT NULL DEFAULT 0,

  PRIMARY KEY (`id`),
  KEY `uid`                       (`uid`),
  KEY `author`                    (`author`),
  KEY `publish_category`          (`time_publish`, `category`),
  KEY `submit_category`           (`time_submit`, `category`),
  KEY `subject`                   (`subject`)
);

CREATE TABLE `{extended}` (
  `id`              INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `article`         INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `seo_title`       VARCHAR(255)     NOT NULL DEFAULT '',
  `seo_keywords`    VARCHAR(255)     NOT NULL DEFAULT '',
  `seo_description` VARCHAR(255)     NOT NULL DEFAULT '',
  `slug`            VARCHAR(255)              DEFAULT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY (`article`)
);

CREATE TABLE `{field}` (
  `id`    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`  VARCHAR(64)      NOT NULL DEFAULT '',
  `title` VARCHAR(255)     NOT NULL DEFAULT '',

  PRIMARY KEY (`id`),
  UNIQUE KEY (`name`)
);

CREATE TABLE `{compiled}` (
  `id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`    VARCHAR(64)      NOT NULL DEFAULT '',
  `article` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `type`    VARCHAR(64)      NOT NULL DEFAULT '',
  `content` LONGTEXT,

  PRIMARY KEY (`id`),
  UNIQUE KEY (`name`),
  KEY `article_type`              (`article`, `type`)
);

CREATE TABLE `{draft}` (
  `id`            INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `article`       INT(10) UNSIGNED    NOT NULL DEFAULT 0,
  `markup`        VARCHAR(64)         NOT NULL DEFAULT 'html',
  `detail`        LONGTEXT,
  `uid`           INT(10) UNSIGNED    NOT NULL DEFAULT 0,
  `author`        INT(10) UNSIGNED    NOT NULL DEFAULT 0,
  `category`      INT(10) UNSIGNED    NOT NULL DEFAULT 0,
  `image`         VARCHAR(255)        NOT NULL DEFAULT '',
  `time_submit`   INT(10) UNSIGNED    NOT NULL DEFAULT 0,
  `time_publish`  INT(10) UNSIGNED    NOT NULL DEFAULT 0,
  `time_update`   INT(10) UNSIGNED    NOT NULL DEFAULT 0,
  `status`        TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
  `time_save`     INT(10) UNSIGNED    NOT NULL DEFAULT 0,
  `reject_reason` VARCHAR(255)        NOT NULL DEFAULT '',

  PRIMARY KEY (`id`),
  KEY `article`         (`article`),
  KEY `uid`             (`uid`),
  KEY `time_save`       (`time_save`)
);

CREATE TABLE `{related}` (
  `id`      INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `article` INT(10) UNSIGNED    NOT NULL DEFAULT 0,
  `related` INT(10) UNSIGNED    NOT NULL DEFAULT 0,
  `order`   TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,

  PRIMARY KEY (`id`),
  KEY `article`        (`article`)
);

CREATE TABLE `{visit}` (
  `id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `article` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `time`    INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `ip`      VARCHAR(255)     NOT NULL DEFAULT '',
  `uid`     INT(10) UNSIGNED NOT NULL DEFAULT 0,

  PRIMARY KEY (`id`),
  UNIQUE KEY `article_time`   (`article`, `time`),
  KEY `time`           (`time`)
);

CREATE TABLE `{category}` (
  `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `left`        INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `right`       INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `depth`       INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `name`        VARCHAR(64)      NOT NULL DEFAULT '',
  `slug`        VARCHAR(64)               DEFAULT NULL,
  `title`       VARCHAR(64)      NOT NULL DEFAULT '',
  `description` VARCHAR(255)     NOT NULL DEFAULT '',
  `image`       VARCHAR(255)     NOT NULL DEFAULT '',

  PRIMARY KEY (`id`),
  UNIQUE KEY `name`     (`name`),
  UNIQUE KEY `slug`     (`slug`)
);

CREATE TABLE `{author}` (
  `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(64)      NOT NULL DEFAULT '',
  `photo`       VARCHAR(255)     NOT NULL DEFAULT '',
  `description` TEXT,

  PRIMARY KEY (`id`),
  KEY `name`            (`name`)
);

CREATE TABLE `{stats}` (
  `id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `article` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `visits`  INT(10) UNSIGNED NOT NULL DEFAULT 0,

  PRIMARY KEY (`id`),
  UNIQUE KEY `article`  (`article`),
  KEY `article_visits`  (`article`, `visits`)
);

CREATE TABLE `{topic}` (
  `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(64)      NOT NULL DEFAULT '',
  `content`     TEXT,
  `title`       VARCHAR(255)     NOT NULL DEFAULT '',
  `image`       VARCHAR(255)     NOT NULL DEFAULT '',
  `slug`        VARCHAR(64)               DEFAULT NULL,
  `template`    VARCHAR(64)      NOT NULL DEFAULT '',
  `description` VARCHAR(255)     NOT NULL DEFAULT '',
  `active`      TINYINT(1)       NOT NULL DEFAULT 1,
  `time_create` INT(10) UNSIGNED NOT NULL DEFAULT 0,

  PRIMARY KEY (`id`),
  UNIQUE KEY `name`     (`name`),
  UNIQUE KEY `slug`     (`slug`)
);

CREATE TABLE `{article_topic}` (
  `id`        INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `article`   INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `topic`     INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `time`      INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_pull` INT(10) UNSIGNED NOT NULL DEFAULT 0,

  PRIMARY KEY (`id`),
  KEY `article`         (`article`),
  KEY `topic`           (`topic`)
);

CREATE TABLE `{media}` (
  `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(64)      NOT NULL DEFAULT '',
  `title`       VARCHAR(255)     NOT NULL DEFAULT '',
  `type`        VARCHAR(64)      NOT NULL DEFAULT '',
  `description` VARCHAR(255)     NOT NULL DEFAULT '',
  `url`         VARCHAR(255)     NOT NULL DEFAULT '',
  `size`        INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `uid`         INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `time_upload` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `time_update` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `meta`        TEXT,

  PRIMARY KEY (`id`),
  UNIQUE KEY `name`     (`name`),
  KEY `type`            (`type`),
  KEY `uid`             (`uid`)
);

CREATE TABLE `{media_stats}` (
  `id`       INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `media`    INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `download` INT(10) UNSIGNED NOT NULL DEFAULT 0,

  PRIMARY KEY (`id`),
  UNIQUE KEY `media`    (`media`)
);

CREATE TABLE `{asset}` (
  `id`      INT(10) UNSIGNED             NOT NULL AUTO_INCREMENT,
  `media`   INT(10) UNSIGNED             NOT NULL DEFAULT 0,
  `article` INT(10) UNSIGNED             NOT NULL DEFAULT 0,
  `type`    ENUM ('attachment', 'image') NOT NULL DEFAULT 'attachment',

  PRIMARY KEY (`id`),
  UNIQUE KEY `media_article`    (`media`, `article`),
  KEY `article_type`            (`article`, `type`),
  KEY `media`                   (`media`)
);

CREATE TABLE `{asset_draft}` (
  `id`    INT(10) UNSIGNED             NOT NULL AUTO_INCREMENT,
  `media` INT(10) UNSIGNED             NOT NULL DEFAULT 0,
  `draft` VARCHAR(255)                 NOT NULL DEFAULT '',
  `type`  ENUM ('attachment', 'image') NOT NULL DEFAULT 'attachment',

  PRIMARY KEY (`id`),
  KEY `draft_type`              (`draft`, `type`)
);
