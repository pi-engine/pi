# Pi Engine schema
# http://pialog.org
# Author: Zongshu Lin <lin40553024@163.com>
# --------------------------------------------------------

# ------------------------------------------------------
# Article custom compound
# >>>>

# Entity for article custom compound: related articles
CREATE TABLE `{related}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `article`         int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `related`         int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `order`           tinyint(3) UNSIGNED   NOT NULL DEFAULT 0,

  PRIMARY KEY          (`id`),
  KEY `article`        (`article`)
);
