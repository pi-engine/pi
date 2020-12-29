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
            $events->attach('update.post', [$this, 'removeDependency']);
            $events->attach('update.post', [$this, 'createDependency']);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function process()
    {
        $model = Pi::model('module');
        $row   = $model->select(
            [
                'name' => $this->event->getParam('module'),
            ]
        )->current();

        $config        = $this->event->getParam('config');
        $configVersion = $config['meta']['version'];
        if (version_compare($row->version, $configVersion, '>=')) {
            $row->update = time();
            $row->save();
            return true;
        } else {
            $this->event->setParam('upgrade', true);
        }

        $originalRow   = clone $row;
        $config        = $this->event->getParam('config');
        $meta          = ['update' => time()];
        $moduleColumns = ['directory', 'version'];
        foreach ($config['meta'] as $key => $value) {
            if (in_array($key, $moduleColumns)) {
                $meta[$key] = $value;
            }
        }
        //$meta['active'] = 1;
        $row->assign($meta);

        // save module entry into database
        try {
            $row->save();
        } catch (\Exception $e) {
            $this->setResult(
                'module',
                [
                    'status'  => false,
                    'message' => ['Module upgrade failed'],
                ]
            );
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
