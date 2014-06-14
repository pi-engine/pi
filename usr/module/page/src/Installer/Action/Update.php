<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Page\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Update as BasicUpdate;
use Pi\Application\Installer\SqlSchema;
use Zend\EventManager\Event;

class Update extends BasicUpdate
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('update.pre', array($this, 'updateSchema'));
        parent::attachDefaultListeners();
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function updateSchema(Event $e)
    {
        $moduleVersion  = $e->getParam('version');
        $model          = Pi::model('page', $this->module);
        $table          = $model->getTable();
        $adapter        = $model->getAdapter();

        // Add `theme` `layout` fields
        if (version_compare($moduleVersion, '1.2.1', '<=')) {
            $sql =<<<'EOD'
ALTER TABLE %s
ADD  `theme`           varchar(64)             NOT NULL default '',
ADD  `layout`          varchar(64)             NOT NULL default '';
EOD;

            $sql = sprintf($sql, $table);
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
        }

        // Drop homepage for blocks
        if (version_compare($moduleVersion, '1.2.0', '<=')) {
            Pi::model('page')->delete(array(
                'section'       => 'front',
                'module'        => $this->module,
                'controller'    => 'index',
                'action'        => 'index',
            ));
        }

        // Check for version 1.0.0-beta.2
        if (version_compare($moduleVersion, '1.0.0-beta.2', '<')) {

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

        // Check for version 1.0.1
        if (version_compare($moduleVersion, '1.0.1', '<')) {

            // Alter table add field `seo_title`
            $sql = sprintf('ALTER TABLE %s ADD `seo_title` varchar(255) NOT NULL',
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

            // Alter table add field `seo_keywords`
            $sql = sprintf('ALTER TABLE %s ADD `seo_keywords` varchar(255) NOT NULL',
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

            // Alter table add field `seo_description`
            $sql = sprintf('ALTER TABLE %s ADD `seo_description` varchar(255) NOT NULL',
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
        }

        // Check for version 1.2.0
        if (version_compare($moduleVersion, '1.2.0', '<')) {

            // Alter table add field `nav_order`
            $sql = sprintf('ALTER TABLE %s ADD `nav_order` smallint(5) unsigned NOT NULL default \'0\'',
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
        }

        return true;
    }
}
