CREATE TABLE `{detail}` (
  `id`              int(10) UNSIGNED                NOT NULL AUTO_INCREMENT,
  `name`            varchar(255)                    NOT NULL DEFAULT '',
  `title`           varchar(255)                    NOT NULL DEFAULT '',
  `raw_name`        varchar(255)                    NOT NULL DEFAULT '',
  `mimetype`        varchar(64)                     NOT NULL DEFAULT '',
  `description`     varchar(255)                    NOT NULL DEFAULT '',
  `uid`             int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `url`             varchar(255)                    NOT NULL DEFAULT '',
  `filesize`        int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `size_width`      int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `size_height`     int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `ip`              varchar(64)                     NOT NULL DEFAULT '',
  `module`          varchar(64)                     NOT NULL DEFAULT '',
  `application`     int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `category`        int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `delete`          tinyint(1) UNSIGNED             NOT NULL DEFAULT 0,
  `active`          tinyint(1) UNSIGNED             NOT NULL DEFAULT 0,
  `time_upload`     int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `time_update`     int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `meta`            text                            NOT NULL DEFAULT '',

  PRIMARY KEY                     (`id`),
  KEY `uid`                       (`uid`),
  KEY `module`                    (`module`),
  KEY `application`               (`application`)
);

CREATE TABLE `{extended}` (
  `id`              int(10) UNSIGNED                NOT NULL AUTO_INCREMENT,
  `media`           int(10) UNSIGNED                NOT NULL DEFAULT 0,

  PRIMARY KEY                     (`id`),
  UNIQUE KEY                      (`media`)
);

CREATE TABLE `{application}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `appkey`          varchar(255)          NOT NULL DEFAULT '',
  `name`            varchar(255)          NOT NULL DEFAULT '',
  `title`           varchar(255)          NOT NULL DEFAULT '',

  PRIMARY KEY           (`id`),
  KEY `name`            (`name`)
);

CREATE TABLE `{category}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `module`          varchar(64)           NOT NULL DEFAULT '',
  `name`            varchar(64)           NOT NULL DEFAULT '',
  `title`           varchar(255)          NOT NULL DEFAULT '',
  `active`          tinyint(1)            NOT NULL DEFAULT 1,

  PRIMARY KEY                   (`id`),
  UNIQUE KEY `module_category`  (`module`, `name`)
);

CREATE TABLE `{statistics}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `media`           int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `fetch_count`     int(10) UNSIGNED      NOT NULL DEFAULT 0,

  PRIMARY KEY           (`id`),
  KEY `media`           (`media`)
);
