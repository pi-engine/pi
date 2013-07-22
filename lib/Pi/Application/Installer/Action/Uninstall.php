<?php
/**
 * Pi module uninstall action
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Installer\Action;

use Pi;


class Uninstall extends AbstractAction
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        //$events->attach('install.pre', array($this, 'loadConfig'));
        $events->attach('uninstall.pre', array($this, 'checkDependent'));
        $events->attach('uninstall.post', array($this, 'removeDependency'));
        //$events->attach('install.post', array($this->installer, 'updateMeta'));
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function process()
    {
        $result = $this->event->getParam('result');
        $model = Pi::model('module');
        $row = $model->select(array('name' => $this->module))->current();
        // save module entry into database
        if ($row) {
            $row->delete();
        }
        /*
        if (!$status) {
            $result['module'] = array(
                'status'    => false,
                'message'   => array('Module is failed to delete.')
            );
            $this->event->setParam('result', $result);
            return false;
        }
        */

        return true;
    }

}
