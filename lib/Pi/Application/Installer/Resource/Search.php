<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Installer\Resource;

use Pi;

/**
 * Module search setup configuration
 *
 * ```
 *  // Comprehensive mode
 *  return array(
 *      'class'  => <searchClass>,
 *  );
 *
 *  // Simple mode
 *  return <searchClass>;
 *
 *  // Simplest mode, Search class should be located in module Api folder
 *  return;
 *
 *  // Disable search
 *  return false;
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Search extends AbstractResource
{
    /**
     * Canonize config data
     *
     * @param string|array $config
     *
     * @return string
     */
    protected function canonize($config)
    {
        $class = '';
        if (false === $config) {
            return $class;
        }
        if ($config) {
            if (is_string($config)) {
                $class = $config;
            } elseif (!empty($config['class'])) {
                $class = $config['class'];
            }
        }
        $class = $class ?: 'search';
        $directory = $this->event->getParam('directory');
        $class = sprintf(
            'Module\\%s\Api\\%s',
            ucfirst($directory),
            ucfirst($class)
        );
        $abstract = 'Pi\Search\AbstractSearch';
        if (class_exists($class) && is_subclass_of($class, $abstract)) {
            $result = $class;
        } else {
            $result = '';
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function installAction()
    {
        $module = $this->event->getParam('module');
        Pi::registry('search')->flush();
        $class = $this->canonize($this->config);
        if (!$class) {
            return;
        }
        $data = array(
            'module'    => $module,
            'callback'  => $class,
        );
        $row = Pi::model('search')->createRow($data);
        $row->save();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function updateAction()
    {
        $module = $this->event->getParam('module');
        Pi::registry('search')->flush();
        if ($this->skipUpgrade()) {
            return;
        }

        $class = $this->canonize($this->config);
        if (!$class) {
            return;
        }
        $model = Pi::model('search');
        $rowset = $model->select(array('module' => $module));
        $row = $rowset->current();
        if ($row && !$class) {
            $row->delete();
            return;
        }
        $data = array(
            'module'    => $module,
            'callback'  => $class,
        );
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
        Pi::registry('search')->flush();

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
