
Implement Tag in a module
=========================

In edit form
-------------

- Regular source

```
$form->add(
    array(
        'name'      => <element-name>,
        'type'      => 'tag',
        'options'   => array(
            ['module'    => <module>,]
            'item'      => <item-id>,
            ['type'      => <type>,]
        ),
    )
);
```

- Draft source

```
$form->add(
    array(
        'name'      => <element-name>,
        'type'      => 'tag',
        'options'   => array(
            'active'    => false,
            ['module'    => <module>,]
            'item'      => <item-id>,
            ['type'      => <type>,]
        ),
    )
);
```

In submission page
-----------------------

- Add tags to a regular source

```
Pi::service('tag')->add(<module>, <item-id>, <type>, <post value from element-name>, <time>);
```

- Add tags to a draft source

```
Pi::service('tag')->add(<module>, <item-id>, <type>, <post value from element-name>, <time>, false);
```

In delete page
-----------------------

- Delete a regular source

```
Pi::service('tag')->delete(<module>, <item-id>, <type>);
```

- Delete a draft source

```
Pi::service('tag')->delete(<module>, <item-id>, <type>, false);
```

In approval/disapproval page
-----------------------

- Activate a draft source to a regular source

```
Pi::service('tag')->enable(<module>, <item-id>, <type>);
```

- Deactivate a regular source to a draft source

```
Pi::service('tag')->disable(<module>, <item-id>, <type>);
```

In content display page
------------------------

```
// Comprehensive mode
<?php echo $this->tag(array(['module' => <module>, ]'item' => <item-id>[, 'type' => <type>]), array(<attributes>)); ?>

// Simple mode, only applicable of variable name for item is `id`
<?php echo $this->tag(); ?>
```


-- @taiwen