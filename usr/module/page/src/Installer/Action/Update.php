<?php
/**
 * Pi module installer action
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Module\Page
 * @subpackage      Installer
 * @version         $Id$
 */

namespace Module\Page\Installer\Action;
use Pi;
use Pi\Application\Installer\Action\Update as BasicUpdate;
use Zend\EventManager\Event;
use Pi\Application\Installer\SqlSchema;

class Update extends BasicUpdate
{
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('update.pre', array($this, 'updateSchema'));
        parent::attachDefaultListeners();
        return $this;
    }

    public function updateSchema(Event $e)
    {
        $moduleVersion = $e->getParam('version');
        if (version_compare($moduleVersion, '1.0.0-beta.2', '>=')) {
            return true;
        }

        // Add table of stats, not used yet; Solely for demonstration, will be dropped off by end of the udpate
        $sql =<<<'EOD'
CREATE TABLE `{stats}` (
  `id`      int(10) unsigned        NOT NULL auto_increment,
  `page`    int(10)                 unsigned    NOT NULL default '0',
  `clicks`  int(10)                 unsigned    NOT NULL default '0',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `page` (`page`)
);
EOD;
        SqlSchema::setType($this->module);
        $sqlHandler = new SqlSchema;
        try {
            $sqlHandler->queryContent($sql);
        } catch (\Exception $exception) {
            $this->setResult('db', array(
                'status'    => false,
                'message'   => 'SQL schema query failed: ' . $exception->getMessage(),
            ));
            return false;
        }

        // Table modify
        $model = Pi::model('page', $this->module);
        $table = $model->getTable();
        $adapter = $model->getAdapter();

        // Alter table field `time` to `time_created`
        $sql = sprintf('ALTER TABLE %s CHANGE `time` `time_created` int(10) unsigned NOT NULL default \'0\'', $table);
        try {
            $adapter->query($sql, 'execute');
        } catch (\Exception $exception) {
            $this->setResult('db', array(
                'status'    => false,
                'message'   => 'Table alter query failed: ' . $exception->getMessage(),
            ));
            return false;
        }

        // Add table field `time_updated`
        $sql = sprintf('ALTER TABLE %s ADD `time_updated` int(10) unsigned NOT NULL default \'0\' AFTER `time_created`', $table);
        try {
            $adapter->query($sql, 'execute');
        } catch (\Exception $exception) {
            $this->setResult('db', array(
                'status'    => false,
                'message'   => 'Table alter query failed: ' . $exception->getMessage(),
            ));
            return false;
        }
        // Add table field `clicks`
        try {
            $sql = sprintf('ALTER TABLE %s ADD `clicks` int(10) unsigned NOT NULL default \'0\'', $table);
            $adapter->query($sql, 'execute');
        } catch (\Exception $exception) {
            $this->setResult('db', array(
                'status'    => false,
                'message'   => 'Table alter query failed: ' . $exception->getMessage(),
            ));
            return false;
        }

        // Drop not used table
        try {
            $sql = sprintf('DROP TABLE IF EXISTS %s', Pi::model('stats', $this->module)->getTable());
            $adapter->query($sql, 'execute');
        } catch (\Exception $exception) {
            $this->setResult('db', array(
                'status'    => false,
                'message'   => 'Table drop failed: ' . $exception->getMessage(),
            ));
            return false;
        }

    }
}
