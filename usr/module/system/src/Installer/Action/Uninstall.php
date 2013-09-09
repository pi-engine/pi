<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Uninstall as BasicUninstall;
use Zend\EventManager\Event;

/**
 * Uninstall handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Uninstall extends BasicUninstall
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('uninstall.pre', array($this, 'checkModules'), 1000);
        $events->attach('uninstall.post', array($this, 'dropTables'), -1000);
        parent::attachDefaultListeners();

        return $this;
    }

    /**
     * Check module model availability
     *
     * @param Event $e
     * @return bool
     */
    public function checkModules(Event $e)
    {
        $module = $this->event->getParam('module');
        $model = Pi::mdel('module');
        $rowset = $model->select(array('dirname <> ?' => $module));
        if ($rowset->count() > 0) {
            $result = array(
                'status'    => false,
                'message'   => 'Modules are not unistalled completely.'
            );
            $e->setParam('result', $result);

            return false;
        }

        return true;
    }

    /**
     * Drop module tables
     *
     * @param Event $e
     * @return void
     */
    public function dropTables(Event $e)
    {
        $module = $this->event->getParam('module');
        $modelTable = Pi::model('module_schema');
        $rowset = $modelTable->select(array('module' => $module));
        foreach ($rowset as $row) {
            Pi::db()->adapter()
                ->query(
                    'DROP TABLE IF EXISTS ' . Pi::db()->prefix($row->name, ''),
                    'execute'
                );
        }
        return;
    }
}
