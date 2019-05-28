# Pi Engine schema
# http://piengine.org
# Author: Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
# --------------------------------------------------------

# ------------------------------------------------------
# User
# >>>>

# Entity meta for custom user profile fields
CREATE TABLE `{profile}`
(
    `id`  INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `uid` INT(10) UNSIGNED NOT NULL,
    -- Custom profile field

    PRIMARY KEY (`id`),
    UNIQUE KEY `uid` (`uid`)
);

# Entity for user profile compound fields
CREATE TABLE `{compound}`
(
    `id`       INT(10) UNSIGNED     NOT NULL AUTO_INCREMENT,
    `uid`      INT(10) UNSIGNED     NOT NULL,
    -- Compound name, stored in table `field`
    `compound` VARCHAR(64)          NOT NULL,
    -- Field set key, integer
    `set`      SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
    -- Compound field name, stored in table `compound_field`
    `field`    VARCHAR(64)          NOT NULL,
    `value`    TEXT,

    PRIMARY KEY (`id`),
    UNIQUE KEY `field` (`uid`, `compound`, `set`, `field`)
);

# Entity meta for all profile fields: account, basic profile and custom fields
CREATE TABLE `{field}`
(
    `id`          SMALLINT(5) UNSIGNED                    NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(64)                             NOT NULL,
    `module`      VARCHAR(64)                             NOT NULL DEFAULT '',
    `title`       VARCHAR(255)                            NOT NULL DEFAULT '',
    -- Specs for edit form element, filters and validators, encoded with json
    `edit`        TEXT,
    -- Filter for display value
    `filter`      TEXT,
    -- Handler for custom compound
    `handler`     TEXT,

    -- Field type, default as 'profile'
    `type`        ENUM ('profile', 'account', 'compound') NOT NULL,

    -- Is editable by user
    `is_edit`     TINYINT(1) UNSIGNED                     NOT NULL DEFAULT '0',
    -- Is capable for searching user
    `is_search`   TINYINT(1) UNSIGNED                     NOT NULL DEFAULT '0',
    -- Available for display
    `is_display`  TINYINT(1) UNSIGNED                     NOT NULL DEFAULT '0',
    -- Required by profile edit
    `is_required` TINYINT(1) UNSIGNED                     NOT NULL DEFAULT '0',

    -- Available, usually set by module activation/deactivation
    `active`      TINYINT(1) UNSIGNED                     NOT NULL DEFAULT '0',

    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
);

# Entity meta for compound fields and custom compound fields
CREATE TABLE `{compound_field}`
(
    `id`          SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(64)          NOT NULL,
    `compound`    VARCHAR(64)          NOT NULL,
    `module`      VARCHAR(64)          NOT NULL DEFAULT '',
    `title`       VARCHAR(255)         NOT NULL DEFAULT '',

    `edit`        TEXT,
    `filter`      TEXT,

    -- Required by profile edit
    `is_required` TINYINT(1) UNSIGNED  NOT NULL DEFAULT '0',

    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`compound`, `name`)
);

# Display group for profile fields
CREATE TABLE `{display_group}`
(
    `id`       INT(10) UNSIGNED     NOT NULL AUTO_INCREMENT,
    `title`    VARCHAR(255)         NOT NULL DEFAULT '',
    `order`    SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
    -- Compound name;
    `compound` VARCHAR(64)                   DEFAULT NULL,

    PRIMARY KEY (`id`)
);

# Display grouping and order of field
CREATE TABLE `{display_field}`
(
    `id`    INT(10) UNSIGNED     NOT NULL AUTO_INCREMENT,
    -- Profile field name;
    -- Or compound field name if `compound` is specified in table 'display_group'
    `field` VARCHAR(64)          NOT NULL DEFAULT '',
    `group` INT(10) UNSIGNED     NOT NULL DEFAULT '0',
    `order` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',

    PRIMARY KEY (`id`),
    UNIQUE KEY `group_field` (`group`, `field`)
);

