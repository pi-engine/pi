# Pi Engine schema
# http://pialog.org
# Author: Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
# --------------------------------------------------------

# ------------------------------------------------------
# User
# >>>>

# User action log generated for user module
CREATE TABLE `{log}` (
  `id`              int(10)             unsigned NOT NULL auto_increment,
  `uid`             int(10)             unsigned NOT NULL,
  `time`            int(10)             unsigned NOT NULL default '0',
  `data`            varchar(255)        NOT NULL default '',
  `action`          varchar(64)         NOT NULL default '',

  PRIMARY KEY (`id`),
  KEY (`uid`)
);
