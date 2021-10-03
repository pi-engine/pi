# Pi Engine schema
# http://piengine.org
# Author: Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
# --------------------------------------------------------

# ------------------------------------------------------
# Audit
# >>>>

# Auditing of application operations
CREATE TABLE `{core.audit}`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user`       INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `ip`         VARCHAR(15)      NOT NULL DEFAULT '',
    `section`    VARCHAR(64)      NOT NULL DEFAULT '',
    `module`     VARCHAR(64)      NOT NULL DEFAULT '',
    `controller` VARCHAR(64)      NOT NULL DEFAULT '',
    `action`     VARCHAR(64)      NOT NULL DEFAULT '',
    `method`     VARCHAR(64)      NOT NULL DEFAULT '',
    `message`    TEXT,
    `extra`      TEXT,
    `time`       INT(10) UNSIGNED NOT NULL DEFAULT '0',

    PRIMARY KEY (`id`)
);

# ------------------------------------------------------
# Block
# >>>>

# Blocks
CREATE TABLE `{core.block}`
(
    `id`            INT(8) UNSIGNED     NOT NULL AUTO_INCREMENT,
    `root`          INT(8) UNSIGNED     NOT NULL DEFAULT '0',  # ID for root block schema
    `name`          VARCHAR(64)                  DEFAULT NULL, # user key, empty or unique string, for calling from template
    `title`         VARCHAR(255)        NOT NULL DEFAULT '',
    `description`   VARCHAR(255)        NOT NULL DEFAULT '',   # Description
    `module`        VARCHAR(64)         NOT NULL DEFAULT '',   # module generating the block
    `template`      VARCHAR(64)         NOT NULL DEFAULT '',   # for generated
    `render`        VARCHAR(64)         NOT NULL DEFAULT '',   # for generated, render class::method

    `config`        TEXT,                                      # serialized configs (in JSON)

    `type`          VARCHAR(64)         NOT NULL DEFAULT '',   # Content type: "" - module generated; carousel - Carousel; tab - block compound; text - static text; html - static HTML; markdown - static Markdown syntax compliant
    `content`       TEXT,                                      # for custom

    `cache_ttl`     INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `cache_level`   VARCHAR(64)         NOT NULL DEFAULT '',   # for custom
    `title_hidden`  TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',  # Hide the title
    `body_fullsize` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',  # Display body in full-size mode, no padding

    `active`        TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',  # for generated, updated by system on module activation
    `cloned`        TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',  # is cloned

    # `link`            varchar(255)    NOT NULL default '',            # URL the title linked to
    `class`         VARCHAR(64)         NOT NULL DEFAULT '',   # specified stylesheet class for display
    `subline`       TEXT,                                      # block subline content, HTML is allowed

    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
);

# Block roots/defs for module blocks
CREATE TABLE `{core.block_root}`
(
    `id`          INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,

    `name`        VARCHAR(64)     NOT NULL DEFAULT '', # internal key
    `title`       VARCHAR(255)    NOT NULL DEFAULT '',
    `description` VARCHAR(255)    NOT NULL DEFAULT '', # Description
    `render`      VARCHAR(64)     NOT NULL DEFAULT '', # for generated, render class::method
    `module`      VARCHAR(64)     NOT NULL DEFAULT '', # module generating the block
    `template`    VARCHAR(64)     NOT NULL DEFAULT '', # for generated
    `config`      TEXT,                                # serialized options (in JSON) for edit
    `cache_level` VARCHAR(64)     NOT NULL DEFAULT '', # content cache level type
    `type`        VARCHAR(64)     NOT NULL DEFAULT '', # Content type: "" - module generated; carousel - Carousel; tab - block compound; text - static text; html - static HTML; markdown - static Markdown syntax compliant

    PRIMARY KEY (`id`),
    UNIQUE KEY `module_name` (`module`, `name`)
);

# ------------------------------------------------------
# Bootstrap
# >>>>

# Module bootstraps
CREATE TABLE `{core.bootstrap}`
(
    `id`       INT(10) UNSIGNED     NOT NULL AUTO_INCREMENT,
    `module`   VARCHAR(64)          NOT NULL DEFAULT '',
    `priority` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '1',
    `active`   TINYINT(1)           NOT NULL DEFAULT '1',

    PRIMARY KEY (`id`),
    UNIQUE KEY `module` (`module`)
);

# ------------------------------------------------------
# Config
# >>>>

# Configs for system and modules
CREATE TABLE `{core.config}`
(
    `id`          SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(64)          NOT NULL DEFAULT '',
    `module`      VARCHAR(64)          NOT NULL DEFAULT '', # Dirname of module
    `category`    VARCHAR(64)          NOT NULL DEFAULT '', # Category name of configs
    `title`       VARCHAR(255)         NOT NULL DEFAULT '',
    `value`       TEXT,
    `description` TEXT,
    `edit`        TEXT,                                     # callback options for edit
    `filter`      VARCHAR(64)          NOT NULL DEFAULT '',
    `order`       SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
    `visible`     TINYINT(1) UNSIGNED  NOT NULL DEFAULT '1',

    PRIMARY KEY (`id`),
    UNIQUE KEY `module_name` (`module`, `name`),
    KEY `module_category` (`module`, `category`)
);

# Config categories
CREATE TABLE `{core.config_category}`
(
    `id`          SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(64)          NOT NULL DEFAULT '',
    `module`      VARCHAR(64)          NOT NULL DEFAULT '',
    `title`       VARCHAR(64)          NOT NULL DEFAULT '',
    `description` VARCHAR(255)         NOT NULL DEFAULT '',
    `order`       SMALLINT(5) UNSIGNED NOT NULL DEFAULT '99',

    PRIMARY KEY (`id`),
    UNIQUE KEY `module_name` (`module`, `name`)
);

# ------------------------------------------------------
# Event
# >>>>

# Events
CREATE TABLE `{core.event}`
(
    `id`     INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`   VARCHAR(64)      NOT NULL DEFAULT '',
    `title`  VARCHAR(255)     NOT NULL DEFAULT '',
    `module` VARCHAR(64)      NOT NULL DEFAULT '',
    `active` TINYINT(1)       NOT NULL DEFAULT '1',

    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`module`, `name`)
);

