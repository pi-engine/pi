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
use Module\System\Controller\ComponentController;
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
class ConfigController extends ComponentController
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
            $module = $this->params('name', $this->moduleName('system'));
        }

        if (!$this->permission($module, 'config')) {
            return;
        }

        $updateLanguage = false;
        $updateEnv      = false;
        if ($module) {
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

                        // Prepare for language check
                        $currentLocale  = null;
                        $currentEnv     = null;
                        if ('system' == $module) {
                            $currentLocale = array(
                                'locale'    => Pi::config('locale'),
                                'charset'   => Pi::config('charset'),
                            );
                            $currentEnv = array(
                                'environment'   =>  Pi::config('environment')
                            );
                        }

                        $values = $form->getData();
                        foreach ($configs as $row) {
                            $row->value = $values[$row->name];
                            $row->save();

                            // Check for language update
                            if ($currentLocale
                                && isset($currentLocale[$row->name])
                                && $row->value != $currentLocale[$row->name]
                            ) {
                                $updateLanguage = true;
                            }
                            // Check for environment update
                            if ($currentEnv
                                && isset($currentEnv[$row->name])
                                && $row->value != $currentEnv[$row->name]
                            ) {
                                $currentEnv[$row->name] = $row->value;
                                $updateEnv = true;
                            }
                        }
                        Pi::registry('config')->clear($module);

                        if ($updateLanguage) {
                            Pi::service('cache')->flush();
                        }

                        if ($updateEnv) {
                            $data = Pi::config()->load('engine');
                            $data['config'] = array_replace_recursive(
                                $data['config'],
                                $currentEnv
                            );
                            Pi::config()->write('engine', $data, true);
                        }

                        $this->jump(
                            array('action' => 'index', 'name' => $module),
                            _a('Configuration data saved successfully.'),
                            'success'
                        );

                        return;
                    }
                }

                $this->view()->assign('form', $form);
            }
        }

        //$this->view()->assign('success', $messageSuccessful);

        $model = Pi::model('config');
        $select = $model->select()->group('module')
            ->columns(array('count' => new Expression('count(*)'), 'module'));
        $rowset = $model->selectWith($select);
        $configCounts = array();
        foreach ($rowset as $row) {
            $configCounts[$row->module] = $row->count;
        }

        $this->view()->assign('name', $module);

        $this->view()->assign('title', _a('Module configurations'));
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
