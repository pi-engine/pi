<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Page\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Update as BasicUpdate;
use Pi\Application\Installer\SqlSchema;
use Zend\EventManager\Event;

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

        // Add table of stats, not used yet;
        // Solely for demonstration, will be dropped off by end of the udpate
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
                'message'   => 'SQL schema query failed: '
                               . $exception->getMessage(),
            ));

            return false;
        }

        // Table modify
        $model = Pi::model('page', $this->module);
        $table = $model->getTable();
        $adapter = $model->getAdapter();

        // Alter table field `time` to `time_created`
        $sql = sprintf('ALTER TABLE %s CHANGE `time` `time_created` int(10)'
                       . ' unsigned NOT NULL default \'0\'',
                       $table);
        try {
            $adapter->query($sql, 'execute');
        } catch (\Exception $exception) {
            $this->setResult('db', array(
                'status'    => false,
                'message'   => 'Table alter query failed: '
                               . $exception->getMessage(),
            ));

            return false;
        }

        // Add table field `time_updated`
        $sql = sprintf('ALTER TABLE %s ADD `time_updated` int(10) unsigned'
                       . ' NOT NULL default \'0\' AFTER `time_created`',
                       $table);
        try {
            $adapter->query($sql, 'execute');
        } catch (\Exception $exception) {
            $this->setResult('db', array(
                'status'    => false,
                'message'   => 'Table alter query failed: '
                               . $exception->getMessage(),
            ));

            return false;
        }
        // Add table field `clicks`
        try {
            $sql = sprintf('ALTER TABLE %s ADD `clicks` int(10)'
                           . ' unsigned NOT NULL default \'0\'',
                           $table);
            $adapter->query($sql, 'execute');
        } catch (\Exception $exception) {
            $this->setResult('db', array(
                'status'    => false,
                'message'   => 'Table alter query failed: '
                               . $exception->getMessage(),
            ));

            return false;
        }

        // Drop not used table
        try {
            $sql = sprintf('DROP TABLE IF EXISTS %s',
                           Pi::model('stats', $this->module)->getTable());
            $adapter->query($sql, 'execute');
        } catch (\Exception $exception) {
            $this->setResult('db', array(
                'status'    => false,
                'message'   => 'Table drop failed: '
                               . $exception->getMessage(),
            ));

            return false;
        }

    }
}
