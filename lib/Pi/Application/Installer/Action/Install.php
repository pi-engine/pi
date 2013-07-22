<?php
/**
 * Pi module install action
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Installer\Action;

use Pi;

class Install extends AbstractAction
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        //$events->attach('install.pre', array($this, 'loadConfig'));
        $events->attach('install.pre', array($this, 'checkIndependent'));
        $events->attach('install.post', array($this, 'createDependency'));
        //$events->attach('install.post', array($this->installer, 'updateMeta'));
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function process()
    {
        $model = Pi::model('module');
        $moduleData = array(
            'name'          => $this->module,
            'directory'     => $this->directory,
            'title'         => $this->title ?: $this->config['meta']['title'],
            'version'       => $this->config['meta']['version'],
        );

        $row = $model->createRow($moduleData);
        // save module entry into database
        if (!$row->save()) {
            $this->setResult('module', array(
                'status'    => false,
                'message'   => array('Module insert failed')
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
        return $row->delete();
    }
}
