# User module API design


### Fields for **account** and basic **profile** registry in user module `config/profile.php`
    * Field name will be defined as **<module-name>_<field-key>** if not specified
    * The entities will be inserted into `field` table with `type` as **account** or **profile**
    * User account data and profile data for the fields will be inserted into **account** and **profile** respectively

```
    array(
        'account' => array(
            <field-key>    => array(
                // Field name: if the 'name' is not specified, field name will be defined as '<module-name>_<field-key>'
                'name'          => <field-name>
                // Title
                'title'         => <Field Title>
                // Edit element specs
                'edit'          => <form-element-filter-validator>,
                // Filter for value processing for output
                'filter'        => <output-filter>
                // Is editable by admin, default as true
                'is_edit'       => <bool>,
                // Display on user profile page, default as true
                'is_display'    => <bool>,
                // Search user by this field, default as true
                'is_search'     => <bool>,
            ),
            <...>,
        ),
        'profile' => array(
            <field-key>    => array(
                // Field name: if the 'name' is not specified, field name will be defined as '<module-name>_<field-key>'
                'name'          => <field-name>
                // Title
                'title'         => <Field Title>
                // Edit element specs
                'edit'          => <form-element-filter-validator>,
                // Filter for value processing for output
                'filter'        => <output-filter>
                // Is editable by admin, default as true
                'is_edit'       => <bool>,
                // Display on user profile page, default as true
                'is_display'    => <bool>,
                // Search user by this field, default as true
                'is_search'     => <bool>,
            ),
            <...>,
        ),
    ),
```

### Compound fields registry in user module `config/compound.php`
    * Compound name will be defined as **<module-name>_<field-key>** if not specified
    * Compound entities will be inserted into `field` table with `type` as **compound**
    * Fields of compounds will be inserted into `compound_field` table
    * User data for the compounds will be inserted into compound tables respectively
```
    // Social networking tools
    'tool'      => array(
        'name'  => 'tool',
        'title' => __('Social tools'),
        'table' => 'tool',

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
        'table' => 'address',

        'field' => array(
            'postcode'   => array(
                'title' => __('Post code'),
            ),
            'country'   => array(
                'title' => __('Country'),
            ),
            'province'   => array(
                'title' => __('Province'),
            ),
            'city'   => array(
                'title' => __('City'),
            ),
            'street'   => array(
                'title' => __('Street'),
            ),
            'office'   => array(
                'title' => __('Office'),
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

### Compound fields by **user** module
   * Load `field` from `extra/user/config/compound.php` if file available, otherwise
   * Load `field` from `module/user/config/compound.php`

### Custom profile field registry in a module `config/user.php` with key `profile`
   * The entities will be inserted into `field` table with:
     * Field name will be defined as **<module-name>_<field-key>** if not specified
     * Field type is set: `type` as **custom**
   * User profile data for active fields will be inserted into `custom` table

```
    array(
        'field' => array(
            <field-key>    => array(
                // Field name: if the 'name' is not specified, field name will be defined as '<module-name>_<field-key>'
                'name'          => <field-name>
                // Title
                'title'         => <Field Title>
                // Edit element specs
                'edit'          => <form-element-filter-validator>,
                // Filter for value processing for output
                'filter'        => <output-filter>
                // Is editable, default as true
                'is_edit'       => <bool>,
                // Display on user profile page, default as true
                'is_display'    => <bool>,
                // Search user by this field, default as true
                'is_search'     => <bool>,
            ),
            <...>,
        ),
    ),
```

### Custom profile fields by **user** module
   * Load `field` from `extra/user/config/user.php` if file available, otherwise
   * Load `field` from `module/user/config/user.php`

## User module API

### API class
 `use Module\User\Api\User as UserApi;`


### Canonize meta data
* Call by `UserApi::canonizeMeta($rawData, $action = null)`
* Return array for account, profile, custom

```
$result = array(
    'account'   => array(),
    'profile'   => array(),
    'custom'    => array(),
);

foreach ($result as $type => &$data) {
    $fields = Pi::service('user')->getMeta($type, $action);
    foreach ($fields as $field) {
        if (in_array($field, $rawData)) {
            $data[] = $field;
        }
    }
}

return $result;
```

### Canonize user data
* Call by `UserApi::canonizeUser($rawData)`
* Return associative array of account, profile, custom

```
$result = array(
    'account'   => array(),
    'profile'   => array(),
    'custom'    => array(),
);