# Event listeners
CREATE TABLE `{core.event_listener}`
(
    `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_name`   VARCHAR(64)      NOT NULL DEFAULT '',
    `event_module` VARCHAR(64)      NOT NULL DEFAULT '',
    `class`        VARCHAR(64)      NOT NULL DEFAULT '',
    `method`       VARCHAR(64)      NOT NULL DEFAULT '',
    `module`       VARCHAR(64)      NOT NULL DEFAULT '',
    `active`       TINYINT(1)       NOT NULL DEFAULT '1',

    PRIMARY KEY (`id`)
);

# ------------------------------------------------------
# Module
# >>>>

# Module meta
CREATE TABLE `{core.module}`
(
    # ID, auto created
    `id`        SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    # Module identifier, unique
    `name`      VARCHAR(64)          NOT NULL DEFAULT '',
    # File directory
    `directory` VARCHAR(64)          NOT NULL DEFAULT '',
    # Module title
    `title`     VARCHAR(64)          NOT NULL DEFAULT '',
    # Installed version, support for semantic version and build metadata, for instance: 1.2.3, 1.2.3+20140101
    `version`   VARCHAR(64)          NOT NULL DEFAULT '',
    # Last update time
    `update`    INT(10) UNSIGNED     NOT NULL DEFAULT '0',
    # Is active?
    `active`    TINYINT(1) UNSIGNED  NOT NULL DEFAULT '1',

    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
);

# Tables/views/triggers to be removed upon module uninstallation
CREATE TABLE `{core.module_schema}`
(
    `id`     INT(10) UNSIGNED                  NOT NULL AUTO_INCREMENT,
    `name`   VARCHAR(64)                       NOT NULL,
    `module` VARCHAR(64)                       NOT NULL,
    `type`   ENUM ('table', 'view', 'trigger') NOT NULL DEFAULT 'table',

    PRIMARY KEY (`id`)
);

