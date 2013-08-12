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
 * Config setup
 *
 * - With category and configs
 *
 * <code>
 *  array(
 *      'category'  => array(
 *          array(
 *              'name'  => 'category_name',
 *              'title' => 'Category Title'
 *              'order' => 1,
 *          ),
 *          array(
 *              'name'  => 'category_b',
 *              'title' => 'Category B Title'
 *              'order' => 2,
 *          ),
 *          ...
 *      ),
 *      'item'     => array(
 *          // Config of input textbox
 *          'config_name_a' => array(
 *              'title'         => 'Config title A',
 *              'category'      => 'cate',
 *              'description'   => '',
 *              'value'         => 'a config',
 *              'edit'          => 'input'
 *              'filter'        => 'text',
 *          ),
 *          // 'edit' default as 'input'
 *          'config_name_ab' => array(
 *              'title'         => 'Config title AB',
 *              'category'      => 'cate',
 *              'description'   => '',
 *              'value'         => 'a config',
 *              'filter'        => 'text',
 *          ),
 *          // Config with select edit type
 *          'config_name_b' => array(
 *              'title'         => 'Config title B',
 *              'description'   => '',
 *              'value'         => 'option_a',
 *              'edit'          => array(
 *                  'attributes'    => array(
 *                      'type'      => 'select'
 *                      'options'   => array(
 *                          'option_a'  => 'Option A',
 *                          'option_b'  => 'Option B',
 *                      ),
 *                  ),
 *              ),
 *              'filter'        => 'filtertype',
 *          ),
 *          // Config with custom edit element
 *          'config_name_c' => array(
 *              'title'         => 'Config title C',
 *              'category'      => 'general',
 *              'description'   => '',
 *              'value'         => '',
 *              'edit'          => array(
 *                  'type'          => 'Module\Demo\Form\Element\ConfigTest',
 *                  'attributes'    => array(
 *                      'att'   => 'attValue',
 *                  ),
 *              ),
 *              'filter'        => 'filtertype',
 *          ),
 *          // Config not show on edit pages
 *          'config_name_d' => array(
 *              'title'         => 'Config title D',
 *              'category'      => 'general',
 *              'description'   => '',
 *              'value'         => 'a config',
 *              'filter'        => 'text',
 *              'visible'       => 0, // Not show on edit page
 *          ),
 *          // Orphan configs
 *          'config_name_e' => array(
 *              'title'         => 'Config title E',
 *              'category'      => '', // Not managed by any category
 *              'description'   => '',
 *              'value'         => 'a config',
 *              'edit'          => 'SpecifiedEditElement',
 *              'filter'        => 'text',
 *          ),
 *
 *          ...
 *      )
 *  );
 * </code>
 *
 * - Only with configs
 *
 * <code>
 *  array(
 *          'config_name'   => array(
 *              'title'         => 'Config title',
 *              'category'      => '',
 *              'description'   => '',
 *              'value'         => '',
 *          ),
 *          ...
 *  );
 * </code>
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Config extends AbstractResource
{
    /**
     * Default category name
     * @var string
     */
    const DEFAULT_CATEGORY = 'general';

    /**
     * Canonize config category and item list data
     *
     * @param array $config
     * @return array
     */
    protected function canonize($config)
    {
        $ret = array();
        // Canonize categories and items
        if (!isset($config['item']) && !isset($config['category'])) {
            $ret = array(
                'category'  => array(),
                'item'      => $config
            );
        } else {
            $ret = array(
                'category'  => isset($config['category'])
                                ? $config['category'] : array(),
                'item'      => isset($config['item'])
                                ? $config['item'] : array()
            );
        }

        // Formulate config name and order
        $order = 1;
        foreach ($ret['item'] as $key => &$item) {
            if (!isset($item['name'])) {
                $item['name'] = strval($key);
            }
            $item['order'] = $order++;
        }
        // Formulate category order
        $order = 1;
        foreach ($ret['category'] as $key => &$item) {
            $item['order'] = $order++;
        }

        return $ret;
    }

    /**
     * Canonize a config
     *
     * @param array $config
     * @return array
     */
    protected function canonizeConfig($config)
    {
        $module = $this->event->getParam('module');
        $config['module'] = $module;
        if (!isset($config['category'])) {
            $config['category'] = static::DEFAULT_CATEGORY;
        }
        if (!empty($config['edit']) && is_string($config['edit'])) {
            $config['edit'] = array(
                'type'  => $config['edit'],
            );
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
        Pi::registry('config')->clear($module);

        $config = $this->canonize($this->config);
        if (!empty($config['category'])) {
            $modelCategory = Pi::model('config_category');
            //$order = 0;
            foreach ($config['category'] as $category) {
                $category['module'] = $module;
                /*
                if (!isset($category['order'])) {
                    $category['order'] = ++$order;
                }
                */
                $status = $modelCategory->insert($category);
                if (!$status) {
                    return array(
                        'status'    => false,
                        'message'   => sprintf(
                            'Category "%s" is not created.',
                             $category['name']
                        )
                    );
                }
            };
        }

        $model = Pi::model('config');
        //$order = 0;
        foreach ($config['item'] as $item) {
            /*
            if (!isset($item['order'])) {
                $item['order'] = ++$order;
            }
            */
            $item = $this->canonizeConfig($item);
            $row = $model->createRow($item);
            $status = $row->save();
            if (!$status) {
                return array(
                    'status'    => false,
                    'message'   => sprintf(
                        'Config "%s" is not created.',
                        $item['name']
                    ),
                );
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function updateAction()
    {
        $module = $this->event->getParam('module');
        Pi::registry('config')->clear($module);

        if ($this->skipUpgrade()) {
            return;
        }

        $config = $this->canonize($this->config);
        $modelCategory = Pi::model('config_category');
        $modelConfig = Pi::model('config');
        $categories = array();
        foreach ($config['category'] as $category) {
            $categories[$category['name']] = $category;
        }

        $rowsetCategory = $modelCategory->select(array('module' => $module));
        foreach ($rowsetCategory as $row) {
            $key = $row->name;
            // Delete unused category
            if (!isset($categories[$key])) {
                $row->delete();
                $status = true;
                if (!$status) {
                    return array(
                        'status'    => false,
                        'message'   => sprintf(
                            'Category "%s" is not deleted.',
                            $row->name
                        )
                    );
                }
            } else {
                // Get existent category id
                $categories[$key]['id'] = $row->id;

                $isChanged = false;
                if ($categories[$key]['name'] != $row->name) {
                    $row->name = $categories[$key]['name'];
                    $isChanged = true;
                }
                if ($categories[$key]['order'] != $row->order) {
                    $row->order = $categories[$key]['order'];
                    $isChanged = true;
                }
                // Update existent category
                if (!empty($isChanged)) {
                    $status = $row->save();
                    if (!$status) {
                        $msg = 'Category "%s" is not updated.';
                        return array(
                            'status'    => false,
                            'message'   => sprintf($msg, $row->name),
                        );
                    }
                }
            }
        }
        foreach ($categories as $key => $category) {
            // Skip existent category
            if (isset($category['id'])) continue;
            // Insert new category
            $category['module'] = $module;
            $status = $modelCategory->insert($category);
            if (!$status) {
                return array(
                    'status'    => false,
                    'message'   => sprintf(
                        'Category "%s" is not created.',
                        $category['name']
                    )
                );
            }
        }

        $configList = array();
        foreach ($config['item'] as $item) {
            $item = $this->canonizeConfig($item);
            $configList[$item['name']] = $item;
        }
        $rowsetConfig = $modelConfig->select(array('module' => $module));
        foreach ($rowsetConfig as $row) {
            // Update existent config
            if (isset($configList[$row->name])) {
                // Skip value to avoid overwriting
                if (isset($configList[$row->name]['value'])) {
                    unset($configList[$row->name]['value']);
                }
                $row->assign($configList[$row->name]);
                $row->save();
                unset($configList[$row->name]);
                continue;
            }
            // Delete deprecated config
            $row->delete();
            $status = true;
            if (!$status) {
                return array(
                    'status'    => false,
                    'message'   => sprintf(
                        'Config "%s" is failed to delete.',
                        $row->name
                    )
                );
            }
        }
        foreach ($configList as $name => $config) {
            $row = $modelConfig->createRow($config);
            $status = $row->save();
            if (!$status) {
                return array(
                    'status'    => false,
                    'message'   => sprintf(
                        'Config "%s" is not created.',
                        $config['name']
                    ),
                );
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function uninstallAction()
    {
        $module = $this->event->getParam('module');
        Pi::registry('config')->clear($module);

        $modelCategory = Pi::model('config_category');
        $modelConfig = Pi::model('config');
        $modelCategory->delete(array('module' => $module));
        $modelConfig->delete(array('module' => $module));

        return true;
    }
}
