# $Id$
# --------------------------------------------------------

# ------------------------------------------------------
# ACL
# >>>>

# extended edge table: edge ID, entry edge ID, direct edge, exit edge, start vertex, end vertex
# DAG (Directed Acyclic Graph) algorithm
# NOT USED yet
# see: http://www.codeproject.com/KB/database/Modeling_DAGs_on_SQL_DBs.aspx#Table5
CREATE TABLE `{core.acl_edge}` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `start`           varchar(64)     NOT NULL    default '',
  `end`             varchar(64)     NOT NULL    default '',
  `entry`           int(10)         unsigned    NOT NULL default '0',
  `direct`          int(10)         unsigned    NOT NULL default '0',
  `exit`            int(10)         unsigned    NOT NULL default '0',
  `hops`            int(10)         unsigned    NOT NULL default '0',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `pair` (`start`, `end`)
);

# role inheritance or edge: edge ID, child ID, parent ID
# TODO: could use vertext model with start vertex & end vertex
CREATE TABLE `{core.acl_inherit}` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `child`           varchar(64)     NOT NULL    default '',
  `parent`          varchar(64)     NOT NULL    default '',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `pair` (`child`, `parent`)
);

# ACL privileges
CREATE TABLE `{core.acl_privilege}` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `resource`        int(10)         unsigned    NOT NULL default '0', # resource ID
  `name`            varchar(64)     NOT NULL    default '', # Privilege name
  `title`           varchar(255)    NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',

  PRIMARY KEY (`id`),
  UNIQUE KEY `pair` (`resource`, `name`)
);

# ACL resources
CREATE TABLE `{core.acl_resource}` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `left`            int(10)         unsigned    NOT NULL default '0',
  `right`           int(10)         unsigned    NOT NULL default '0',
  `depth`           smallint(3)     unsigned    NOT NULL default '0',
  `section`         varchar(64)     NOT NULL    default '', # page resource: admin, front; other resource: module, block
  `name`            varchar(64)     NOT NULL    default '', # pattern: generated - module[:controller]; or custom - module-resource
# `item`            varchar(64)     NOT NULL    default '',
  `title`           varchar(255)    NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',
  `type`            varchar(64)     NOT NULL    default '', # potential values: system - created by module installation; page - created by page creation; custom - created manually

  PRIMARY KEY  (`id`),
  UNIQUE KEY `left` (`left`),
  UNIQUE KEY `right` (`right`),
  UNIQUE KEY `pair` (`section`, `module`, `name`)
);

# ACL roles
# See http://en.wikipedia.org/wiki/Role-based_access_control
CREATE TABLE `{core.acl_role}` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `name`            varchar(64)     NOT NULL    default '',                 # Unique name
  `title`           varchar(255)    NOT NULL    default '',                 # Title
  `description`     text,
  `active`          tinyint(1)      unsigned    NOT NULL default '1',       # Active for usage
  `custom`          tinyint(1)      unsigned    NOT NULL default '0',       # Added manually?
  `module`          varchar(64)     NOT NULL    default '',                 # Applicable wide

  # Added in Pi
  `section`         varchar(64)     NOT NULL    default 'front', # admin, front

  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
);

# ACL rules
CREATE TABLE `{core.acl_rule}` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `section`         varchar(64)     NOT NULL    default '',
  `role`            varchar(64)     NOT NULL    default '',
  `resource`        varchar(64)     NOT NULL    default '',
  `privilege`       varchar(64)     NOT NULL    default '',
  `deny`            tinyint(1)      unsigned    NOT NULL default '0',   # 0 for allowed; 1 for denied
  `module`          varchar(64)     NOT NULL    default '',

  PRIMARY KEY  (`id`),
  KEY `pair` (`resource`, `privilege`),
  KEY `section_module` (`section`, `module`)
);

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
  `edit`            tinytext        default NULL,           # callback options for edit
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
  # Installed version
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

  PRIMARY KEY  (`id`)
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
  `cache_ttl`       int(10)         NOT NULL    default '0',            # positive: for cache TTL; negative: for inheritance
  `cache_level`     varchar(64)     NOT NULL    default '',
  `block`           tinyint(1)      unsigned    NOT NULL default '0',   # block inheritance: 1 - for self-setting; 0 - for inheriting form parent
  `custom`          tinyint(1)      unsigned    NOT NULL default '0',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `mca` (`section`, `module`, `controller`, `action`)
);

