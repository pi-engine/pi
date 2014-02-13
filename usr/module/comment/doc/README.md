How to implement Comment in a module
====================================

Step 1. Set up comment specification in module meta configuration

- Define types of items to be commented
- Provide parameters or methods to identify/locate an item to comment on
  - Use variables: `module`, `controller`, `action`, `identifier`, `params`
  - Use custom locator
- Provide callback to fetch commented item information
- Code

    In `config/module.php`
    ```
        'resource'  => array(
            ...
            'comment'   => 'comment.php',
        ),
    ```

    In `config/comment.php`
    ```
    return array(
        '<comment-type-a>' => array(
            'title'         => _a('Comments for A'),
            'icon'          => 'icon-post',

            // Optional, Api\Content will be used to fetch source data if no callback available
            'callback'      => '<Class\To\Fetch\Object\Information>',

            'locator'       => array(
                'controller'    => '<controller-to-match-this-comment-type>',
                'action'        => '<action-to-match-this-comment-type>',
                'identifier'    => '<param-to-identify-object>',
                'params'        => array(
                    <extra-param-pairs-to-identify-the-comment>,
                ),
            ),
        ),
        'example' => array(
            'title'         => _a('Article comments'),
            'icon'          => 'icon-post',

            // Optional, Api\Content will be used to fetch source data if no callback available
            'callback'      => 'Module\<ModuleName>\Api\Article',

            'locator'       => array(
                'controller'    => 'demo',
                'action'        => 'index',
                'identifier'    => 'id',
                'params'        => array(
                    'enable'    => 'yes',
                ),
            ),
        ),
        'custom' => array(
            'title'     => _a('Custom comments'),
            'icon'      => 'icon-post',

            // Optional, Api\Content will be used to fetch source data if no callback available
            'callback'  => 'Module\<ModuleName>\Api\Custom',

            'locator'   => 'Module\<ModuleName>\Api\Custom',
        ),
    );
    ```

Step 2. Build callback for item information fetch

- Callback is optional, `Module\<ModuleName>\Api\Content` will be used to fetch source data if no callback available
- Callback class must extend `Pi\Application\Api\AbstractComment`
- The callback is recommended to locate in module api folder
- Check `Module\Comment\Api\Article` and `Module\Comment\Api\Custom` for example

Step 3. Build custom locator to identify target item, JIC

- Build the locator only if 'locator' is specified in comment specifications
- Extend `Pi\Application\Api\AbstractComment` with method `locate`
- Check `Module\Comment\Api\Custom::locate` for example

Step 4. Increase module version number and update