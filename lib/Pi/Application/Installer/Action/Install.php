<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
        $events->attach('install.pre', [$this, 'checkIndependent']);
        $events->attach('install.post', [$this, 'createDependency']);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function process()
    {
        $model      = Pi::model('module');
        $moduleData = [
            'name'      => $this->module,
            'directory' => $this->directory,
            'title'     => $this->title ?: $this->config['meta']['title'],
            'version'   => $this->config['meta']['version'],
        ];

        $row = $model->createRow($moduleData);
        // save module entry into database
        if (!$row->save()) {
            $this->setResult(
                'module',
                [
                    'status'  => false,
                    'message' => ['Module insert failed'],
                ]
            );
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
