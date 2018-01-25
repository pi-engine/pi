# Quote all table names with '{' and '}', and prefix all system tables with 'core.'

CREATE TABLE `{test}` (
  `id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `message` VARCHAR(255)     NOT NULL DEFAULT '',
  `active`  TINYINT(1)       NOT NULL DEFAULT '1',

  PRIMARY KEY (`id`)
);

CREATE TABLE `{page}` (
  `id`           INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `title`        VARCHAR(255)        NOT NULL DEFAULT '',
  `uid`          INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `time_created` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `content`      TEXT,
  `flag`         TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',

  PRIMARY KEY (`id`)
);