# Pi Engine schema
# http://pialog.org
# Author: Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
# --------------------------------------------------------

# ------------------------------------------------------
# Audit
# >>>>

# Auditting of application operations
CREATE TABLE `{core.audit}` (
  `id`              int(10)         unsigned NOT NULL auto_increment,
  `user`            int(10)         unsigned NOT NULL    default '0',
  `ip`              varchar(15)     NOT NULL    default '',
  `section`         varchar(64)     NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',
  `controller`      varchar(64)     NOT NULL    default '',
  `action`          varchar(64)     NOT NULL    default '',
  `method`          varchar(64)     NOT NULL    default '',
  `message`         text,
  `extra`           text,
  `time`            int(10)         unsigned NOT NULL   default '0',

  PRIMARY KEY  (`id`)
);

# ------------------------------------------------------
# Block
# >>>>

# Blocks
CREATE TABLE `{core.block}` (
  `id`              int(8)          unsigned NOT NULL auto_increment,
  `root`            int(8)          unsigned NOT NULL default '0',  # ID for root block schema
  `name`            varchar(64)     default NULL,                   # user key, empty or unique string, for calling from template
  `title`           varchar(255)    NOT NULL default '',
  `description`     varchar(255)    NOT NULL default '',            # Description
  `module`          varchar(64)     NOT NULL default '',            # module generating the block
  `template`        varchar(64)     NOT NULL default '',            # for generated
  `render`          varchar(64)     NOT NULL default '',            # for generated, render class::method

  `config`          text,                                           # serialized configs (in JSON)

  `type`            varchar(64)     NOT NULL default '',            # Content type: "" - module generated; carousel - Carousel; tab - block compound; text - static text; html - static HTML; markdown - static Markdown syntax compliant
  `content`         text,                                           # for custom

  `cache_ttl`       int(10)         unsigned NOT NULL default '0',
  `cache_level`     varchar(64)     NOT NULL default '',            # for custom
  `title_hidden`    tinyint(1)      unsigned NOT NULL default '0',  # Hide the title
  `body_fullsize`   tinyint(1)      unsigned NOT NULL default '0',  # Display body in full-size mode, no padding

  `active`          tinyint(1)      unsigned NOT NULL default '1',  # for generated, updated by system on module activation
  `cloned`          tinyint(1)      unsigned NOT NULL default '0',  # is cloned

# `link`            varchar(255)    NOT NULL default '',            # URL the title linked to
  `class`           varchar(64)     NOT NULL default '',            # specified stylesheet class for display
  `subline`         text,                                           # block subline content, HTML is allowed

  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
);

# Block roots/defs for module blocks
CREATE TABLE `{core.block_root}` (
  `id`              int(8)          unsigned NOT NULL auto_increment,

  `name`            varchar(64)     NOT NULL default '',            # internal key
  `title`           varchar(255)    NOT NULL default '',
  `description`     varchar(255)    NOT NULL default '',            # Description
  `render`          varchar(64)     NOT NULL default '',            # for generated, render class::method
  `module`          varchar(64)     NOT NULL default '',            # module generating the block
  `template`        varchar(64)     NOT NULL default '',            # for generated
  `config`          text,                                           # serialized options (in JSON) for edit
  `cache_level`     varchar(64)     NOT NULL default '',            # content cache level type
  `type`            varchar(64)     NOT NULL default '',            # Content type: "" - module generated; carousel - Carousel; tab - block compound; text - static text; html - static HTML; markdown - static Markdown syntax compliant

  PRIMARY KEY  (`id`),
  UNIQUE KEY `module_name` (`module`, `name`)
);

# ------------------------------------------------------
# Bootstrap
# >>>>

