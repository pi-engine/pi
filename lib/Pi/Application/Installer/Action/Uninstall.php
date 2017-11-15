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
 * Module uninstallation
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Uninstall extends AbstractAction
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('uninstall.pre', array($this, 'checkDependent'));
        $events->attach('uninstall.post', array($this, 'removeDependency'));

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
