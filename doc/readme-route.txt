
Route names for all module routes except system routes are prepended with module, for instance Page module's route "page" should be called as "page-page".
<code>
    // From inside other modules' controllers or templates
    $uri = $this->url('page-page', ...);
    // From inside the page module itself, for controllers or templates, the '$module' is available automatically
    $uri = $this->url('.page', ...);
    $uri = $this->url($module . '-page', ...);
    // From inside the page module's block template, the '$module' is available automatically
    $uri = $this->url($module . '-page', ...);

    // For navigation config inside the page module
    $navigation = array(
        ...
            'contact'     => array(
                'label'         => __('Contact us'),
                'route'         => '.page',
                'action'        => 'contact',
            ),
        ...
    );
</code>


Taiwen Jiang
October 10th, 2012