# Module bootstraps
CREATE TABLE `{core.bootstrap}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `module`          varchar(64)     NOT NULL default '',
  `priority`        smallint(5)     unsigned NOT NULL default '1',
  `active`          tinyint(1)      NOT NULL default '1',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `module` (`module`)
);

# ------------------------------------------------------
# Config
# >>>>

# Configs for system and modules
CREATE TABLE `{core.config}` (
  `id`              smallint(5)     unsigned NOT NULL auto_increment,
  `name`            varchar(64)     NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',             # Dirname of module
  `category`        varchar(64)     NOT NULL    default '',             # Category name of configs
  `title`           varchar(255)    NOT NULL default '',
  `value`           text,
  `description`     varchar(255)    NOT NULL default '',
  `edit`            text,           # callback options for edit
  `filter`          varchar(64)     NOT NULL default '',
  `order`           smallint(5)     unsigned NOT NULL default '0',
  `visible`         tinyint(1)      unsigned NOT NULL default '1',

  PRIMARY KEY   (`id`),
  UNIQUE KEY    `module_name`   (`module`, `name`),
  KEY `module_category`  (`module`, `category`)
);

# Config categories
CREATE TABLE `{core.config_category}` (
  `id`              smallint(5)     unsigned NOT NULL auto_increment,
  `name`            varchar(64)     NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',
  `title`           varchar(64)     NOT NULL    default '',
  `description`     varchar(255)    NOT NULL    default '',
  `order`           smallint(5)     unsigned NOT NULL default '99',

  PRIMARY KEY  (`id`),
  UNIQUE KEY        `module_name`   (`module`, `name`)
);

# ------------------------------------------------------
# Event
# >>>>

# Events
CREATE TABLE `{core.event}` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `name`            varchar(64)     NOT NULL    default '',
  `title`           varchar(255)    NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',
  `active`          tinyint(1)      NOT NULL    default '1',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`module`, `name`)
);

# Event listeners
CREATE TABLE `{core.event_listener}` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `event_name`      varchar(64)     NOT NULL    default '',
  `event_module`    varchar(64)     NOT NULL    default '',
  `class`           varchar(64)     NOT NULL    default '',
  `method`          varchar(64)     NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',
  `active`          tinyint(1)      NOT NULL    default '1',

  PRIMARY KEY  (`id`)
);

# ------------------------------------------------------
# Module
# >>>>

# Module meta
CREATE TABLE `{core.module}` (
  # ID, auto created
  `id`          smallint(5)         unsigned NOT NULL auto_increment,
  # Module identifier, unique
  `name`        varchar(64)         NOT NULL default '',
  # File directory
  `directory`   varchar(64)         NOT NULL default '',
  # Module title
  `title`       varchar(64)         NOT NULL default '',
  # Installed version, support for semantic version and build metadata, for instance: 1.2.3, 1.2.3+20140101
  `version`     varchar(64)         NOT NULL default '',
  # Last update time
  `update`      int(10)             unsigned NOT NULL default '0',
  # Is active?
  `active`      tinyint(1)          unsigned NOT NULL default '1',

  PRIMARY KEY   (`id`),
  UNIQUE KEY    `name` (`name`)
);

# Tables/views/triggers to be removed upon module uninstallation
CREATE TABLE `{core.module_schema}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `name`            varchar(64)     NOT NULL,
  `module`          varchar(64)     NOT NULL,
  `type`            enum('table', 'view', 'trigger')   NOT NULL default 'table',

  PRIMARY KEY  (`id`)
);