# Module dependency
CREATE TABLE `{core.module_dependency}`
(
    `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `dependent`   VARCHAR(64)      NOT NULL,
    `independent` VARCHAR(64)      NOT NULL,

    PRIMARY KEY (`id`)
);

# ------------------------------------------------------
# Navigation
# >>>>

# Navigation meta
CREATE TABLE `{core.navigation}`
(
    `id`        INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`      VARCHAR(64)         NOT NULL DEFAULT '',
    `section`   VARCHAR(64)         NOT NULL DEFAULT '',
    `title`     VARCHAR(255)        NOT NULL DEFAULT '',
    `module`    VARCHAR(64)         NOT NULL DEFAULT '',
    `cache_ttl` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `active`    TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',

    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
);

# Navigation pages node
CREATE TABLE `{core.navigation_node}`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `navigation` VARCHAR(64)      NOT NULL DEFAULT '',
    `module`     VARCHAR(64)      NOT NULL DEFAULT '',
    `data`       MEDIUMTEXT,

    PRIMARY KEY (`id`),
    UNIQUE KEY `nav_name` (`navigation`)
);

# ------------------------------------------------------
# Page
# >>>>

# MVC page for block and cache
CREATE TABLE `{core.page}`
(
    `id`          INT(8) UNSIGNED         NOT NULL AUTO_INCREMENT,
    `title`       VARCHAR(64)             NOT NULL DEFAULT '',
    `section`     VARCHAR(64)             NOT NULL DEFAULT '',  # page resource: admin, front; other resource: block
    `module`      VARCHAR(64)             NOT NULL DEFAULT '',
    `controller`  VARCHAR(64)             NOT NULL DEFAULT '',
    `action`      VARCHAR(64)             NOT NULL DEFAULT '',
    `permission`  VARCHAR(64)             NOT NULL DEFAULT '',
    `cache_type`  ENUM ('page', 'action') NOT NULL,
    `cache_ttl`   INT(10)                 NOT NULL DEFAULT '0', # positive: for cache TTL; negative: for inheritance
    `cache_level` VARCHAR(64)             NOT NULL DEFAULT '',
    `block`       TINYINT(1) UNSIGNED     NOT NULL DEFAULT '0', # block inheritance: 1 - for self-setting; 0 - for inheriting form parent
    `custom`      TINYINT(1) UNSIGNED     NOT NULL DEFAULT '0',

    PRIMARY KEY (`id`),
    UNIQUE KEY `mca` (`section`, `module`, `controller`, `action`)
);

# Page-block links
CREATE TABLE `{core.page_block}`
(
    `id`    INT(8) UNSIGNED      NOT NULL AUTO_INCREMENT,
    `page`  INT(8) UNSIGNED      NOT NULL DEFAULT '0',
    `block` INT(8) UNSIGNED      NOT NULL DEFAULT '0',
    `zone`  SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0', #potential value: 0 - head, 99 - foot, 1 - left, 2 - topcenter, 3 - topleft, 4 - topright, 5 - bottomcenter, 6 - bottomleft, 7 - bottomright, 8 - right
    `order` INT(8)               NOT NULL DEFAULT '5', # positive: display order; negative: id of global page-block link that will be disabled on a specific page

    PRIMARY KEY (`id`),
    UNIQUE KEY `page_block` (`page`, `block`)
);

# ------------------------------------------------------
# Route
# >>>>

# Route definitions
CREATE TABLE `{core.route}`
(
    `id`       INT(8) UNSIGNED     NOT NULL AUTO_INCREMENT,
    `priority` SMALLINT(5)         NOT NULL DEFAULT '0',
    `section`  VARCHAR(64)         NOT NULL DEFAULT '',
    `name`     VARCHAR(64)         NOT NULL DEFAULT '',
    `module`   VARCHAR(64)         NOT NULL DEFAULT '',
    `data`     TEXT,
    `active`   TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
    # `custom`          tinyint(1)      unsigned NOT NULL default '0',

    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
);

# ------------------------------------------------------
# Search
# >>>>

