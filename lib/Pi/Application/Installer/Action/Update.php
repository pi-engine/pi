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
 * Module updater
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Update extends AbstractAction
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        if ($this->event->getParam('upgrade')) {
            $events->attach('update.post', array($this, 'removeDependency'));
            $events->attach('update.post', array($this, 'createDependency'));
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function process()
    {
        $model = Pi::model('module');
        $row = $model->select(array(
            'name' => $this->event->getParam('module')
        ))->current();

        $config = $this->event->getParam('config');
        $configVersion = $config['meta']['version'];
        if (version_compare($row->version, $configVersion, '>=')) {
            $row->update = time();
            $row->save();
            return true;
        } else {
            $this->event->setParam('upgrade', true);
        }

        $originalRow = clone $row;
        $config = $this->event->getParam('config');
        $meta = array('update' => time());
        $moduleColumns = array('id', 'name', 'title', 'directory',
                               'version', 'update', 'active');
        foreach ($config['meta'] as $key => $value) {
            if (in_array($key, $moduleColumns)) {
                $meta[$key] = $value;
            }
        }
        //$meta['active'] = 1;
        $row->assign($meta);

        // save module entry into database
        if (!$row->save()) {
            $this->setResult('module', array(
                'status'    => false,
                'message'   => array('Module upgrade failed')
            ));
            return false;
        }

        $this->event->setParam('row', $originalRow);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function rollback()
    {
        $row = $this->event->getParam('row');
        if ($row) {
            $row->save();
        }
        
        return;
    }
}
