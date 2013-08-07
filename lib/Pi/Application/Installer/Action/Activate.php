<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Installer\Action;

use Pi;

/**
 * Module activation
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Activate extends AbstractAction
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('activate.pre', array($this, 'checkIndependent'));
        $events->attach('activate.post', array($this, 'createDependency'));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function process()
    {
        $model = Pi::model('module');
        $row = $model->select(array('name' => $this->module))->current();
        $row->active = 1;
        // save module entry into database
        if (!$row->save()) {
            $this->setResult('module', array(
                'status'    => false,
                'message'   => array('Module activate failed')
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
        $row->active = 0;
        
        return $row->save();
    }
}