# Module search callbacks
CREATE TABLE `{core.search}`
(
    `id`       SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `module`   VARCHAR(64)          NOT NULL DEFAULT '',
    `callback` VARCHAR(64)          NOT NULL DEFAULT '',
    `active`   TINYINT(1) UNSIGNED  NOT NULL DEFAULT '1',

    PRIMARY KEY (`id`),
    UNIQUE KEY `module` (`module`)
);

# ------------------------------------------------------
# Session
# >>>>

# System session
CREATE TABLE `{core.session}`
(
    `id`       VARCHAR(32)      NOT NULL DEFAULT '',
    `modified` INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `lifetime` INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `uid`      INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `data`     TEXT,

    PRIMARY KEY (`id`),
    KEY `modified` (`modified`)
);

# ------------------------------------------------------
# Taxonomy
# >>>>

# Taxonomy domain
CREATE TABLE `{core.taxonomy_domain}`
(
    `id`          SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(64)          NOT NULL DEFAULT '',
    `title`       VARCHAR(255)         NOT NULL DEFAULT '',
    `description` VARCHAR(255)         NOT NULL DEFAULT '',
    `module`      VARCHAR(64)          NOT NULL DEFAULT '',

    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
);

# Taxonomy taxon
CREATE TABLE `{core.taxonomy_taxon}`
(
    `id`          SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(64)          NOT NULL DEFAULT '',
    `title`       VARCHAR(255)         NOT NULL DEFAULT '',
    `description` VARCHAR(255)         NOT NULL DEFAULT '',
    # `domain`          varchar(64)     NOT NULL    default '',

    `left`        INT(10) UNSIGNED     NOT NULL DEFAULT '0',
    `right`       INT(10) UNSIGNED     NOT NULL DEFAULT '0',
    `depth`       SMALLINT(3) UNSIGNED NOT NULL DEFAULT '0',

    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`),
    UNIQUE KEY `left` (`left`),
    UNIQUE KEY `right` (`right`)
);

# ------------------------------------------------------
# Theme
# >>>>

# Theme meta
CREATE TABLE `{core.theme}`
(
    `id`      SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`    VARCHAR(64)          NOT NULL DEFAULT '',
    `version` VARCHAR(64)          NOT NULL DEFAULT '',
    `type`    VARCHAR(32)          NOT NULL DEFAULT 'both', # Type of theme: both - both front and admin; front - front; admin - admin
    `update`  INT(10) UNSIGNED     NOT NULL DEFAULT '0',
    # `title`           varchar(64)     NOT NULL default '',
    # `author`          varchar(255)    NOT NULL default '',
    # `active`          tinyint(1)      unsigned NOT NULL default '1',
    # `parent`          varchar(64)     NOT NULL default '',
    # `order`           smallint(5)     unsigned NOT NULL default '0',
    # `screenshot`      varchar(255)    NOT NULL default '',
    # `license`         varchar(255)    NOT NULL default '',

    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
);

# ------------------------------------------------------
# User and permission
# >>>>


# user ID: unique in the system, referenced as `uid`
# user identity: unique identity, generated by system or set by third-party
# all local data of a user should be indexed by user ID

# User account and authentication data
CREATE TABLE `{core.user_account}`
(
    `id`                INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    -- Account identity or username
    `identity`          VARCHAR(32)                        DEFAULT NULL,
    -- Credential/password hash
    `credential`        VARCHAR(255)        NOT NULL       DEFAULT '',
    -- Salt for credential hash
    `salt`              VARCHAR(255)        NOT NULL       DEFAULT '',
    -- User email
    `email`             VARCHAR(64)                        DEFAULT NULL,
    -- Display name
    `name`              VARCHAR(255)                       DEFAULT NULL,
    -- Avatar image src
    `avatar`            VARCHAR(255)        NOT NULL       DEFAULT '',
    -- Gender
    `gender`            ENUM ('male', 'female', 'unknown') DEFAULT 'unknown',
    -- Birth date with format 'YYYY-mm-dd'
    `birthdate`         VARCHAR(10)         NOT NULL       DEFAULT '',
    -- Synchronized availability of account
    -- 1: time_activated > 0 && time_disabled == 0 && time_deleted == 0
    -- 0: time_activated == 0 || time_disabled > 0 || time_deleted > 0
    `active`            TINYINT(1) UNSIGNED NOT NULL       DEFAULT '0',
    -- Two factor authentication status
    `two_factor`        TINYINT(1) UNSIGNED NOT NULL       DEFAULT '0',
    -- Avatar image src
    `two_factor_secret` VARCHAR(255)        NOT NULL       DEFAULT '',
    -- Time for account registration
    `time_created`      INT(10) UNSIGNED    NOT NULL       DEFAULT '0',
    -- Time for account activation
    `time_activated`    INT(10) UNSIGNED    NOT NULL       DEFAULT '0',
    -- Time for account disabling
    `time_disabled`     INT(10) UNSIGNED    NOT NULL       DEFAULT '0',
    -- Time for account deletion, can not be reset
    `time_deleted`      INT(10) UNSIGNED    NOT NULL       DEFAULT '0',

    PRIMARY KEY (`id`),
    UNIQUE KEY `identity` (`identity`),
    UNIQUE KEY `email` (`email`),
    KEY `name` (`name`),

    KEY `status` (`active`)
);