foreach ($result as $type => &$data) {
    $fields = Pi::service('user')->getMeta($type);
    foreach ($fields as $field) {
        if (array_key_exists($field, $rawData)) {
            $data[$key][$field] = $rawData[$field];
        }
    }
}

return $result;
```

### Add user
* Call by `UserApi::add(array $data)` return `<uid>`
```
$data = UserApi::canonizeUser($data);
$uid = UserApi::addAccount($data['account']);

$id = UserApi::addProfile($data['profile'], $uid);

$results = UserApi::addCustom($data['custom'], $uid);

return $uid;
```

* Add user account `UserApi::addAccount(array $account)` return `<uid>`
```
$row = Pi::model('account', 'user')->createRow($account);
$uid = $row->save();

return $uid;
```

* Add user profile `UserApi::addProfile(array $profile, $uid)` return `<profile-id>`
```
$profile['uid'] = $uid;
$row = Pi::model('profile', 'user')->createRow($profile);
$id = $row->save();

return $id;
```

* Add user custom entities `UserApi::addCustom($custom, $uid)` return `<bool[]>`
```
$result = array();
foreach (custom as $key => $value) {
    $id = UserApi::addEntity($key, $value, $uid);
    $result[$key] = $id;
}

return $result;
```

* Add user custom entity `UserApi::addEntity($name, $value, $uid)` return `<entity-id>`
```
$entity = array(
    'uid'   => $uid,
    'field' => $name,
    'value' => $value,
);
$row = Pi::model('custom', 'user')->createRow($entity);
$id = $row->save();

return $id;
```

### Update user
* Call by `UserApi::update(array $data, $uid)` return `<bool>`
```
$data = UserApi::canonizeUser($data);
$status = UserApi::updateAccount($data['account'], $uid);
$status = UserApi::updateProfile($data['profile'], $uid);
$status = UserApi::updateCustom($data['custom'], $uid);

return $status;
```

* Update user account `UserApi::updateAccount(array $account, $uid)` return `<bool>`
```
$status = Pi::model('account', 'user')->update($account, array('id' => $uid));
```

* Update user profile `UserApi::updateProfile(array $profile, $uid)` return `<bool>`
```
$status = Pi::model('profile', 'user')->update($profile, array('uid' => $uid));
```

* Update user custom profile `UserApi::updateCustom(array $custom, $uid)` return `<bool>`
```
foreach (custom as $key => $value) {
    $status = UserApi::updateEntity($key, $value, $uid);
}
```

* Update user custom entity `UserApi::updateEntity($name, $value, $uid)` return `<bool>`
```
$status = Pi::model('custom', 'user')->update(
    array('value' => $value),
    array(
        'field' => $name,
        'uid'   => $uid,
    )
);
```

### Delete user
* Call by `UserApi::delete($uid)` return `<bool>`
```
$status = UserApi::deleteAccount($uid);
$status = UserApi::deleteProfile($uid);
$status = UserApi::deleteCustom($uid);

return $status;
```

* Delete user account `UserApi::deleteAccount($uid)` return `<bool>`
```
$status = Pi::model('account', 'user')->delete(array('id' => $uid));
```

* Delete user profile `UserApi::deleteProfile($uid)` return `<bool>`
```
$status = Pi::model('profile', 'user')->delete(array('uid' => $uid));
```

* Delete user custom profile `UserApi::deleteCustom($uid)`
return `<bool>`
```
$status = Pi::model('custom', 'user')->delete(array('uid' => $uid));
```

* Delete user custom entity `UserApi::deleteEntity($name, $uid)`
return `<bool>`
```
$status = Pi::model('custom', 'user')->delete(
    array(
        'field' => $name,
        'uid'   => $uid,
    )
);
```

### Get user data with specific fields
* Call by `UserApi::get($uid, $action = null, array $fields = null)`
return associative array
```
$account = array();
$profile = array();
$custom = array();

$account = UserApi::getAccount($uid, $action, $fields);
$profile = UserApi::getProfile($uid, $action, $fields);
$Custom = UserApi::getCustom($uid, $action, $fields);

$result = $account + $profile + $custom;
```

* Get user account `UserApi::getAccount($uid, $action = null, array $fields = null)`
```
$type = 'account';
$model = Pi::model('account', 'user');
$where = array('id' = $uid);

$result = array();
$columns = Pi::registry('profile', 'user')->read($type, $action);

