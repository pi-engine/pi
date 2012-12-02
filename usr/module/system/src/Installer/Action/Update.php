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
 * @package         Module\System
 * @subpackage      Installer
 * @version         $Id$
 */

namespace   Module\System\Installer\Action;
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
        $events->attach('update.post', array($this, 'updateConfig'));
        parent::attachDefaultListeners();
        return $this;
    }

    public function updateConfig(Event $e)
    {
        $model = Pi::model('update', $this->module);
        $data = array(
            'title'     => __('System updated'),
            'content'   => __('The system is updated successfully.'),
            'uri'       => Pi::url('www', true),
            'time'      => time(),
        );
        $model->insert($data);
    }

    public function updateSchema(Event $e)
    {
        $moduleVersion = $e->getParam('version');
        if (version_compare($moduleVersion, '3.0.0-beta.3', '>=')) {
            return true;
        }

        // Add table of stats, not used yet; Solely for demonstration, will be dropped off by end of the udpate
        $sql =<<<'EOD'
CREATE TABLE `{core.navigation_node}` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `navigation`      varchar(64)     NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',
  `data`            text,

  PRIMARY KEY  (`id`)
);
EOD;
        $sqlHandler = new SqlSchema;
        try {
            $sqlHandler->queryContent($sql);
        } catch (\Exception $exception) {
            $result = $e->getParam('result');
            $result['db'] = array(
                'status'    => false,
                'message'   => 'SQL schema query failed: ' . $exception->getMessage(),
            );
            $e->setParam('result', $result);
            return false;
        }

        // Drop not used table
        $tables = array(
            Pi::model('monitor')->getTable(),
            Pi::model('navigation_page')->getTable(),
        );
        $adapter = Pi::db()->getAdapter();
        foreach ($tables as $table) {
            try {
                $sql = sprintf('DROP TABLE IF EXISTS %s', $table);
                $adapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $result = $e->getParam('result');
                $result['db'] = array(
                    'status'    => false,
                    'message'   => 'Table drop failed: ' . $exception->getMessage(),
                );
                $e->setParam('result', $result);
                return false;
            }
        }
    }
}