# Module dependency
CREATE TABLE `{core.module_dependency}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `dependent`       varchar(64)     NOT NULL,
  `independent`     varchar(64)     NOT NULL,

  PRIMARY KEY  (`id`)
);

# ------------------------------------------------------
# Navigation
# >>>>

# Navigation meta
CREATE TABLE `{core.navigation}` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `name`            varchar(64)     NOT NULL    default '',
  `section`         varchar(64)     NOT NULL    default '',
  `title`           varchar(255)    NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',
  `cache_ttl`       int(10)         unsigned    NOT NULL default '0',
  `active`          tinyint(1)      unsigned    NOT NULL default '1',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
);

# Navigation pages node
CREATE TABLE `{core.navigation_node}` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `navigation`      varchar(64)     NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',
  `data`            text,

  PRIMARY KEY  (`id`),
  UNIQUE KEY `nav_name` (`navigation`)
);

# ------------------------------------------------------
# Page
# >>>>

# MVC page for block and cache
CREATE TABLE `{core.page}` (
  `id`              int(8)    unsigned    NOT NULL auto_increment,
  `title`           varchar(64)     NOT NULL    default '',
  `section`         varchar(64)     NOT NULL    default '', # page resource: admin, front; other resource: block
  `module`          varchar(64)     NOT NULL    default '',
  `controller`      varchar(64)     NOT NULL    default '',
  `action`          varchar(64)     NOT NULL    default '',
  `permission`      varchar(64)     NOT NULL    default '',
  `cache_type`      enum('page', 'action')      NOT NULL,
  `cache_ttl`       int(10)         NOT NULL    default '0',            # positive: for cache TTL; negative: for inheritance
  `cache_level`     varchar(64)     NOT NULL    default '',
  `block`           tinyint(1)      unsigned    NOT NULL default '0',   # block inheritance: 1 - for self-setting; 0 - for inheriting form parent
  `custom`          tinyint(1)      unsigned    NOT NULL default '0',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `mca` (`section`, `module`, `controller`, `action`)
);

# Page-block links
CREATE TABLE `{core.page_block}` (
  `id`              int(8)      unsigned    NOT NULL auto_increment,
  `page`            int(8)      unsigned    NOT NULL    default '0',
  `block`           int(8)      unsigned    NOT NULL    default '0',
  `zone`            smallint(5) unsigned    NOT NULL    default '0', #potential value: 0 - head, 99 - foot, 1 - left, 2 - topcenter, 3 - topleft, 4 - topright, 5 - bottomcenter, 6 - bottomleft, 7 - bottomright, 8 - right
  `order`           int(8)      NOT NULL    default '5',    # positive: display order; negative: id of global page-block link that will be disabled on a specific page

  PRIMARY KEY  (`id`),
  UNIQUE KEY `page_block` (`page`, `block`)
);

# ------------------------------------------------------
# Route
# >>>>

# Route definitions
CREATE TABLE `{core.route}` (
  `id`              int(8)          unsigned    NOT NULL auto_increment,
  `priority`        smallint(5)     NOT NULL    default '0',
  `section`         varchar(64)     NOT NULL    default '',
  `name`            varchar(64)     NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',
  `data`            text,
  `active`          tinyint(1)      unsigned NOT NULL default '1',
  `custom`          tinyint(1)      unsigned NOT NULL default '0',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
);

# ------------------------------------------------------
# Search
# >>>>

# Module search callbacks
CREATE TABLE `{core.search}` (
  `id`              smallint(5)     unsigned NOT NULL auto_increment,
  `module`          varchar(64)     NOT NULL default '',
  `callback`        varchar(64)     NOT NULL default '',
  `active`          tinyint(1)      unsigned NOT NULL default '1',

  PRIMARY KEY       (`id`),
  UNIQUE KEY        `module` (`module`)
);

# ------------------------------------------------------
# Session
# >>>>

# System session
CREATE TABLE `{core.session}` (
  `id`          varchar(32) NOT NULL default '',
  `modified`    int(10) unsigned NOT NULL default '0',
  `lifetime`    int(10) unsigned NOT NULL default '0',
  `uid`         int(10) unsigned    NOT NULL default '0',
  `data`        text,

  PRIMARY KEY  (`id`),
  KEY `modified` (`modified`)
);

# ------------------------------------------------------
# Taxonomy
# >>>>

# Taxonomy domain
CREATE TABLE `{core.taxonomy_domain}` (
  `id`              smallint(5)     unsigned NOT NULL auto_increment,
  `name`            varchar(64)     NOT NULL    default '',
  `title`           varchar(255)    NOT NULL    default '',
  `description`     varchar(255)    NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',

  PRIMARY KEY       (`id`),
  UNIQUE KEY        `name` (`name`)
);

# Taxonomy taxon
CREATE TABLE `{core.taxonomy_taxon}` (
  `id`              smallint(5)     unsigned NOT NULL auto_increment,
  `name`            varchar(64)     NOT NULL    default '',
  `title`           varchar(255)    NOT NULL    default '',
  `description`     varchar(255)    NOT NULL    default '',
# `domain`          varchar(64)     NOT NULL    default '',

  `left`            int(10)         unsigned    NOT NULL default '0',
  `right`           int(10)         unsigned    NOT NULL default '0',
  `depth`           smallint(3)     unsigned    NOT NULL default '0',

  PRIMARY KEY       (`id`),
  UNIQUE KEY        `name` (`name`),
  UNIQUE KEY        `left` (`left`),
  UNIQUE KEY        `right` (`right`)
);

# ------------------------------------------------------
# Theme
# >>>>

# Theme meta
CREATE TABLE `{core.theme}` (
  `id`              smallint(5)     unsigned NOT NULL auto_increment,
  `name`            varchar(64)     NOT NULL default '',
  `version`         varchar(64)     NOT NULL default '',
  `type`            varchar(32)     NOT NULL default 'both',   # Type of theme: both - both front and admin; front - front; admin - admin
  `update`          int(10)         unsigned NOT NULL default '0',
# `title`           varchar(64)     NOT NULL default '',
# `author`          varchar(255)    NOT NULL default '',
# `active`          tinyint(1)      unsigned NOT NULL default '1',
# `parent`          varchar(64)     NOT NULL default '',
# `order`           smallint(5)     unsigned NOT NULL default '0',
# `screenshot`      varchar(255)    NOT NULL default '',
# `license`         varchar(255)    NOT NULL default '',

  PRIMARY KEY       (`id`),
  UNIQUE KEY        `name` (`name`)
);

# ------------------------------------------------------
# User and permission
# >>>>


# user ID: unique in the system, referenced as `uid`
# user identity: unique identity, generated by system or set by third-party
# all local data of a user should be indexed by user ID

# User account and authentication data
CREATE TABLE `{core.user_account}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  -- Account identity or username
  `identity`        varchar(32)     default NULL,
  -- Credential/password hash
  `credential`      varchar(255)    NOT NULL default '',
  -- Salt for credential hash
  `salt`            varchar(255)    NOT NULL default '',
  -- User email
  `email`           varchar(64)     default NULL,

  -- Display name
  `name`            varchar(255)    default NULL,
  -- Avatar image src
  `avatar`          varchar(255)    NOT NULL default '',
  -- Gender
  `gender`          enum('male', 'female', 'unknown') default 'unknown',
  -- Birth date with format 'YYYY-mm-dd'
  `birthdate`       varchar(10)     NOT NULL default '',

  -- Synchronized availability of account
  -- 1: time_activated > 0 && time_disabled == 0 && time_deleted == 0
  -- 0: time_activated == 0 || time_disabled > 0 || time_deleted > 0
  `active`          tinyint(1)      unsigned NOT NULL default '0',

  -- Time for account registration
  `time_created`    int(10)         unsigned NOT NULL default '0',
  -- Time for account activation
  `time_activated`  int(10)         unsigned NOT NULL default '0',
  -- Time for account disabling
  `time_disabled`   int(10)         unsigned NOT NULL default '0',
  -- Time for account deletion, can not be reset
  `time_deleted`    int(10)         unsigned NOT NULL default '0',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `identity` (`identity`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `name` (`name`),

  KEY `status` (`active`)
);