# Timeline meta
CREATE TABLE `{timeline}`
(
    `id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`    VARCHAR(64)      NOT NULL DEFAULT '',
    `title`   VARCHAR(255)     NOT NULL DEFAULT '',
    `module`  VARCHAR(64)      NOT NULL DEFAULT '',
    `icon`    VARCHAR(255)     NOT NULL DEFAULT '',
    `app_key` VARCHAR(32)      NOT NULL DEFAULT '',
    `active`  TINYINT(1)       NOT NULL DEFAULT '0',

    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`, `app_key`)
);

# Activity meta
CREATE TABLE `{activity}`
(
    `id`          INT(10) UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(64)          NOT NULL DEFAULT '',
    `title`       VARCHAR(255)         NOT NULL DEFAULT '',
    `description` TEXT,
    `module`      VARCHAR(64)          NOT NULL DEFAULT '',
    -- Render template
    `template`    VARCHAR(255)         NOT NULL DEFAULT '',
    `icon`        VARCHAR(255)         NOT NULL DEFAULT '',
    `active`      TINYINT(1) UNSIGNED  NOT NULL DEFAULT '0',
    -- Display order, '0' for hidden
    `display`     SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',

    -- Callback to get user activity data
    `callback`    VARCHAR(64)          NOT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
);

# Quicklinks
CREATE TABLE `{quicklink}`
(
    `id`      INT(10) UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`    VARCHAR(64)          NOT NULL DEFAULT '',
    `title`   VARCHAR(255)         NOT NULL DEFAULT '',
    `module`  VARCHAR(64)          NOT NULL DEFAULT '',
    `link`    VARCHAR(255)         NOT NULL DEFAULT '',
    `icon`    VARCHAR(255)         NOT NULL DEFAULT '',
    `active`  TINYINT(1) UNSIGNED  NOT NULL DEFAULT '0',
    -- Display order, '0' for hidden
    `display` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',

    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
);

# Timeline for user activities
CREATE TABLE `{timeline_log}`
(
    `id`       INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `uid`      INT(10) UNSIGNED NOT NULL,
    -- Timeline name, defined in table `timeline`
    `timeline` VARCHAR(64)      NOT NULL DEFAULT '',
    `module`   VARCHAR(64)      NOT NULL DEFAULT '',
    `message`  TEXT,
    `data`     VARCHAR(64)      NOT NULL DEFAULT '',
    `link`     VARCHAR(255)     NOT NULL DEFAULT '',
    `time`     INT(11) UNSIGNED NOT NULL,

    PRIMARY KEY (`id`),
    KEY (`uid`)
);

#Privacy setting
CREATE TABLE `{privacy}`
(
    `id`        INT(10) UNSIGNED     NOT NULL AUTO_INCREMENT,
    `field`     VARCHAR(64)          NOT NULL DEFAULT '',
    -- Default access level: 0 - everyone/public; 1 - member; 2 - follower; 4 - following; 255 - owner
    `value`     SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
    -- Is forced by admin
    `is_forced` TINYINT(1) UNSIGNED  NOT NULL DEFAULT '0',

    PRIMARY KEY (`id`),
    UNIQUE KEY `field` (`field`)
);

#Privacy setting for user profile field
CREATE TABLE `{privacy_user}`
(
    `id`    INT(10) UNSIGNED     NOT NULL AUTO_INCREMENT,
    `uid`   INT(10) UNSIGNED     NOT NULL,
    `field` VARCHAR(64)          NOT NULL DEFAULT '',
    -- Access level: 0 - everyone/public; 1 - member; 2 - follower; 4 - following; 255 - owner
    `value` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',

    PRIMARY KEY (`id`),
    UNIQUE KEY `user_field` (`uid`, `field`)
);

# User action log generated for user module
CREATE TABLE `{log}`
(
    `id`     INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `uid`    INT(10) UNSIGNED NOT NULL,
    `time`   INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `data`   VARCHAR(255)     NOT NULL DEFAULT '',
    `action` VARCHAR(64)      NOT NULL DEFAULT '',

    PRIMARY KEY (`id`),
    KEY (`uid`)
);

# Condition
CREATE TABLE `{condition}`
(
    `id`         INT(11)          NOT NULL AUTO_INCREMENT,
    `version`    VARCHAR(255)     NOT NULL,
    `filename`   VARCHAR(255)     NOT NULL,
    `created_at` INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `active_at`  INT(10) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
);