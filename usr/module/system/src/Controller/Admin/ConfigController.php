<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Controller\Admin;

use Pi;
use Module\System\Controller\ComponentController  as ActionController;
use Module\System\Form\ConfigForm;
use Zend\Db\Sql\Expression;

/**
 * Configuration controller
 *
 * Feature list:
 *
 *  1. List of system configuration categories
 *  2. List of modules
 *  3. Edit form of a module with category
 *  4. Configuration submission
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ConfigController extends ActionController
{
    /**
     * Module configuration edit
     *
     * @return void
     */
    public function indexAction()
    {
        $messageSuccessful = '';

        if ($this->request->isPost()) {
            $module = $this->params()->fromPost('name');
        } else {
            $module = $this->params('name', 'system');
        }

        if ($module) {
            $category = $this->params('category', '');
            Pi::service('i18n')->load('module/' . $module . ':config');

            $model = Pi::model('config');
            $select = $model->select()
                ->where(array('module' => $module, 'visible' => 1))
                ->order(array('order ASC'));
            $rowset = $model->selectWith($select);
            $configs = array();
            foreach ($rowset as $row) {
                $configs[] = $row;
            }
            if ($configs) {
                $groups = array();
                //$configsByCategory = array();
                $select = Pi::model('config_category')->select()
                    ->where(array('module' => $module))
                    ->order(array('order ASC'));
                $categories = Pi::model('config_category')
                    ->selectWith($select);
                if ($categories->count() > 1) {
                    foreach ($categories as $category) {
                        $groups[$category->name] = array(
                            'label'     => $category->title,
                            'elements'  => array(),
                        );
                    }
                    foreach ($configs as $config) {
                        $groups[$config->category]['elements'][] =
                            $config->name;
                    }
                }
                $this->view()->assign('configs', $configs);

                $form = $this->getForm($configs, $module);
                $form->setGroups($groups);
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
                        Pi::registry('config')->clear($module);
                        $messageSuccessful =
                            __('Configuration data saved successfully.');
                    }
                }

                $this->view()->assign('form', $form);
            }
        }

        $this->view()->assign('success', $messageSuccessful);

        $model = Pi::model('config');
        $select = $model->select()->group('module')
            ->columns(array('count' => new Expression('count(*)'), 'module'));
        $rowset = $model->selectWith($select);
        $configCounts = array();
        foreach ($rowset as $row) {
            $configCounts[$row->module] = $row->count;
        }

        $this->view()->assign('name', $module);

        $this->view()->assign('title', __('Module configurations'));
        //$this->view()->setTemplate('config-module');
    }

    /**
     * Get config edit form
     *
     * @param array $configs
     * @param string $module
     * @return ConfigForm
     */
    protected function getForm($configs, $module)
    {
        $form = new ConfigForm($configs, $module);

        return $form;
    }
}