# user custom contents
CREATE TABLE `{core.user_data}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `uid`             int(10)         unsigned    NOT NULL default '0',
  `module`          varchar(64)     NOT NULL    default '',
  `name`            varchar(64)     NOT NULL,
  `time`            int(10)         unsigned    NOT NULL default '0',
  `expire`          int(10)         unsigned    NOT NULL default '0',
  `value`           text            default NULL,
  `value_int`       int(10)         default NULL,
  `value_multi`     text            default NULL,

  PRIMARY KEY  (`id`),
  UNIQUE KEY `user_data_name` (`uid`, `module`, `name`)
);

# Role
CREATE TABLE `{core.role}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `name`            varchar(64)     NOT NULL,
  `title`           varchar(255)    NOT NULL,
  `description`     text,
  `module`          varchar(64)     NOT NULL    default '',
  `custom`          tinyint(1)      unsigned    NOT NULL default '0',
  `active`          tinyint(1)      unsigned    NOT NULL default '1',
  `section`         enum('front', 'admin')      NOT NULL,
  -- Display order
  #`order`           int(10)         unsigned NOT NULL default '0',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
);

# user-role links
CREATE TABLE `{core.user_role}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `uid`             int(10)         unsigned    NOT NULL,
  `role`            varchar(64)     NOT NULL,
  `section`         enum('front', 'admin')      NOT NULL,

  PRIMARY KEY  (`id`),
  UNIQUE KEY `section_user` (`section`, `uid`, `role`)
);

# Permission resources
CREATE TABLE `{core.permission_resource}` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `section`         varchar(64)     NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',
  -- Resource name: page - <module-controller>; specific - <module-resource>
  `name`            varchar(64)     NOT NULL    default '',
  `title`           varchar(255)    NOT NULL    default '',
  -- system - created on module installation; custom
  `type`            varchar(64)     NOT NULL    default '',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `resource_name` (`section`, `module`, `name`, `type`)
);

# Permission rules
CREATE TABLE `{core.permission_rule}` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  -- Resource name or id
  `resource`        varchar(64)     NOT NULL    default '',
  -- Resource item name or id, optional
  #`item`            varchar(64)     default NULL,
  `module`          varchar(64)     NOT NULL    default '',
  `section`         enum('front', 'admin')      NOT NULL,
  `role`            varchar(64)     NOT NULL,
  -- Permission value: 0 - allowed; 1 - denied
  #`deny`            tinyint(1)      unsigned NOT NULL default '0',

  PRIMARY KEY  (`id`),
  #KEY `item` (`item`),
  #KEY `role` (`role`),
  UNIQUE KEY `section_module_perm` (`section`, `module`, `resource`, `role`)
);
