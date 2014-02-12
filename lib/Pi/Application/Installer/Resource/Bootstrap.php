<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Installer\Resource;

use Pi;

/**
 * Module bootstrap setup
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Bootstrap extends AbstractResource
{
    /**
     * Canonize bootstrap data
     *
     * @param array $data
     * @return array
     */
    protected function canonize($data)
    {
        $config = array(
            'module'    => $this->event->getParam('module'),
            'priority'  => 1,
            'active'    => 1,
        );
        if (is_scalar($data)) {
            $config['priority'] = intval($data);
        } elseif (is_array($data) && isset($data['priority'])) {
            $config['priority'] = intval($data['priority']);
        }

        return $config;
    }

    /**
     * {@inheritDoc}
     */
    public function installAction()
    {
        if (empty($this->config)) {
            return;
        }
        $module = $this->event->getParam('module');
        Pi::registry('bootstrap')->clear($module);

        $model = Pi::model('bootstrap');
        $data = $this->canonize($this->config);
        $model->insert($data);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function updateAction()
    {
        $module = $this->event->getParam('module');
        Pi::registry('bootstrap')->clear($module);
        if ($this->skipUpgrade()) {
            return;
        }

        $model = Pi::model('bootstrap');
        $row = $model->select(array('module' => $module))->current();
        if (empty($this->config)) {
            if ($row) {
                $row->delete();
            }
            return true;
        }
        $data = $this->canonize($this->config);
        if ($row) {
            $status = $model->update($data, array('id' => $row->id));
        } else {
            $status = $model->insert($data);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function uninstallAction()
    {
        $module = $this->event->getParam('module');
        Pi::registry('bootstrap')->clear($module);

        $model = Pi::model('bootstrap');
        $model->delete(array('module' => $module));

        return true;
    }
}
