# Manage user module activity
* Url supported from version 1.3.6

You need add config/user.php file on your module config by same codes
```
return array(
    // Activity
    'activity' => array(
        'post'    => array(
            'title' => _a('Comment posts by me'),
            'icon'  => '',
            'callback'  => 'Module\Comment\Comment\Post',
            'url' => JSON_ARRAY
        ),
    ),
);

```
for icons you can use http://fontawesome.io/icons/ and just add icon name , like : fa-building

And call it on module.php, 
```
    // Resource
    'resource' => array(
        // Database meta
        'database'      => array(
            // SQL schema/data file
            'sqlfile'   => 'sql/mysql.sql',
        ),
        'config'        => 'config.php',
        'user'          => 'user.php',
        'block'         => 'block.php',
        'navigation'    => 'nav.php',
        'route'         => 'route.php',
        'comment'       => 'comment.php',
        'event'         => 'event.php',
    ),
```

Check comment module for more informations