# Page-block links
CREATE TABLE `{core.page_block}` (
  `id`              int(8)    unsigned    NOT NULL auto_increment,
  `page`            int(8)    unsigned    NOT NULL    default '0',
  `block`           int(8)    unsigned    NOT NULL    default '0',
  `zone`            smallint(5)     unsigned    NOT NULL    default '0', #potential value: 0 - left, 1 - right, 2 - topleft, 3 - topcenter, 4 - topright, 5 - bottomleft, 6 - bottomcenter, 7 - bottomright
  `order`           int(8)    NOT NULL    default '5',    # positive: display orer; negative: id of global page-block link that will be disabled on a specific page

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
# User
# >>>>

# user ID: the unique identity in the system
# user identity: the user's unique identity, generated by the system or sent from other systems like openID
# all local data of a user should be indexed by user ID

# User accout and authentication data
CREATE TABLE `{core.user_account}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `identity`        varchar(32)     NOT NULL,
  `credential`      varchar(255)    NOT NULL default '',    # Credential hash
  `salt`            varchar(255)    NOT NULL default '',    # Hash salt
  `email`           varchar(64)     NOT NULL,
  `name`            varchar(255)    NOT NULL default '',
  `active`          tinyint(1)      NOT NULL default '0',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `identity` (`identity`),
  UNIQUE KEY `email` (`email`)
# KEY `authenticate` (`identity`, `credential`)
);

# Entity meta for custom profile
CREATE TABLE `{core.user_meta}` (
  `id`              smallint(5)     unsigned    NOT NULL    auto_increment,
  `key`             varchar(64)     NOT NULL,
  `category`        varchar(64)     NOT NULL default '',
  `title`           varchar(255)    NOT NULL default '',
  `attribute`       varchar(255)    default NULL,           # profile column attribute
  `view`            varchar(255)    default NULL,           # callback function for view
  `edit`            tinytext        default NULL,           # callback options for edit
  `admin`           tinytext        default NULL,           # callback options for administration
  `search`          tinytext        default NULL,           # callback options for search
  `options`         tinytext        default NULL,           # value options
  `module`          varchar(64)     NOT NULL default '',
  `active`          tinyint(1)      NOT NULL default '0',
  `required`        tinyint(1)      NOT NULL default '0',

  PRIMARY KEY  (`id`),
  UNIQUE KEY  `key` (`key`)
);

# Aggregated entities for custom profile
CREATE TABLE `{core.user_profile}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `user`            int(10)         unsigned    NOT NULL,

  PRIMARY KEY  (`id`)
);

# user custom contents
CREATE TABLE `{core.user_repo}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `user`            int(10)         unsigned    NOT NULL default '0',
  `module`          varchar(64)     NOT NULL    default '',
  `type`            varchar(64)     NOT NULL    default '',
  `content`         text,

  PRIMARY KEY  (`id`),
  UNIQUE KEY `user_content` (`user`, `module`, `type`)
);

# user-role links for regular
CREATE TABLE `{core.user_role}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `user`            int(10)         unsigned    NOT NULL,
  `role`            varchar(64)     NOT NULL    default '',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `user` (`user`)
);


# user-role links for staff
CREATE TABLE `{core.user_staff}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `user`            int(10)         unsigned    NOT NULL,
  `role`            varchar(64)     NOT NULL    default '',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `user` (`user`)
);
# ------------------------------------------------------