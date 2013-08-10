<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Mvc\Service;

use Zend\View\HelperPluginManager;
use Zend\View\Helper;
use Zend\View\Exception;
use Zend\View\Renderer;

/**
 * View helper manager
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ViewHelperManager extends HelperPluginManager
{
    /**
     * Default set of helpers
     * @var array
     */
    protected $invokableClasses = array();

    /**
     * Default set of helpers
     * @var array
     */
    protected $invokableList = array(
        // View Helpers
        'doctype'               => 'View\Helper\Doctype',
        'basepath'              => 'View\Helper\BasePath',
        'url'                   => 'View\Helper\Url',
        'cycle'                 => 'View\Helper\Cycle',
        'declarevars'           => 'View\Helper\DeclareVars',
        'escapehtml'            => 'View\Helper\EscapeHtml',
        'escapehtmlattr'        => 'View\Helper\EscapeHtmlAttr',
        'escapejs'              => 'View\Helper\EscapeJs',
        'escapecss'             => 'View\Helper\EscapeCss',
        'escapeurl'             => 'View\Helper\EscapeUrl',
        'gravatar'              => 'View\Helper\Gravatar',
        'headlink'              => 'View\Helper\HeadLink',
        'headmeta'              => 'View\Helper\HeadMeta',
        'headscript'            => 'View\Helper\HeadScript',
        'headstyle'             => 'View\Helper\HeadStyle',
        'headtitle'             => 'View\Helper\HeadTitle',
        'htmlflash'             => 'View\Helper\HtmlFlash',
        'htmllist'              => 'View\Helper\HtmlList',
        'htmlobject'            => 'View\Helper\HtmlObject',
        'htmlpage'              => 'View\Helper\HtmlPage',
        'htmlquicktime'         => 'View\Helper\HtmlQuicktime',
        'inlinescript'          => 'View\Helper\InlineScript',
        'json'                  => 'View\Helper\Json',
        'layout'                => 'View\Helper\Layout',
        'paginationcontrol'     => 'View\Helper\PaginationControl',
        'partialloop'           => 'View\Helper\PartialLoop',
        'partial'               => 'View\Helper\Partial',
        'placeholder'           => 'View\Helper\Placeholder',
        'renderchildmodel'      => 'View\Helper\RenderChildModel',
        'rendertoplaceholder'   => 'View\Helper\RenderToPlaceholder',
        'serverurl'             => 'View\Helper\ServerUrl',
        'viewmodel'             => 'View\Helper\ViewModel',

        // Form helpers
        'form'                  => 'Form\View\Helper\Form',
        'formbutton'            => 'Form\View\Helper\FormButton',
        'formcaptcha'           => 'Form\View\Helper\FormCaptcha',
        'captchadumb'           => 'Form\View\Helper\Captcha\Dumb',
        'formcaptchadumb'       => 'Form\View\Helper\Captcha\Dumb',
        'captchafiglet'         => 'Form\View\Helper\Captcha\Figlet',
        'formcaptchafiglet'     => 'Form\View\Helper\Captcha\Figlet',
        'captchaimage'          => 'Form\View\Helper\Captcha\Image',
        'formcaptchaimage'      => 'Form\View\Helper\Captcha\Image',
        'captcharecaptcha'      => 'Form\View\Helper\Captcha\ReCaptcha',
        'formcaptcharecaptcha'  => 'Form\View\Helper\Captcha\ReCaptcha',
        'formcheckbox'          => 'Form\View\Helper\FormCheckbox',
        'formcollection'        => 'Form\View\Helper\FormCollection',
        'formcolor'             => 'Form\View\Helper\FormColor',
        'formdate'              => 'Form\View\Helper\FormDate',
        'formdatetime'          => 'Form\View\Helper\FormDateTime',
        'formdatetimelocal'     => 'Form\View\Helper\FormDateTimeLocal',
        'formelement'           => 'Form\View\Helper\FormElement',
        'formelementerrors'     => 'Form\View\Helper\FormElementErrors',
        'formemail'             => 'Form\View\Helper\FormEmail',
        'formfile'              => 'Form\View\Helper\FormFile',
        'formhidden'            => 'Form\View\Helper\FormHidden',
        'formimage'             => 'Form\View\Helper\FormImage',
        'forminput'             => 'Form\View\Helper\FormInput',
        'formlabel'             => 'Form\View\Helper\FormLabel',
        'formmonth'             => 'Form\View\Helper\FormMonth',
        'formmulticheckbox'     => 'Form\View\Helper\FormMultiCheckbox',
        'formnumber'            => 'Form\View\Helper\FormNumber',
        'formpassword'          => 'Form\View\Helper\FormPassword',
        'formradio'             => 'Form\View\Helper\FormRadio',
        'formrange'             => 'Form\View\Helper\FormRange',
        'formreset'             => 'Form\View\Helper\FormReset',
        'form_reset'            => 'Form\View\Helper\FormReset',
        'formrow'               => 'Form\View\Helper\FormRow',
        'formsearch'            => 'Form\View\Helper\FormSearch',
        'formselect'            => 'Form\View\Helper\FormSelect',
        'formsubmit'            => 'Form\View\Helper\FormSubmit',
        'formtel'               => 'Form\View\Helper\FormTel',
        'formtext'              => 'Form\View\Helper\FormText',
        'formtextarea'          => 'Form\View\Helper\FormTextarea',
        'formtime'              => 'Form\View\Helper\FormTime',
        'formurl'               => 'Form\View\Helper\FormUrl',
        'formweek'              => 'Form\View\Helper\FormWeek',

        'formdescription'       => 'Form\View\Helper\FormDescription',
        'formfieldset'          => 'Form\View\Helper\FormFieldset',
        'formeditor'            => 'Form\View\Helper\FormEditor',

        // i18n helpers
        'currencyformat'        => 'I18n\View\Helper\CurrencyFormat',
        'dateformat'            => 'I18n\View\Helper\DateFormat',
        'numberformat'          => 'I18n\View\Helper\NumberFormat',
        'translate'             => 'I18n\View\Helper\Translate',
        'translateplural'       => 'I18n\View\Helper\TranslatePlural',

        // Navigation
        'breadcrumbs'           => 'View\Helper\Navigation\Breadcrumbs',
        'links'                 => 'View\Helper\Navigation\Links',
        'menu'                  => 'View\Helper\Navigation\Menu',
        'sitemap'               => 'View\Helper\Navigation\Sitemap',
    );

    /**
     * Helper locations
     * @var string[]
     */
    protected $helperLocations = array(
        'View\Helper',
        'View\Helper\Navigation',
        'I18n\View\Helper',
        'Form\View\Helper',
    );

    /**
     * Retrieve a service from the manager by name
     *
     * Allows passing an array of options to use when creating the instance.
     * createFromInvokable() will use these and pass them to the instance
     * constructor if not null and a non-empty array.
     *
     * @param  string $name
     * @param  array $options
     * @param  bool $usePeeringServiceManagers
     * @return Helper\HelperInterface
     */
    public function get(
        $name,
        $options = array(),
        $usePeeringServiceManagers = true
    ) {
        // Canonize invokable class from name
        if (!$this->has($name) && !class_exists($name)) {
            // Lookup in default invokable list
            $cname = strtolower(
                str_replace(array('-', '_', ' ', '\\', '/'), '', $name)
            );
            if (isset($this->invokableList[$cname])) {
                $invokableClass = 'Pi\\' . $this->invokableList[$cname];
                if (!class_exists($invokableClass)) {
                    $invokableClass = 'Zend\\' . $this->invokableList[$cname];
                }
                $name = $invokableClass;
            // Lookup in helper locations
            } else {
                $class = str_replace(' ', '', ucwords(
                    str_replace(array('-', '_', '\\', '/'), ' ', $name)
                ));
                foreach ($this->helperLocations as $location) {
                    $invokableClass = 'Pi\\' . $location . '\\' . $class;
                    if (class_exists($invokableClass)) {
                        $name = $invokableClass;
                        break;
                    } else {
                        $invokableClass = 'Zend\\' . $location . '\\' . $class;
                        if (class_exists($invokableClass)) {
                            $name = $invokableClass;
                            break;
                        }
                    }
                }
            }
        }

        return parent::get($name, $options, $usePeeringServiceManagers);
    }

    /**
     * Skip translation for view helpers
     *
     * @param  Helper\HelperInterface $helper
     * @return void
     */
    public function injectTranslator($helper)
    {
        return;
    }
}
