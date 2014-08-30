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
use Module\User\Field\AbstractCustomHandler;

/**
 * User meta setup
 *
 * Meta data registered to user module
 *
 * - Profile field registry
 * - Profile compound field registry
 * - Timeline registry
 * - Activity registry
 * - Quicklink registry
 *
 * <code>
 *  array(
 *      // Profile field
 *      'field' => array(
 *          // Field with simple text input
 *          <field-key> => array(
 *              // Field type, optional, default as 'profile'
 *              // For non-user module, only 'profile' is accepted
 *              'type'          => 'profile',
 *              // Field name, optional, will be set as <module>_<field-key>
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
 *              // Editable by user
 *              'is_edit'       => true,
 *              // Display on user profile page, default as true
 *              'is_display'    => true,
 *              // Search user by this field, default as true
 *              'is_search'     => false,
 *              // Is required by profile, default as false
 *              'is_required'   => false,
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
 *              // Filter for value processing for output
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
 *              // Filter for value processing for output
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
 *              'handler'       => <Custom\User\Field\HandlerClass>,
 *          ),
 *
 *          <...>,
 *
 *          // Compound
 *          <compound-field-key> => array(
 *              // Field type, MUST be 'compound'
 *              'type'          => 'compound',
 *              // Field name, optional, will be set as <module>_<field-key>
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
 *
 *          // Custom compound
 *          <custom-field-key> => array(
 *              // Field type, MUST be 'custom'
 *              'type'          => 'custom',
 *              // Field name, optional, will be set as <module>_<field-key>
 *              // if not specified
 *              'name'          => <specified_field_name>,
 *              'title'         => __('Custom Compound'),
 *
 *              // Callback class for operation handling
 *              'handler'       => <Custom\User\Field\HandlerClass>,
 *
 *              'field' => array(
 *                  <field-key> => array(
 *                      'title'         => __('Custom Field Item'),
 *
 *                      // Edit element specs
 *                      'edit'          => 'text',
 *                      // Filter for value processing for output
 *                      'filter'        => <output-filter>
 *                  ),
 *                  <...>,
 *              ),
 *          ),
 *      ),
 *
 *      // Timeline
 *      'timeline'      => array(
 *          <name>  => array(
 *              'title' => __('Timeline Title'),
 *              ['icon'  => <img-src>,]
 *          ),
 *          <...>
 *      ),
 *
 *      // Activity
 *      'activity'      => array(
 *          <name>  => array(
 *              'title'     => __('Activity Title'),
 *              'callback'  => <callback>
 *              //'link'      => <link-to-full-list>,
 *
 *              ['icon'     => <img-src>,]
 *          ),
 *          <...>
 *      ),
 *
 *      // Quicklink
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
     * Resource name: if `name` is not specified, `name` will be defined
     * as module name followed by field key and delimited by underscore `_`
     * as `<module-name>_<field_key>`
     *
     * @param array $config
     * @return array
     */
    protected function canonize($config)
    {
        $result = array(
            'field'             => array(),
            'compound_field'     => array(),
            'timeline'          => array(),
            'activity'          => array(),
            'quicklink'         => array(),
        );

        $module = $this->getModule();
        // Canonize fields
        if (isset($config['field'])) {
            $profile = $this->canonizeProfile($config['field']);
            $result['field'] = $profile['field'];
            if (isset($profile['compound_field'])) {
                $result['compound_field'] = $profile['compound_field'];
            }
        }

        foreach (array('timeline', 'activity', 'quicklink') as $op) {
            if (isset($config[$op])) {
                foreach ($config[$op] as $key => $spec) {
                    // Canonize field name
                    if (!empty($spec['name'])) {
                        $name = $spec['name'];
                    } else {
                        $name = $module . '_' . $key;
                        $spec['name'] = $name;
                    }
                    if (!isset($spec['active'])) {
                        $spec['active'] = 1;
                    }
                    if (!isset($spec['module'])) {
                        $spec['module'] = $module;
                    }
                    $result[$op][$name] = $spec;
                }
            }
        }

        return $result;
    }

    /**
     * Canonize user profile specs
     *
     * Field name: if field `name` is not specified, `name` will be defined
     * as module name followed by field key and delimited by underscore `_`
     * as `<module-name>_<field_key>`
     *
     * Canonize profile fields and compound fields.
     * Use <compound-name>-<field-name> as compound field key (not field name).
     *
     * @param array $config
     * @return array
     */
    public function canonizeProfile(array $config)
    {
        $profile = array(
            'field'             => array(),
            'compound_field'    => array(),
        );

        $module = $this->getModule();
        foreach ($config as $key => $data) {
            // Skip empty fields
            if (!$data) {
                continue;
            }
            $data = $this->canonizeField($data);
            if (!isset($data['active'])) {
                $data['active'] = 1;
            }
            if (!isset($data['module'])) {
                $data['module'] = $module;
            }
            if (!isset($data['name'])) {
                $data['name'] = $data['module'] . '_' . $key;
            }
            if (isset($data['field'])) {
                $profile['compound_field'] += $this->canonizeCompoundField(
                    $data['field'],
                    $data
                );
                unset($data['field']);
            }
            $profile['field'][$data['name']] = $data;
        }

        return $profile;
    }

    /**
     * Canonize a profile field specs
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
     * 3. Add edit specs if `is_edit` is `true` or not specified
     *
     * @param array $spec
     * @return array
     * @see Pi\Application\Service\User::canonizeField()
     */
    protected function canonizeField($spec)
    {
        if (!isset($spec['handler'])) {
            $spec['handler'] = '';
        }
        if (!isset($spec['type'])) {
            if (isset($spec['field'])) {
                $spec['type'] = 'compound';
            } else {
                $spec['type'] = 'profile';
            }
        }
        if ('compound' == $spec['type']/* || 'custom' == $spec['type']*/) {
            $spec['is_display'] = 1;
            $spec['is_edit']    = 1;
            $spec['is_search']  = 0;

            return $spec;
        }

        // Canonize editable, display and searchable, default as true
        foreach (array('is_edit', 'is_display', 'is_search') as $key) {
            if (!isset($spec[$key])) {
                $spec[$key] = 1;
            } else {
                $spec[$key] = (int) $spec[$key];
            }
        }

        if (!isset($spec['edit']) && $spec['is_edit']) {
            $spec['edit'] = 'text';
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
            if (!isset($data['module'])) {
                $data['module'] = $compound['module'];
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
        if ('user' != $this->getModule() && !$this->isActive()) {
            return;
        }
        if (empty($this->config)) {
            return;
        }
        Pi::registry('field', 'user')->clear();
        Pi::registry('compound_field', 'user')->clear();

        $profileFields  = array();
        $customNew      = array();
        $config         = $this->canonize($this->config);
        foreach (array(
            'field',
            'compound_field',
            'timeline',
            'activity',
            'quicklink'
        ) as $op) {
            $model = Pi::model($op, 'user');
            foreach ($config[$op] as $key => $spec) {
                $row = $model->createRow($spec);
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
                    } elseif ('profile' == $spec['type']) {
                        $profileFields[] = $key;
                    }
                }
            }
        }
        if ($profileFields) {
            $this->addFields($profileFields);
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
        Pi::registry('field', 'user')->clear();
        Pi::registry('compound_field', 'user')->clear();
        Pi::registry('display_group', 'user')->clear();
        Pi::registry('display_field', 'user')->clear();

        if (!$force && $this->skipUpgrade()) {
            return;
        }

        $custom         = array();
        $fieldsNew      = array();
        $itemsDeleted   = array();
        $config = $this->canonize($this->config);
        foreach (array(
            'field',
            'compound_field',
            'timeline',
            'activity',
            'quicklink'
        ) as $op) {
            $model = Pi::model($op, 'user');
            $rowset = $model->select(array('module' => $module));
            $items = $config[$op];
            $itemsNew[$op] = array();
            $itemsDeleted[$op] = array();
            foreach ($rowset as $row) {
                if ('compound_field' == $op) {
                    $key = $row['compound'] . '-' . $row['name'];
                } else {
                    $key = $row['name'];
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

                    $row->assign($items[$key]);
                    $row->save();

                    if ('field' == $op && $row['handler']) {
                        $custom['update'][] = $row->toArray();
                    }

                    unset($items[$key]);

                // Delete deprecated items
                } else {
                    if ('field' == $op) {
                        if ($row['handler']) {
                            $custom['delete'][] = $row->toArray();
                        } elseif ('profile' == $row['type']) {
                            $itemsDeleted[$op]['profile'][] = $key;
                        } elseif ('compound' == $row['type']) {
                            $itemsDeleted[$op]['compound'][] = $key;
                        }
                        if ('compound' == $row['type']) {
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
                $row = $model->createRow($spec);
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
                        $custom['add'][] = $spec;
                    } elseif ('profile' == $spec['type']) {
                        $fieldsNew[] = $key;
                    }
                }
            }
        }

        // Add new fields to profile
        if ($fieldsNew) {
            $this->addFields($fieldsNew);
        }

        // Delete deprecated user custom profile data
        if ($itemsDeleted['field']) {
            if (!empty($itemsDeleted['field']['profile'])) {
                $this->dropFields($itemsDeleted['field']['profile']);
            }
            if (!empty($itemsDeleted['field']['compound_field'])) {
                Pi::model('compound_field', 'user')->delete(array(
                    'compound' => $itemsDeleted['field']['compound_field'],
                ));
            }
            if (!empty($itemsDeleted['field']['compound'])) {
                Pi::model('compound', 'user')->delete(array(
                    'compound' => $itemsDeleted['field']['compound'],
                ));
            }
        }

        // Delete deprecated user compound profile data
        if ($itemsDeleted['compound_field']) {
            foreach ($itemsDeleted['compound_field'] as $key) {
                list($compound, $field) = explode('-', $key);
                Pi::model('compound', 'user')->delete(array(
                    'compound' => $compound,
                    'field'     => $field,
                ));
            }
        }

        // Delete deprecated timeline log
        if ($itemsDeleted['timeline']) {
            Pi::model('timeline_log', 'user')->delete(array(
                'timeline' => $itemsDeleted['timeline'],
            ));
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
        $module = $this->getModule();
        Pi::model('user_data')->delete(array('module' => $module));

        if (!$this->isActive() || 'user' == $module) {
            return;
        }
        Pi::registry('field', 'user')->clear();
        Pi::registry('compound_field', 'user')->clear();
        Pi::registry('display_group', 'user')->clear();
        Pi::registry('display_field', 'user')->clear();

        $fields         = array();
        $compounds      = array();
        $customs        = array();
        $model          = Pi::model('field', 'user');
        $rowset         = $model->select(array('module' => $module));
        foreach ($rowset as $row) {
            if ($row['handler']) {
                $customs[] = $row->toArray();
            } elseif ('profile' == $row['type']) {
                $fields[] = $row['name'];
            } elseif ('compound' == $row['type']) {
                $compounds[] = $row['name'];
            }
        }
        // Remove module profile data
        if ($fields) {
            $this->dropFields($fields);
        }

        // Remove module profile data
        if ($compounds) {
            Pi::model('compound', 'user')->delete(array(
                'compound'  => $compounds,
            ));
        }

        foreach (array(
            'field',
            'compound_field',
            'timeline',
            'activity',
            'quicklink',
            'timeline_log'
        ) as $op) {
            $model = Pi::model($op, 'user');
            $model->delete(array('module' => $module));
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
        // Skip for active user module, or other modules w/o user installed
        if (!$this->isActive() || ($this->isActive() && 'user' == $module)) {
            return;
        }

        $module = $this->getModule();
        Pi::registry('field', 'user')->clear();
        Pi::registry('compound_field', 'user')->clear();
        Pi::registry('display_group', 'user')->clear();
        Pi::registry('display_field', 'user')->clear();

        foreach (array('field', 'timeline', 'activity', 'quicklink')
            as $op
        ) {
            $model = Pi::model($op, 'user');
            $model->update(array('active' => 1), array('module' => $module));
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
        Pi::registry('field', 'user')->clear();
        Pi::registry('compound_field', 'user')->clear();
        Pi::registry('display_group', 'user')->clear();
        Pi::registry('display_field', 'user')->clear();

        foreach (array('field', 'timeline', 'activity', 'quicklink')
            as $op
        ) {
            $model = Pi::model($op, 'user');
            $model->update(array('active' => 0), array('module' => $module));
        }

        return true;
    }

    /**
     * Add new fields to profile table
     *
     * @param string[] $fields
     *
     * @return bool
     */
    protected function addFields(array $fields)
    {
        //$meta = Pi::registry('field', 'user')->read('account');
        $table = Pi::model('profile', 'user')->getTable();
        $meta = Pi::db()->metadata()->getColumns($table);
        $pattern = 'ALTER TABLE ' . $table . ' ADD `%s` text';
        foreach ($fields as $field) {
            if (isset($meta[$field])) {
                continue;
            }
            $sql = sprintf($pattern, $field);
            try {
                Pi::db()->query($sql);
            } catch (\Exception $exception) {
                $this->setResult('profile-field', array(
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
        $table = Pi::model('profile', 'user')->getTable();
        $meta = Pi::db()->metadata()->getColumns($table);
        $pattern = 'ALTER TABLE ' . $table . ' DROP `%s`';
        foreach ($fields as $field) {
            if (!isset($meta[$field])) {
                continue;
            }
            $sql = sprintf($pattern, $field);
            try {
                Pi::db()->query($sql);
            } catch (\Exception $exception) {
                $this->setResult('profile-field', array(
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
        foreach ($list as $compound) {
            $handler = new $compound['handler'];
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