if ($fields) {
    foreach (array_keys($columns) as $field) {
        if (!in_array($field, $fields)) {
            unset(columns[$field]);
        }
    }
}
if (!$columns) {
    return $result;
}
$select = $model->select()->where($where)->columns($columns);
$rowset = $model->selectWith($select);
$result = $rowset->current()->toArray();

if ('display' == $action) {
    foreach ($result as $key => &$value) {
        if ($columns[$key]['filter']) {
            $value = call_user_func($columns[$key]['filter'], $value);
        }
    }
}
```

* Get user profile `UserApi::getProfile($uid, $action = null, array $fields = null)`
```
$type = 'account';
$model = Pi::model('account', 'user');
$where = array('uid' = $uid);

// Same as account
<...>
```

* Get user custom profile `UserApi::getCustom($uid, $action = null, array $fields = null)`
```
$type = 'custom';
$model = Pi::model('custom', 'user');
$where = array('uid' = $uid);

$result = array();
$columns = Pi::registry('profile', 'user')->read($type, $action);

if ($fields) {
    foreach (array_keys($columns) as $field) {
        if (!in_array($field, $fields)) {
            unset(columns[$field]);
        }
    }
}
if (!$columns) {
    return $result;
}
$where['field'] = array_keys($columns);

$select = $model->select()->where($where)->columns($columns);
$rowset = $model->selectWith($select);
foreach ($rowset as $row) {
    $result[$row->field] = $row->value;
}

if ('display' == $action) {
    foreach ($result as $key => &$value) {
        if ($columns[$key]['filter']) {
            $value = call_user_func($columns[$key]['filter'], $value);
        }
    }
}
```

### Get user profile field
* Call by `UserApi::getField($uid, $field, $action = '', $type = '')`
```
$columns = Pi::registry('profile', 'user')->read($type, $action);
if (!isset($columns[$field])) {
    return null;
}
$type = $type ?: $columns[$field]['type];
$method = 'get' . ucfirst($type);
$data = UserApi::{$method}($uid, (array) $field, $action);
if (isset($data[$field])) {
    $result = $data[$field];
} else {
    $result = null;
}
```


## Cache registry

### Load profile fields with type and action
* Call by `Pi::registry('profile', 'user')->read(<type>, <action>)`
return array of fields as `string[]`
* Type: account, profile, custom
* action: display, edit, search

```
$columns = array();
$where = array('active' => 1);
if ($type) {
    $where['type'] = $type;
}

switch ($action) {
    case 'display':
        $columns = array('name', 'title', 'filter');
        $where['is_display'] = 1;
        break;
    case 'edit':
        $columns = array('name', 'title', 'edit');
        $where['is_edit'] = 1;
        break;
    case 'search':
        $columns = array('name', 'title');
        $where['is_search'] = 1;
        break;
    default:
        break;
}
$select = Pi::model('field', 'user')->select()->where($where);
if ($columns) {
    $select->columns($columns);
}
$rowset = Pi::model('field', 'user')->selectWith($select);
foreach ($rowset as $row) {
    $result[$row->name] = $row->toArray();
}
```

## Use cases

### Load fields editable by user
* Identified by `is_edit` attribute
```
$fields = Pi::registry('profile', 'user')->read('', 'edit');
```

### Load fields editable by admin
* Identified by non-empty `edit` attribute
```
$fields = Pi::registry('profile', 'user')->read();
foreach ($fields as $key => $data) {
    if (!$data['edit']) {
        unset($fields[$key]);
    }
}
```

### Load full data of a user
```
$user = array(
    'uid'   => $id,
);

$registryService = Pi::registry('profile', 'user');
$fields = $registryService->read('account');
$row = Pi::model('account', 'user')->find($id);
$account = array(
    'id'    => $row->id,
);
foreach (array_keys($fields) as $key) {
    $account[$key] = $row->{$key};
}
$user += $account;

$fields = $registryService->read('profile');
$row = Pi::model('profile', 'user')->find($id);
$profile = array(
    'id'    => $row->id,
    'uid'   => $row->uid,
);
foreach (array_keys($fields) as $key) {
    $profile[$key] = $row->{$key};
}
$user += $profile;

$fields = $registryService->read('custom');
$rowset = Pi::model('custom', 'user')->select(array(
    'uid'   => $uid,
    'field' => array_keys($fields),
));
$custom = array(
    'uid'   => $row->uid,
);

foreach ($rowset as $row) {
    $custom[$row->field] = $row->value;
}
$user += $custom;
```
