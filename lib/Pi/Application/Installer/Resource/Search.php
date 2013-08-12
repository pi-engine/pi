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
 * Module search setup configuration
 *
 * ```
 * array(
 *  'callback'  => array('class', 'method'),
 * );
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Search extends AbstractResource
{
    /**
     * {@inheritDoc}
     */
    public function installAction()
    {
        if (empty($this->config)) {
            return;
        }
        $module = $this->event->getParam('module');
        Pi::registry('search')->clear($module);

        $model = Pi::model('search');
        $data = $this->config;
        $directory = $this->event->getParam('directory');
        $data['callback'][0] = sprintf(
            'Module\\%s\\%s',
            ucfirst($directory),
            $data['callback'][0]
        );
        $data['module'] = $module;
        $row = $model->createRow($data);
        $row->save();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function updateAction()
    {
        $module = $this->event->getParam('module');
        Pi::registry('search')->clear($module);
        if ($this->skipUpgrade()) {
            return;
        }

        $model = Pi::model('search');
        $rowset = $model->select(array('module' => $module));
        $row = $rowset->current();
        if (empty($this->config)) {
            if ($row) {
                $row->delete();
            }
            return true;
        }
        $data = $this->config;
        $directory = $this->event->getParam('directory');
        $data['callback'][0] = sprintf(
            'Module\\%s\\%s',
            ucfirst($directory),
            $data['callback'][0]
        );
        $data['module'] = $module;
        if ($row) {
            $row->assign($data);
        } else {
            $row = $model->createRow($data);
        }
        $row->save();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function uninstallAction()
    {
        $module = $this->event->getParam('module');
        Pi::registry('search')->clear($module);

        $model = Pi::model('search');
        $model->delete(array('module' => $module));

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function activateAction()
    {
        $module = $this->event->getParam('module');
        $model = Pi::model('search');
        $model->update(array('active' => 1), array('module' => $module));
        Pi::registry('search')->flush();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function deactivateAction()
    {
        $module = $this->event->getParam('module');
        $model = Pi::model('search');
        $model->update(array('active' => 0), array('module' => $module));
        Pi::registry('search')->flush();

        return true;
    }
}
