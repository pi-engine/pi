
Implement Tag in a module
=========================

In edit form
-------------

```
$form->add(
    array(
        'name'      => <element-name>,
        'type'      => 'tag',
        'options'   => array(
            'module'    => <module>,
            'item'      => <item-id>,
            'type'      => <type>,
        ),
    )
);
```

In post receiving page
-----------------------

```
Pi::service('tag')->add(<module>, <item-id>, <type>, <post value from element-name>));
```

In content display page
------------------------

```
// Comprehensive mode
<?php echo $this->tag(array('module' => <module>, 'item' => <item-id>, 'type' => <type>), array(<attributes>)); ?>

// Simple mode, only applicable of variable name for item is `id`
<?php echo $this->tag(); ?>
```


-- @taiwen