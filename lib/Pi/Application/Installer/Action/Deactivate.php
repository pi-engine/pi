<?php
/**
 * Pi module deactivate action
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Installer\Action;

use Pi;


class Deactivate extends AbstractAction
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('deactivate.pre', array($this, 'checkDependent'));
        $events->attach('deactivate.post', array($this, 'removeDependency'));
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function process()
    {
        $model = Pi::model('module');
        $row = $model->select(array('name' => $this->module))->current();
        $row->active = 0;
        // save module entry into database
        if (!$row->save()) {
            $this->setResult('module', array(
                'status'    => false,
                'message'   => array('Module deactivate failed')
            ));
            return false;
        }

        $this->event->setParam('row', $row);
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function rollback()
    {
        $row = $this->event->getParam('row');
        $row->active = 1;
        return $row->save();
    }
}
