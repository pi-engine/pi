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
 * Module deactivation
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Deactivate extends AbstractAction
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('deactivate.pre', [$this, 'checkDependent']);
        $events->attach('deactivate.post', [$this, 'removeDependency']);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function process()
    {
        $model       = Pi::model('module');
        $row         = $model->select(['name' => $this->module])->current();
        $row->active = 0;
        // save module entry into database
        try {
            $row->save();
        } catch (\Exception $e) {
            $this->setResult(
                'module',
                [
                    'status'  => false,
                    'message' => ['Module deactivate failed'],
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
        $row         = $this->event->getParam('row');
        $row->active = 1;

        return $row->save();
    }
}
