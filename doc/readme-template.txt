
--------------------------------------------------------------------------------
To fetch a template using template view helper:
<code>
    $this->template($name);
</code>
'$name' parameter:
    Full path to a template, suffix .phtml is required
        '/full/path/to/template.phtml'
    Path to a module template, suffix MUST be omitted
        $module . ':path/to/template'
        'path/to/template'
    Path to a theme template, suffix MUST be omitted
        'path/to/template'
    Path to a component template, suffix MUST be omitted
        $component . ':path/to/template'

To fetch a module template using templateModule view helper:
<code>
    $this->templateModule($name, $module = null);
</code>
'$name' parameter:
    Relative path to a module template, suffix MUST be omitted
        'path/to/template'
'$module' parameter:
    Module name, current module name will be used if omitted

To fetch a theme template using templateTheme view helper:
<code>
    $this->templateTheme($name);
</code>
'$name' parameter:
    Relative path to a template template, suffix MUST be omitted
        'path/to/template'

To fetch a view component template using templateComponent view helper:
<code>
    $this->templateComponent($name);
</code>
'$name' parameter:
    Relative path to a view component inside system module, suffix MUST be omitted, currently only following components are available
        'form'
        'form-popup'
        'form-vertical'


--------------------------------------------------------------------------------
Predefined variables

In theme templates:
* Set from Pi\View\Helper\Meta::assign()
    sitename
    slogan
    locale
    charset
    adminmail
    timezone_server
    timezone_system

In module templates: NOTE - not complete for auto template
* Set from Pi\Mvc\View\Http\InjectTemplateListener::injectTemplate()
    module          - Module name
    controller      - Controller name
    action          - Action name

In block templates:
* Set from Pi\View\Helper\Block::renderBlock()
    module          - Block's module
    route           - Matched route, available for call $route->getParam('module');

--------------------------------------------------------------------------------
To assign variables to theme templates from controller action:
    $this->view()->viewModel()->getRoot()->setVariables(array('var' => 'val'));

To assign variables to module templates from controller action:
    $this->view()->assign(array('var' => 'val'));



Taiwen Jiang
October 18th, 2012