# user custom contents
CREATE TABLE `{core.user_data}`
(
    `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `uid`         INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `module`      VARCHAR(64)      NOT NULL DEFAULT '',
    `name`        VARCHAR(64)      NOT NULL,
    `time`        INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `expire`      INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `value`       TEXT                      DEFAULT NULL,
    `value_int`   INT(10)                   DEFAULT NULL,
    `value_multi` TEXT                      DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `user_data_name` (`uid`, `module`, `name`)
);

# Role
CREATE TABLE `{core.role}`
(
    `id`          INT(10) UNSIGNED        NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(64)             NOT NULL,
    `title`       VARCHAR(255)            NOT NULL,
    `description` TEXT,
    `module`      VARCHAR(64)             NOT NULL DEFAULT '',
    `custom`      TINYINT(1) UNSIGNED     NOT NULL DEFAULT '0',
    `active`      TINYINT(1) UNSIGNED     NOT NULL DEFAULT '1',
    `section`     ENUM ('front', 'admin') NOT NULL,
    -- Display order
    #`order`           int(10)         unsigned NOT NULL default '0',

    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
);

# user-role links
CREATE TABLE `{core.user_role}`
(
    `id`      INT(10) UNSIGNED        NOT NULL AUTO_INCREMENT,
    `uid`     INT(10) UNSIGNED        NOT NULL,
    `role`    VARCHAR(64)             NOT NULL,
    `section` ENUM ('front', 'admin') NOT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `section_user` (`section`, `uid`, `role`)
);

# Permission resources
CREATE TABLE `{core.permission_resource}`
(
    `id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `section` VARCHAR(64)      NOT NULL DEFAULT '',
    `module`  VARCHAR(64)      NOT NULL DEFAULT '',
    -- Resource name: page - <module-controller>; specific - <module-resource>
    `name`    VARCHAR(64)      NOT NULL DEFAULT '',
    `title`   VARCHAR(255)     NOT NULL DEFAULT '',
    -- system - created on module installation; custom
    `type`    VARCHAR(64)      NOT NULL DEFAULT '',

    PRIMARY KEY (`id`),
    UNIQUE KEY `resource_name` (`section`, `module`, `name`, `type`)
);

# Permission rules
CREATE TABLE `{core.permission_rule}`
(
    `id`       INT(10) UNSIGNED        NOT NULL AUTO_INCREMENT,
    -- Resource name or id
    `resource` VARCHAR(64)             NOT NULL DEFAULT '',
    -- Resource item name or id, optional
    #`item`            varchar(64)     default NULL,
    `module`   VARCHAR(64)             NOT NULL DEFAULT '',
    `section`  ENUM ('front', 'admin') NOT NULL,
    `role`     VARCHAR(64)             NOT NULL,
    -- Permission value: 0 - allowed; 1 - denied
    #`deny`            tinyint(1)      unsigned NOT NULL default '0',

    PRIMARY KEY (`id`),
    #KEY `item` (`item`),
    #KEY `role` (`role`),
    UNIQUE KEY `section_module_perm` (`section`, `module`, `resource`, `role`)
);
