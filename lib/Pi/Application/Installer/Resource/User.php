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
 *              'link'      => <link-to-full-list>,
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
            /*
            foreach ($config['field'] as $key => &$spec) {
                $spec = $this->canonizeField($spec);
            }
            */
        }

        foreach (array('timeline', 'activity', 'quicklink') as $op) {
            if (isset($config[$op])) {
                foreach ($config[$op] as $key => $spec) {
                    // Canonize field name
                    $name = !empty($spec['name'])
                        ? $spec['name']
                        : $module . '_' . $key;
                    if (!isset($spec['active'])) {
                        $spec['active'] = 1;
                    }
                    $result[$op][$name] = array_merge($spec, array(
                        'name'      => $name,
                        'module'    => $module,
                    ));
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
                    $data['name']
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
        if (isset($spec['field'])) {
            $spec['type'] = 'compound';
        }
        if (!isset($spec['type'])
            || ('user' != $this->getModule() && 'compound' != $spec['type'])
        ) {
            $spec['type'] = 'profile';
        }
        if ('compound' == $spec['type']) {
            $spec['is_edit'] = 0;
            $spec['is_display'] = 0;
            $spec['is_search'] = 0;

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
     * @param string $compound
     * @return array
     */
    protected function canonizeCompoundField(array $config, $compound)
    {
        $fields = array();
        $module = $this->getModule();
        foreach ($config as $key => &$data) {
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
            $data['module'] = $module;
            $data['compound'] = $compound;
            $fields[$compound . '-' . $key] = $data;
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
        Pi::registry('profile', 'user')->clear();

        $profileFields = array();
        $config = $this->canonize($this->config);
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
                if ('field' == $op && 'profile' == $spec['type']) {
                    $profileFields[] = $key;
                }
            }
        }

        if ($profileFields) {
            $this->addFields($profileFields);
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
        Pi::registry('profile', 'user')->clear();

        if ($this->skipUpgrade()) {
            return;
        }

        $fieldsNew = array();
        $itemsDeleted = array();
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

                    $row->assign($items[$key]);
                    $row->save();
                    unset($items[$key]);

                // Delete deprecated items
                } else {
                    $itemsDeleted[$op][] = $key;
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
                if ('field' == $op && 'profile' == $spec['type']) {
                    $fieldsNew[] = $key;
                }
            }
        }

        // Add new fields to profile
        if ($fieldsNew) {
            $this->addFields($fieldsNew);
        }

        // Delete deprecated user custom profile data
        if ($itemsDeleted['field']) {
            $this->dropFields($itemsDeleted['field']);
            /*
            Pi::model('profile', 'user')->delete(array(
                'field' => $itemsDeleted['field'],
            ));
            */
            Pi::model('compound_field', 'user')->delete(array(
                'compound' => $itemsDeleted['field'],
            ));
            Pi::model('compound', 'user')->delete(array(
                'compound' => $itemsDeleted['field'],
            ));
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
        Pi::registry('profile', 'user')->clear();

        $model = Pi::model('field', 'user');
        $fields = array();
        $rowset = $model->select(array('module' => $module));
        foreach ($rowset as $row) {
            $fields[] = $row->name;
        }
        // Remove module profile data
        if ($fields) {
            //Pi::model('profile', 'user')->delete(array('field' => $fields));
            $this->dropFields($fields);
        }

        $compounds = array();
        $rowset = $model->select(array(
            'module'    => $module,
            'type'      => 'compound',
        ));
        foreach ($rowset as $row) {
            $compounds[] = $row->name;
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

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function activateAction()
    {
        if (!$this->isActive()) {
            return;
        }
        $module = $this->getModule();
        Pi::registry('profile', 'user')->clear();

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
        Pi::registry('profile', 'user')->clear();

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
        $meta = Pi::registry('profile', 'user')->read('account');
        $table = Pi::model('profile', 'user')->getTable();
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
        $meta = Pi::registry('profile', 'user')->read('profile');
        $table = Pi::model('profile', 'user')->getTable();
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
}
