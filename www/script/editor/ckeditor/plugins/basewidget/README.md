# Base widget plugin
## Description

This plugin allows you to use reuse,override and define functions. It also enables you to decide which nested functions will be exucted in the base.

## Configuration

```
require:'basewidget'
```

### Sample widget creation process


     var definition = {
        template: '',
        extend: { //Functions defined here will be executed with the same context as the one in the base widget
            init: function() {

            }
        },
        configuration: {
            init: { //Specifies in which main function you are controlling the sub-functions flow.
                blockEvents: false, //blockEvents function will not be executed
                configToolbar: { //Configure the popup menu bar on each widget
                defaultButtons: {
                    edit: {
                        onClick: function() { //All onClick functions are bound to the widget context

                        }
                    }
                },
                buttons: [{
                    label: 'added from config',
                    icon: '',
                    onClick: function() {

                    }
                }]
                }
            }
        },
        editables: {
            contentfield: {
                selector: '.bootstrapalert',
                allowedContent: ''
            }
        }
    };

    CKEDITOR.basewidget.addWidget(editor, name, def);
    ```

    #### If you want to override entire main function defined in the base just add it alone in the definition object.
