# User architecture


## User specs
__User specs are defined in module config/user.php__

### Fields for **account** and custom **profile** registry
* Field name will be defined as **<module-name>_<field-key>** if not specified
* The meta entities will be inserted into `field` table with `type` as **account**,
    **profile** and **compound** respectively
* Compound field meta will be inserted into `compound_field` table
* User account data will be inserted into **{core}.user_account**,
    custom profile data will be inserted into **{user}.profile**,
    compound data will be inserted into **{user}.compound**

```
    array(
        // Account fields
        <field-key>    => array(
            // Field name: if the 'name' is not specified,
            // field name will be defined as '<module-name>_<field-key>'
            'name'          => <field-name>,
            // Field type, default as 'profile'
            // 'account' type is only allowed in `user` module
            'type'          => 'account',
            // Title
            'title'         => <Field Title>,
            // Edit element specs
            'edit'          => <form-element-filter-validator>,
            // Filter for value processing for output
            'filter'        => <output-filter>,
            // Is editable by admin, default as true
            'is_edit'       => <bool>,
            // Display on user profile page, default as true
            'is_display'    => <bool>,
            // Search user by this field, default as true
            'is_search'     => <bool>,
        ),
        <...>,
        // Custom profile fields
        <field-key>    => array(
            // Field name: if the 'name' is not specified,
            // field name will be defined as '<module-name>_<field-key>'
            'name'          => <field-name>,
            // Field type, default as 'profile'
            'type'          => 'profile',
            // Title
            'title'         => <Field Title>,
            // Edit element specs
            'edit'          => <form-element-filter-validator>,
            // Filter for value processing for output
            'filter'        => <output-filter>,
            // Is editable by admin, default as true
            'is_edit'       => <bool>,
            // Display on user profile page, default as true
            'is_display'    => <bool>,
            // Search user by this field, default as true
            'is_search'     => <bool>,
        ),
        <...>,
        // Compound fields
        // Social networking tools
        'tool'      => array(
            'name'  => 'tool',
            'title' => __('Social tools'),

            'field' => array(
                'title'         => array(
                    'title' => __('Tool name'),
                ),
                'identifier'    => array(
                    'title' => __('ID or URL'),
                ),
            ),
        ),

        // Communication address
        'address'   => array(
            'name'  => 'address',
            'title' => __('Address'),

            'field' => array(
                'postcode'   => array(
                    'title' => __('Post code'),
                ),
                'country'   => array(
                    'title' => __('Country'),
                ),
                'province'  => array(
                    'title' => __('Province'),
                ),
                'city'      => array(
                    'title' => __('City'),
                ),
                'street'    => array(
                    'title' => __('Street'),
                ),
                'room'      => array(
                    'title' => __('Room'),
                ),
            ),
        ),

        // Education experiences
        'education'  => array(
            'name'  => 'education',
            'title' => __('Education'),
            'table' => 'education',

            'field' => array(
                'school'    => array(
                    'title' => __('School name'),
                ),
                'major'    => array(
                    'title' => __('Major'),
                ),
                'degree'    => array(
                    'title' => __('Degree'),
                ),
                'class'    => array(
                    'title' => __('Class'),
                ),
                'start'    => array(
                    'title' => __('Start time'),
                ),
                'end'    => array(
                    'title' => __('End time'),
                ),
            ),
        ),

        // Profession experiences
        'work'      => array(
            'name'  => 'work',
            'title' => __('Work'),
            'table' => 'work',

            'field' => array(
                'company'    => array(
                    'title' => __('Company name'),
                ),
                'department'    => array(
                    'title' => __('Department'),
                ),
                'title'    => array(
                    'title' => __('Job title'),
                ),
                'description'   => array(
                    'title' => __('Description'),
                    'edit'  => 'textarea',
                ),
                'start'    => array(
                    'title' => __('Start time'),
                ),
                'end'    => array(
                    'title' => __('End time'),
                ),
            ),
        ),

```


## User APIs
* User service: `Pi\Application\Service\User`
* User API specs: `Pi\User\Adapter\AbstractAdapter`
* System user API: `Module\System\Api\User`
* User module API: `Module\User\Api\User`

```
