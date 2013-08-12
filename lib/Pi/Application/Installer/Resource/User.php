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
 * User meta setup
 *
 * Meta data registered to user module
 *
 * - Profile field registry
 * - Timeline registry
 * - Activity registry
 * - Quicklink registry
 *
 * <code>
 *  array(
 *      'field' => array(
 *          // Field with simple text input
 *          'field_name_a' => array(
 *              'title'         => __('Field Name A'),
 *              'description'   => __('Description of field A.'),
 *              'value'         => 'field value',
 *
 *              // Edit element specs
 *              'edit'          => 'text',
 *              // Filter for value processing for output
 *              'filter'        => <output-filter>
 *
 *              // Editable by user
 *              'is_edit'       => true,
 *              // Display on user profile page, default as true
 *              'is_display'    => true,
 *              // Search user by this field, default as true
 *              'is_search'     => false,
 *          ),
 *          // Field with specified edit with form element and filter
 *          'field_name_b' => array(
 *              'title'         => __('Field Name B'),
 *              'description'   => __('Description of field B.'),
 *              'value'         => 1,
 *              'edit'          => array(
 *                  'element'       => array(
 *                      'type'          => 'select'
 *                      'attributes'    => array(
 *                         'options'    => array(
 *                             0  => 'Option A',
 *                             1  => 'Option B',
 *                        ),
 *                   ),
 *                  'filters'       => array(
 *                  ),
 *                  'validators'    => array(
 *                  ),
 *              ),
 *              // Filter specs
 *              'filter'        => 'int',
 *          ),
 *          // Field with specified edit with simple element
 *          'field_name_c' => array(
 *              'title'         => __('Field Name C'),
 *              'description'   => __('Description of field C.'),
 *              'value'         => 1,
 *              'edit'          => array(
 *                  'element'       => 'text',
 *                  'validators'    => array(<...>),
 *              ),
 *              // Filter specs
 *              'filter'        => 'int',
 *          ),
 *
 *          // Field with no edit element, it will be handled as 'text'
 *          'field_name_d' => array(
 *              'title'         => __('Field Name D'),
 *              'description'   => __('Description of field D.'),
 *              'value'         => <field-value>,
 *          ),
 *
 *          <...>,
 *      ),
 *
 *      'timeline'      => array(
 *          <name>  => array(
 *              'title' => __('Timeline Title'),
 *              ['icon'  => <img-src>,]
 *          ),
 *          <...>
 *      ),
 *
 *      'activity'      => array(
 *          <name>  => array(
 *              'title' => __('Activity Title'),
 *              'link'  => <link-to-full-list>,
 *
 *              ['icon'  => <img-src>,]
 *          ),
 *          <...>
 *      ),
 *
 *      'quicklink'     => array(
 *          <name>  => array(
 *              'title' => __('Link Title'),
 *              'link'  => <link-href>,
 *              ['icon'  => <img-src>]
 *          ),
 *          <...>
 *      ),
 *  );
 * </code>
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class User extends AbstractResource
{
    /**
     * Check if user spec is applicable
     *
     * @return bool
     */
    protected function isActive()
    {
        return Pi::service('module')->isActive('user') ? true : false;
    }

    /**
     * Canonize user specs for profile, timeline, activity meta and quicklinks
     *
     * @param array $config
     * @return array
     */
    protected function canonize($config)
    {
        $ret = array(
            'field'         => array(),
            'timeline'      => array(),
            'activity'      => array(),
            'quicklink'     => array(),
        );

        if (isset($config['field'])) {
            foreach ($config['field'] as $key => &$spec) {
                $spec = $this->canonizeField($spec);
            }
        }

        foreach (array('field','timeline', 'activity', 'quicklink')
            as $op
        ) {
            if (isset($config[$op])) {
                foreach ($config[$op] as $key => $spec) {
                    $ret[$op][$key] = array_merge($spec, array(
                        'name'      => $key,
                        'module'    => $this->getModule(),
                    ));
                }
            }
        }

        return $ret;
    }

    /**
     * Canonize a profile field specs
     *
     * @param array $spec
     * @return array
     */
    protected function canonizeField($spec)
    {
        // Canonize editable, display and searchable, default as true
        foreach (array('is_edit', 'is_display', 'is_search') as $key) {
            if (!isset($spec[$key])) {
                $spec[$key] = 1;
            } else {
                $spec[$key] = (int) $spec[$key];
            }
        }

        if (!isset($spec['edit']) && $spec['is_edit']) {
            $spec['edit'] = array(
                'element'   => array(
                    'type'  => 'text',
                ),
            );
        }

        if (isset($spec['edit'])) {
            if (is_string($spec['edit'])) {
                $spec['edit'] = array(
                    'element'   => array(
                        'type'  => $spec['edit'],
                    ),
                );
            } elseif (!$spec['edit']['element']) {
                $spec['edit']['element'] = array(
                    'type'  => 'text',
                );
            } elseif (is_string($spec['edit']['element'])) {
                $spec['edit']['element'] = array(
                    'type'  => $spec['edit']['element'],
                );
            }
        }

        return $spec;
    }

    /**
     * {@inheritDoc}
     */
    public function installAction()
    {
        if (!$this->isActive()) {
            return;
        }
        if (empty($this->config)) {
            return;
        }

        $config = $this->canonize($this->config);
        foreach (array('field','timeline', 'activity', 'quicklink')
            as $op
        ) {
            $model = Pi::model($op, 'user');
            foreach ($config[$op] as $key => $spec) {
                $row = $model->creatRow($spec);
                $status = $row->save();
                if (!$status) {
                    return array(
                        'status'    => false,
                        'message'   => sprintf(
                            '%s "%s" is not created.',
                            $op,
                            $key
                        ),
                    );
                }
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function updateAction()
    {
        if (!$this->isActive()) {
            return;
        }
        $module = $this->getModule();
        Pi::registry('user')->clear($module);

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
        if (!$this->isActive()) {
            return;
        }
        $module = $this->event->getParam('module');
        Pi::registry('config')->clear($module);

        $modelCategory = Pi::model('config_category');
        $modelConfig = Pi::model('config');
        $modelCategory->delete(array('module' => $module));
        $modelConfig->delete(array('module' => $module));

        return true;
    }
}
