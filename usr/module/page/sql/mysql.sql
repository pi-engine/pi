# Quote all table names with '{' and '}', and prefix all system tables with 'core.'

CREATE TABLE `{page}` (
  `id`              int(10) unsigned        NOT NULL auto_increment,
  `title`           varchar(255)            NOT NULL default '',
  `name`            varchar(64)             default NULL,
  `user`            int(10)                 unsigned    NOT NULL default '0',
  `time_created`    int(10)                 unsigned    NOT NULL default '0',
  `time_updated`    int(10)                 unsigned    NOT NULL default '0',
  `active`          tinyint(1)              NOT NULL default '1',
  -- Page content, or template name for phtml type
  `content`         text,
  -- Markup type: text, html, markdown, phtml
  `markup`          varchar(64)             NOT NULL default 'text',

  -- SEO slug for URL
  `slug`            varchar(64)             default NULL,
  `clicks`          int(10)                 unsigned    NOT NULL default '0',
  `seo_title`       varchar(255)            NOT NULL default '',
  `seo_keywords`    varchar(255)            NOT NULL default '',
  `seo_description` varchar(255)            NOT NULL default '',

  -- Order in navigation, '0' for not navigated
  `nav_order`       smallint(5)             unsigned    NOT NULL default '0',

  -- For rendering
  `theme`           varchar(64)             NOT NULL default '',
  `layout`          varchar(64)             NOT NULL default '',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`)
);


# NOT used yet. Solely for demonstration, will be dropped
CREATE TABLE `{stats}` (
  `id`      int(10) unsigned        NOT NULL auto_increment,
  `page`    int(10)                 unsigned    NOT NULL default '0',
  `clicks`  int(10)                 unsigned    NOT NULL default '0',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `page` (`page`)
);