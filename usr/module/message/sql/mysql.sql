CREATE TABLE `{message}` (
  `id`                  int(11) UNSIGNED       NOT NULL AUTO_INCREMENT,
  `uid_from`            int(11) UNSIGNED       NOT NULL DEFAULT 0,
  `uid_to`              int(11) UNSIGNED       NOT NULL DEFAULT 0,
  `content`             text,
  `time_send`           int(11) UNSIGNED       NOT NULL DEFAULT 0,
  `is_read_from`        tinyint(1) UNSIGNED    NOT NULL DEFAULT 0,
  `is_read_to`          tinyint(1) UNSIGNED    NOT NULL DEFAULT 0,
  `is_deleted_from`     tinyint(1) UNSIGNED    NOT NULL DEFAULT 0,
  `is_deleted_to`       tinyint(1) UNSIGNED    NOT NULL DEFAULT 0,

  PRIMARY KEY                  (`id`)
);

CREATE TABLE `{notification}` (
  `id`                int(11) UNSIGNED       NOT NULL AUTO_INCREMENT,
  `uid`               int(11) UNSIGNED       NOT NULL DEFAULT 0,
  `subject`           varchar(255)           NOT NULL,
  `content`           text,
  `tag`               varchar(64)            NOT NULL DEFAULT '',
  `time_send`         int(11) UNSIGNED       NOT NULL DEFAULT 0,
  `is_read`           tinyint(1) UNSIGNED    NOT NULL DEFAULT 0,
  `is_deleted`        tinyint(1) UNSIGNED    NOT NULL DEFAULT 0,

  PRIMARY KEY                  (`id`)
);