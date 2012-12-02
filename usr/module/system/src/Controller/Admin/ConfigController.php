<?php
/**
 * Action controller class
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Module\System
 * @subpackage      Controller
 * @version         $Id$
 */

namespace Module\System\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\System\Form\ConfigForm;

/**
 * Feature list:
 *  1. List of system configuration categories
 *  2. List of modules
 *  3. Edit form of a module with category
 *  4. Configuration submission
 */
class ConfigController extends ActionController
{
    /**
     * System config category list
     */
    public function indexAction()
    {
        Pi::service('i18n')->load('module/system:config');
        $messageSuccessful = '';

        if ($this->request->isPost()) {
            $category = $this->params()->fromPost('category');
        } else {
            $category = $this->params('category', 'general');
        }

        $modelCategory = Pi::model('config_category');
        $select = $modelCategory->select()->where(array('module' => 'system'))->order(array('order ASC', 'id ASC'));
        $rowset = $modelCategory->selectWith($select);
        $categories = array();
        foreach ($rowset as $row) {
            $categories[$row->name] = $row;
        }

        $model = Pi::model('config');
        $where = array('module' => 'system', 'category' => $category, 'visible' => 1);
        $select = $model->select()->where($where)->order(array('order ASC'));
        $rowset = $model->selectWith($select);
        $configs = array();
        foreach ($rowset as $row) {
            $configs[] = $row;
        }

        $form = $this->getForm($configs, 'system');
        $form->add(array(
            'name'          => 'category',
            'attributes'    => array(
                'type'  => 'hidden',
                'value' => $category,
            ),
        ));

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            if ($form->isValid()) {
                $values = $form->getData();
                foreach ($configs as $row) {
                    $row->value = $values[$row->name];
                    $row->save();
                }
                Pi::service('registry')->config->clear('system');
                $messageSuccessful = __('Configuration data saved successfully.');
            }
        }

        $this->view()->assign('form', $form);
        $this->view()->assign('success', $messageSuccessful);
        $this->view()->assign('category', $category);
        $this->view()->assign('categories', $categories);

        $title = sprintf(__('System configuration: %s'), $categories[$category]->title);
        $this->view()->assign('title', $title);
        $this->view()->setTemplate('config-system');
    }

    /**
     * Module configuration edit
     */
    public function moduleAction()
    {
        $messageSuccessful = '';

        if ($this->request->isPost()) {
            $module = $this->params()->fromPost('name');
        } else {
            $module = $this->params('name', '');
        }

        if ($module) {
            $category = $this->params('category', '');
            Pi::service('i18n')->load('module/' . $module . ':config');

            $model = Pi::model('config');
            $select = $model->select()->where(array('module' => $module, 'visible' => 1))->order(array('order ASC'));
            $rowset = $model->selectWith($select);
            $configs = array();
            foreach ($rowset as $row) {
                $configs[] = $row;
            }
            if ($configs) {
                $groups = array();
                $configsByCategory = array();
                $categories = Pi::model('config_category')->select(array('module' => $module));
                if ($categories->count() > 1) {
                    foreach ($categories as $category) {
                        $groups[$category->name] = array(
                            'label'     => $category->title,
                            'elements'  => array(),
                        );
                    }
                    foreach ($configs as $config) {
                        $groups[$config->category]['elements'][] = $config->name;
                    }
                }
                $this->view()->assign('configs', $configs);

                $form = $this->getForm($configs, $module);
                $form->setGroups($groups);
                //$form->setAttribute('action', $this->url('admin', array('action' => 'save')));
                $form->add(array(
                    'name'          => 'name',
                    'attributes'    => array(
                        'type'  => 'hidden',
                        'value' => $module,
                    ),
                ));

                if ($this->request->isPost()) {
                    $post = $this->request->getPost();
                    $form->setData($post);
                    if ($form->isValid()) {
                        $values = $form->getData();
                        foreach ($configs as $row) {
                            $row->value = $values[$row->name];
                            $row->save();
                        }
                        Pi::service('registry')->config->clear($module);
                        $messageSuccessful = __('Configuration data saved successfully.');
                    }
                }

                $this->view()->assign('form', $form);
            }
        }

        $this->view()->assign('success', $messageSuccessful);

        $model = Pi::model('config');
        $select = $model->select()->group('module')->columns(array('count' => new \Zend\Db\Sql\Expression('count(*)'), 'module'));
        $rowset = $model->selectWith($select);
        $configCounts = array();
        foreach ($rowset as $row) {
            $configCounts[$row->module] = $row->count;
        }

        $modules = Pi::service('registry')->modulelist->read(array('active' => 1));
        unset($modules['system']);
        foreach (array_keys($modules) as $key) {
            if (empty($configCounts[$key])) {
                unset($modules[$key]);
            }
        }
        $this->view()->assign('modules', $modules);
        $this->view()->assign('name', $module);

        $title = sprintf(__('Module configuration: %s'), $modules[$module]['title']);
        $this->view()->assign('title', $title);
        $this->view()->setTemplate('config-module');
    }

    protected function getForm($configs, $module)
    {
        $form = new ConfigForm($configs, $module);
        return $form;
    }
}
