# Doc table
CREATE TABLE `{doc}` (
  `id`              int(10) UNSIGNED                NOT NULL AUTO_INCREMENT,
  # URL to access, required
  `url`             varchar(255)                    NOT NULL DEFAULT '',
  # Absolute path to access, optional; for uploaded doc only
  `path`            varchar(255)                    NOT NULL DEFAULT '',
  # filename, optional?
  `filename`        varchar(255)                    NOT NULL DEFAULT '',

  # file attributes: mimetype, filesize, width, height, etc.
  `attributes`      text,
  #`mimetype`        varchar(64)                     NOT NULL DEFAULT '',
  #`filesize`        int(10) UNSIGNED                NOT NULL DEFAULT 0,
  #`size_width`      int(10) UNSIGNED                NOT NULL DEFAULT 0,
  #`size_height`     int(10) UNSIGNED                NOT NULL DEFAULT 0,

  # Doc attributes
  `title`           varchar(255)                    NOT NULL DEFAULT '',
  `description`     varchar(255)                    NOT NULL DEFAULT '',

  `active`          tinyint(1) UNSIGNED             NOT NULL DEFAULT 0,
  `time_created`    int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `time_updated`    int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `time_deleted`    int(10) UNSIGNED                NOT NULL DEFAULT 0,

  # Application attributes
  `appkey`          varchar(255)                    NOT NULL DEFAULT '',
  `module`          varchar(64)                     NOT NULL DEFAULT '',
  `category`        varchar(64)                     NOT NULL DEFAULT '',

  # User attributes
  `uid`             int(10) UNSIGNED                NOT NULL DEFAULT 0,
  `ip`              varchar(64)                     NOT NULL DEFAULT '',

  # Extra data
  `meta`            text,

  PRIMARY KEY   (`id`),
  KEY `active`  (`active`),
  KEY `uid`     (`uid`),
  KEY `module`  (`module`),
  KEY `appkey`  (`appkey`),
  KEY `application` (`appkey`, `module`, `category`)
);

# Doc stats
CREATE TABLE `{stats}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `doc`             int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `count`           int(10) UNSIGNED      NOT NULL DEFAULT 0,

  PRIMARY KEY           (`id`),
  UNIQUE KEY `doc`      (`doc`)
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
  `appkey`          varchar(255)          DEFAULT NULL,
  `name`            varchar(255)          DEFAULT NULL,
  `title`           varchar(255)          NOT NULL DEFAULT '',

  PRIMARY KEY           (`id`),
  UNIQUE KEY `name`     (`name`),
  UNIQUE KEY `appkey`   (`appkey`)
);

CREATE TABLE `{category}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `appkey`          varchar(64)           NOT NULL DEFAULT '',
  `module`          varchar(64)           NOT NULL DEFAULT '',
  `name`            varchar(64)           NOT NULL DEFAULT '',
  `title`           varchar(255)          NOT NULL DEFAULT '',

  PRIMARY KEY (`id`),
  UNIQUE KEY `category`  (`appkey`, `module`, `name`)
);
