CREATE TABLE `{private_message}` (
  `id`                  int(11) UNSIGNED       NOT NULL AUTO_INCREMENT,
  `uid_from`            int(11) UNSIGNED       NOT NULL DEFAULT 0,
  `uid_to`              int(11) UNSIGNED       NOT NULL DEFAULT 0,
  `content`             text                   NOT NULL DEFAULT '',
  `is_new_from`         tinyint(1) UNSIGNED    NOT NULL DEFAULT 0,
  `is_new_to`           tinyint(1) UNSIGNED    NOT NULL DEFAULT 1,
  `time_send`           int(11) UNSIGNED       NOT NULL DEFAULT 0,
  `delete_status_from`  tinyint(1) UNSIGNED    NOT NULL DEFAULT 0,
  `delete_status_to`    tinyint(1) UNSIGNED    NOT NULL DEFAULT 0,

  PRIMARY KEY                  (`id`)
);

CREATE TABLE `{notification}` (
  `id`                int(11) UNSIGNED       NOT NULL AUTO_INCREMENT,
  `uid`               int(11) UNSIGNED       NOT NULL DEFAULT 0,
  `subject`           varchar(64)            NOT NULL DEFAULT '',
  `content`           text                   NOT NULL DEFAULT '',
  `tag`               varchar(64)            NOT NULL DEFAULT '',
  `is_new`            tinyint(1) UNSIGNED    NOT NULL DEFAULT 1,
  `time_send`         int(11) UNSIGNED       NOT NULL DEFAULT 0,
  `delete_status`     tinyint(1) UNSIGNED    NOT NULL DEFAULT 0,

  PRIMARY KEY                  (`id`)
);