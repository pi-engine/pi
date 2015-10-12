CREATE TABLE `{message}` (
  `id`              INT(11) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `uid_from`        INT(11) UNSIGNED    NOT NULL DEFAULT 0,
  `uid_to`          INT(11) UNSIGNED    NOT NULL DEFAULT 0,
  `content`         TEXT,
  `time_send`       INT(11) UNSIGNED    NOT NULL DEFAULT 0,
  `is_read_from`    TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  `is_read_to`      TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  `is_deleted_from` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  `is_deleted_to`   TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,

  PRIMARY KEY (`id`)
);

CREATE TABLE `{notification}` (
  `id`         INT(11) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `uid`        INT(11) UNSIGNED    NOT NULL DEFAULT 0,
  `subject`    VARCHAR(255)        NOT NULL,
  `content`    TEXT,
  `tag`        VARCHAR(64)         NOT NULL DEFAULT '',
  `time_send`  INT(11) UNSIGNED    NOT NULL DEFAULT 0,
  `is_read`    TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  `is_deleted` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,

  PRIMARY KEY (`id`)
);