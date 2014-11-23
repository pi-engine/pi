<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Installer\Resource;

use Pi;
use Pi\Application\Installer\Resource\AbstractResource;
use Module\Article\Field\AbstractCustomHandler;

/**
 * Article custom fields setup
 *
 * Meta data registered to article module
 *
 * - Article common field registry
 * - Article compound field registry
 *
 * <code>
 *  array(
 *      // Common field
 *      'field' => array(
 *          // Field with simple text input
 *          <field-key> => array(
 *              // Field type, optional, default as 'common'
 *              'type'          => 'common',
 *              // Field name, optional, will be set as <field-key>
 *              // if not specified
 *              'name'          => <specified_field_name>,
 *              'title'         => __('Field Name A'),
 *              'description'   => __('Description of field A.'),
 *              'value'         => 'field value',
 *
 *              // Edit element specs
 *              'edit'          => 'text',
 *              // Filter for value processing for output
 *              'filter'        => <output-filter>
 *
 *              // Editable by article
 *              'is_edit'       => true,
 *              // Display on draft edit page, default as true
 *              'is_display'    => true,
 *              // Is required, default as false
 *              'is_required'   => false,
 *              // Table column type, default as text
 *              'field_type'    => 'varchar(255) not null default \'\'',
 *          ),
 *          // Field with specified edit with form element and filter
 *          <field-key> => array(
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
 *          <field-key> => array(
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
 *          <field-key> => array(
 *              'title'         => __('Field Name D'),
 *              'description'   => __('Description of field D.'),
 *              'value'         => <field-value>,
 *          ),
 *
 *
 *          // Field with custom handler
 *          <field-key> => array(
 *              'title'         => __('Field Name E'),
 *              'description'   => __('Description of field E.'),
 *              'value'         => <field-value>,
 *
 *              // Callback class for operation handling
 *              'handler'       => <Custom\Article\Field\HandlerClass>,
 *          ),
 *
 *          <...>,
 *
 *          // Compound
 *          <compound-field-key> => array(
 *              // Field type, MUST be 'compound'
 *              'type'          => 'compound',
 *              // Field name, optional, will be set as <field-key>
 *              // if not specified
 *              'name'          => <specified_field_name>,
 *              'title'         => __('Compound Field'),
 *
 *              'field' => array(
 *                  <field-key> => array(
 *                      'title'         => __('Compound Field Item'),
 *
 *                      // Edit element specs
 *                      'edit'          => 'text',
 *                      // Filter for value processing for output
 *                      'filter'        => <output-filter>
 *                  ),
 *                  <...>,
 *              ),
 *          ),
 *          <...>,
 *      ),
 *  );
 * </code>
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Article extends AbstractResource
{
    /**
     * Load custom navigation config
     */
    protected function loadConfig()
    {
        $module = $this->getModule();
        $config = Api::getCustomConfig('article', $module);
        
        if (!empty($config)) {
            $this->config = $config;
        }
    }
    
    /**
     * Check if article spec is applicable
     *
     * @return bool
     */
    protected function isActive()
    {
        return Pi::service('module')->isActive('article') ? true : false;
    }

    /**
     * Canonize article specs
     *
     * Resource name: if `name` is not specified, `name` will be defined
     * by field key
     *
     * @param array $config
     * @return array
     */
    protected function canonize($config)
    {
        $result = array(
            'field'             => array(),
            'compound_field'    => array(),
        );

        // Canonize fields
        if (isset($config['field'])) {
            $common = $this->canonizeFields($config['field']);
            $result['field'] = $common['field'];
            if (isset($common['compound_field'])) {
                $result['compound_field'] = $common['compound_field'];
            }
        }

        return $result;
    }

    /**
     * Canonize article common fields specs
     *
     * Field name: if field `name` is not specified, `name` will be defined
     * by field key
     *
     * Canonize article fields and compound fields.
     * Use <compound-name>-<field-name> as compound field key (not field name).
     *
     * @param array $config
     * @return array
     */
    public function canonizeFields(array $config)
    {
        $field = array(
            'field'             => array(),
            'compound_field'    => array(),
        );

        foreach ($config as $key => $data) {
            // Skip empty fields
            if (!$data) {
                continue;
            }
            $data = $this->canonizeField($data);
            if (!isset($data['active'])) {
                $data['active'] = 1;
            }
            if (!isset($data['name'])) {
                $data['name'] = $key;
            }
            if (isset($data['field'])) {
                $field['compound_field'] += $this->canonizeCompoundField(
                    $data['field'],
                    $data
                );
                unset($data['field']);
            }
            $field['field'][$data['name']] = $data;
        }

        return $field;
    }

    /**
     * Canonize a field specs
     *
     * Edit specs:
     * Transform
     * `'edit' => <type>` and `'edit' => array('element' => <type>)`
     * to
     * ```
     *  'edit' => array(
     *      'element'   => array(
     *          'type'  => <type>,
     *          <...>,
     *      ),
     *      <...>,
     *  ),
     * ```
     *
     * Add edit specs if `is_edit` is `true` or not specified
     *
     * @param array $spec
     * @return array
     */
    protected function canonizeField($spec)
    {
        if (!isset($spec['field_type'])) {
            $spec['field_type'] = '';
        }
        if (!isset($spec['handler'])) {
            $spec['handler'] = '';
        }
        if (!isset($spec['type'])) {
            if (isset($spec['field'])) {
                $spec['type'] = 'compound';
            } else {
                $spec['type'] = 'common';
            }
        }
        if ('compound' == $spec['type']) {
            $spec['is_display'] = 1;
            $spec['is_edit']    = 1;

            return $spec;
        }

        // Canonize editable and display, default as true
        foreach (array('is_edit', 'is_display') as $key) {
            if (!isset($spec[$key])) {
                $spec[$key] = 1;
            } else {
                $spec[$key] = (int) $spec[$key];
            }
        }

        if (!isset($spec['edit'])) {
            if ($spec['is_edit']) {
                $spec['edit'] = 'text';
            } else {
                $spec['edit'] = 'hidden';
            }
        }

        if (isset($spec['edit'])) {
            $spec['edit'] = $this->canonizeFieldEdit($spec['edit']);
            if (isset($spec['edit']['required'])) {
                $spec['is_required'] = $spec['edit']['required'] ? 1 : 0;
                unset($spec['edit']['required']);
            }
        }

        if (isset($spec['filter'])) {
            if (empty($spec['filter'])) {
                $spec['filter'] = array();
            } else {
                $spec['filter'] = (array) $spec['filter'];
            }
        } else {
            $spec['filter'] = array();
        }

        return $spec;
    }

    /**
     * Canonize compound field
     *
     * Indexing compound field with <compound-name>-<field-name>
     *
     * @param array $config
     * @param array $compound   Compound specs
     * @return array
     */
    protected function canonizeCompoundField(array $config, array $compound)
    {
        $fields = array();
        foreach ($config as $key => &$data) {
            $data['compound'] = $compound['name'];
            if (!isset($data['name'])) {
                $data['name'] = $key;
            }
            if (!isset($data['edit'])) {
                $data['edit'] = 'text';
            }
            $data['edit'] = $this->canonizeFieldEdit($data['edit']);
            if (isset($data['filter'])) {
                if (empty($data['filter'])) {
                    $data['filter'] = array();
                } else {
                    $data['filter'] = (array) $data['filter'];
                }
            }
            if (isset($data['edit']['required'])) {
                $data['is_required'] = $data['edit']['required'] ? 1 : 0;
                unset($data['edit']['required']);
            }

            $fields[$compound['name'] . '-' . $key] = $data;
        }

        return $fields;
    }

    /**
     * Canonize field element edit
     *
     * @param string|array $edit
     * @return array
     */
    protected function canonizeFieldEdit($edit)
    {
        if (is_string($edit)) {
            $edit = array(
                'element'   => array(
                    'type'  => $edit,
                ),
            );
        } elseif (!isset($edit['element'])) {
            $edit['element'] = array(
                'type'  => 'text',
            );
        } elseif (is_string($edit['element'])) {
            $edit['element'] = array(
                'type'  => $edit['element'],
            );
        }

        return $edit;
    }

    /**
     * {@inheritDoc}
     */
    public function installAction()
    {
        $this->loadConfig();
        if (empty($this->config)) {
            return;
        }
        
        $module = $this->getModule();
        
        // Temporary method to avoid module directory can not be found when clone module
        Pi::service('module')->createMeta();

        $commonFields   = array();
        $customNew      = array();
        $config         = $this->canonize($this->config);
        foreach (array(
            'field',
            'compound_field',
        ) as $op) {
            $model = Pi::model($op, $module);
            foreach ($config[$op] as $key => $spec) {
                $data   = $model->canonizeColumns($spec);
                $row    = $model->createRow($data);
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
                if ('field' == $op) {
                    if ($spec['handler']) {
                        $customNew[] = $spec;
                    } elseif ('common' == $spec['type']
                        || 'custom' == $spec['type']
                    ) {
                        if (isset($spec['is_insert']) && $spec['is_insert']) {
                            $commonFields[$key] = $spec['field_type'];
                        }
                    }
                }
            }
        }
        if ($commonFields) {
            $this->addFields($commonFields);
        }
        if ($customNew) {
            $this->custom('add', $customNew);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function updateAction($force = false)
    {
        if (!$this->isActive()) {
            return;
        }
        $module = $this->getModule();
        Pi::registry('field', $module)->clear();
        Pi::registry('compound_field', $module)->clear();

        if (!$force && $this->skipUpgrade()) {
            return;
        }

        $custom         = array();
        $fieldsNew      = array();
        $itemsDeleted   = array();
        $this->loadConfig();
        $config = $this->canonize($this->config);
        foreach (array(
            'field',
            'compound_field',
        ) as $op) {
            $model = Pi::model($op, $module);
            $rowset = $model->select(array());
            $items = $config[$op];
            $itemsNew[$op] = array();
            $itemsDeleted[$op] = array();
            foreach ($rowset as $row) {
                if ('compound_field' == $op) {
                    $key = $row->compound . '-' . $row->name;
                } else {
                    $key = $row->name;
                }
                // Update existent item
                if (isset($items[$key])) {
                    // Titles are editable by admin, don't overwrite
                    unset($items[$key]['name']);
                    unset($items[$key]['title']);
                    unset($items[$key]['active']);
                    if (isset($items[$key]['value'])) {
                        unset($items[$key]['value']);
                    }
                    // field/compound_field required attribute is set by admin
                    if (isset($items[$key]['is_required'])) {
                        unset($items[$key]['is_required']);
                    }
                    if (isset($items[$key]['field_type'])) {
                        unset($items[$key]['field_type']);
                    }
                    if (!isset($items[$key]['filter'])) {
                        $items[$key]['filter'] = '';
                    }

                    $row->assign($items[$key]);
                    $row->save();

                    if ('field' == $op && $row->handler) {
                        $custom['update'][] = $row->toArray();
                    }

                    unset($items[$key]);

                // Delete deprecated items
                } else {
                    if ('field' == $op) {
                        if ($row->handler) {
                            $custom['delete'][] = $row->toArray();
                        } elseif ('common' == $row->type
                            || 'custom' == $row->type
                        ) {
                            $itemsDeleted[$op]['common'][] = $key;
                        } elseif ('compound' == $row->type) {
                            $itemsDeleted[$op]['compound'][] = $key;
                        }
                        if ('compound' == $row->type) {
                            $itemsDeleted[$op]['compound_field'][] = $key;
                        }
                    } else {
                        $itemsDeleted[$op][] = $key;
                    }
                    $row->delete();
                }
            }
            // Add new items
            foreach ($items as $key => $spec) {
                $data   = $model->canonizeColumns($spec);
                $row    = $model->createRow($data);
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
                if ('field' == $op) {
                    if (isset($spec['handler']) && $spec['handler']) {
                        $custom['add'][] = $spec;
                    } elseif ('common' == $spec['type']
                        || 'custom' == $spec['type']
                    ) {
                        if (isset($spec['is_insert']) && $spec['is_insert']) {
                            $fieldsNew[$key] = $spec['field_type'];
                        }
                    }
                }
            }
        }

        // Add new fields to article
        if ($fieldsNew) {
            $this->addFields($fieldsNew);
        }

        // Delete deprecated user custom profile data
        if ($itemsDeleted['field']) {
            if (!empty($itemsDeleted['field']['common'])) {
                $this->dropFields($itemsDeleted['field']['common']);
            }
            if (!empty($itemsDeleted['field']['compound_field'])) {
                Pi::model('compound_field', $module)->delete(array(
                    'compound' => $itemsDeleted['field']['compound_field'],
                ));
            }
        }
        
        // Custom compound
        foreach ($custom as $op => $list) {
            $this->custom($op, $list);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function uninstallAction()
    {
        $module    = $this->getModule();
        $directory = Pi::service('module')->directory($module);

        if (!$this->isActive() || 'article' !== $directory) {
            return;
        }
        Pi::registry('field', $module)->clear();
        Pi::registry('compound_field', $module)->clear();

        $this->loadConfig();
        $config = $this->canonize($this->config);
        $customs = array();
        foreach ($config['field'] as $row) {
            if (isset($row['handler']) && $row['handler']) {
                $customs[] = $row;
            }
        }

        if ($customs) {
            $this->custom('drop', $customs);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function activateAction()
    {
        $module = $this->getModule();
        $directory = Pi::service('module')->directory($module);
        
        if (!$this->isActive() || 'article' !== $directory) {
            return;
        }

        Pi::registry('field', $module)->clear();
        Pi::registry('compound_field', $module)->clear();

        foreach (array('field')
            as $op
        ) {
            $model = Pi::model($op, $module);
            $model->update(array('active' => 1));
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function deactivateAction()
    {
        if (!$this->isActive()) {
            return;
        }
        $module = $this->getModule();
        Pi::registry('field', $module)->clear();
        Pi::registry('compound_field', $module)->clear();

        foreach (array('field')
            as $op
        ) {
            $model = Pi::model($op, $module);
            $model->update(array('active' => 0));
        }

        return true;
    }

    /**
     * Add new fields to article table
     *
     * @param string[] $fields
     *
     * @return bool
     */
    protected function addFields(array $fields)
    {
        $module = $this->getModule();
        $table = Pi::model('article', $module)->getTable();
        $meta = Pi::db()->metadata()->getColumns($table);
        $pattern = 'ALTER TABLE ' . $table . ' ADD `%s` %s';
        foreach ($fields as $field => $type) {
            if (isset($meta[$field])) {
                continue;
            }
            $type = $type ?: 'text';
            $sql = sprintf($pattern, $field, $type);
            try {
                Pi::db()->query($sql);
            } catch (\Exception $exception) {
                $this->setResult('article-field', array(
                    'status'    => false,
                    'message'   => 'Table alter query failed: '
                    . $exception->getMessage(),
                ));

                return false;
            }
        }

        return true;
    }

    /**
     * Drop fields from profile table
     *
     * @param string[] $fields
     *
     * @return bool
     */
    protected function dropFields(array $fields)
    {
        $module = $this->getModule();
        $table  = Pi::model('article', $module)->getTable();
        $rowset = Pi::db()->metadata()->getColumns($table);
        $meta   = array();
        foreach ($rowset as $row) {
            $meta[] = $row->getName();
        }
        $pattern = 'ALTER TABLE ' . $table . ' DROP `%s`';
        foreach ($fields as $field) {
            if (!in_array($field, $meta)) {
                continue;
            }
            $sql = sprintf($pattern, $field);
            try {
                Pi::db()->query($sql);
            } catch (\Exception $exception) {
                $this->setResult('article-field', array(
                    'status'    => false,
                    'message'   => 'Table alter query failed: '
                    . $exception->getMessage(),
                ));

                return false;
            }
        }

        return true;
    }


    /**
     * Custom compound operations
     *
     * @param string $op
     * @param array $list
     *
     * @return bool
     */
    protected function custom($op, array $list)
    {
        $module = $this->getModule();
        foreach ($list as $compound) {
            $handler = new $compound['handler']($module);
            if (!$handler instanceof AbstractCustomHandler) {
                continue;
            }
            switch ($op) {
                case 'add':
                    $handler->install();
                    break;
                case 'update':
                    $handler->modify();
                    break;
                case 'delete':
                case 'drop':
                    $handler->uninstall();
                    break;
                default:
                    break;
            }
        }

        return true;
    }
}
