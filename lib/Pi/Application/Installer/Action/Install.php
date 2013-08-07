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
 * Module installation
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Install extends AbstractAction
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('install.pre', array($this, 'checkIndependent'));
        $events->attach('install.post', array($this, 'createDependency'));

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
