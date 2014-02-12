# Doc table
CREATE TABLE `{doc}` (
  `id`              int(10) UNSIGNED                NOT NULL AUTO_INCREMENT,
  # URL to access, required
  `url`             varchar(255)                    NOT NULL DEFAULT '',
  # Absolute path to access, optional; for uploaded doc only
  `path`            varchar(255)                    NOT NULL DEFAULT '',
  # filename, for download
  `filename`        varchar(255)                    NOT NULL DEFAULT '',

  # Encoded file attributes: mimetype, size, width, height, etc.
  `attributes`      text,
  `size`            int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `mimetype`        varchar(255)                    NOT NULL DEFAULT '',

  # Doc attributes
  `title`           varchar(255)                    NOT NULL DEFAULT '',
  `description`     varchar(255)                    NOT NULL DEFAULT '',

  `active`          tinyint(1) UNSIGNED             NOT NULL DEFAULT 0,
  `time_created`    int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `time_updated`    int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `time_deleted`    int(10) UNSIGNED                NOT NULL DEFAULT 0,

  # Application attributes
  `appkey`          varchar(64)                     NOT NULL DEFAULT '',
  `module`          varchar(64)                     NOT NULL DEFAULT '',
  # Application type for doc
  `type`            varchar(64)                     NOT NULL DEFAULT '',
  # Token to identify a group of docs just in case
  `token`           varchar(64)                     NOT NULL DEFAULT '',

  # User attributes
  `uid`             int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `ip`              varchar(64)                     NOT NULL DEFAULT '',

  # Usage stats
  `count`           int(10) UNSIGNED                NOT NULL DEFAULT 0,

  PRIMARY KEY   (`id`),
  KEY `active`  (`active`),
  KEY `uid`     (`uid`),
  KEY `module`  (`module`),
  KEY `appkey`  (`appkey`),
  KEY `application` (`appkey`, `module`, `type`)
);

# Extended meta for docs
CREATE TABLE `{meta}` (
  `id`              int(10) UNSIGNED                NOT NULL AUTO_INCREMENT,
  `doc`             int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `name`            varchar(64)                     NOT NULL DEFAULT '',
  `value`           text,

  PRIMARY KEY       (`id`),
  UNIQUE KEY `meta` (`doc`, `name`)
);

# Application table, for module management only
CREATE TABLE `{application}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `appkey`          varchar(64)           DEFAULT NULL,
  `title`           varchar(255)          NOT NULL DEFAULT '',

  PRIMARY KEY           (`id`),
  UNIQUE KEY `appkey`   (`appkey`)
);
