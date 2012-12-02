<?php
/**
 * Pi Engine i18n sevice
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 * @subpackage      Service
 * @since           3.0
 * @version         $Id$
 */

/**
 * Internationalization service
 *
 * @link    http://www.php.net/manual/en/book.intl.php
 * @see     Pi\Application\Service\Asset for component disptach
 *
 *
 * Usage:
 * 1. Translator
 * <code>
 *  Pi::service('i18n')->setTranslator($translator);
 *  $translator = Pi::service('i18n')->getTranslator();
 *  $translator = Pi::service('i18n')->translator;
 * </code>
 *
 * 2. Locale
 * <code>
 *  Pi::service('i18n')->setLocale($locale);
 *  Pi::service('i18n')->getLocale();
 *  $locale = Pi::service('i18n')->locale;
 * </code>
 *
 * 3. Load a resource file
 * <code>
 *  /**#@+
 *   *  Use namespace pair and specified locale
 *   *\/
 *  // Specified module
 *  Pi::service('i18n')->load(array('module/demo', 'block'), 'en');
 *  // Global resource
 *  Pi::service('i18n')->load('date', 'en');
 *  Pi::service('i18n')->load(array('usr', 'mail/template'), 'en');
 *  // Specified theme
 *  Pi::service('i18n')->load(array('theme/default', 'main'), 'en');
 *  // Editor resource
 *  Pi::service('i18n')->load(array('usr/editor/myeditor', 'toolbar'), 'en');
 *  // Specified lib component resource
 *  Pi::service('i18n')->load(array('lib/Pi/Captcha/Image', 'captcha'), 'en');
 *  // Specified custom component resource
 *  Pi::service('i18n')->load(array('www/script/mycode/demo', 'main'), 'en');
 *  /**#@-*\/
 *  /**#@+
 *   *  Use string namespace with delimitor and specified locale
 *   *\/
 *  // Specified module
 *  Pi::service('i18n')->load('module/demo:block', 'en');
 *  // Global resource
 *  Pi::service('i18n')->load('usr:date', 'en');
 *  Pi::service('i18n')->load('mail/template', 'en');
 *  // Specified theme
 *  Pi::service('i18n')->load('theme/default:main', 'en');
 *  // Editor resource
 *  Pi::service('i18n')->load('usr/editor/myeditor:toolbar', 'en');
 *  // Specified lib component resource
 *  Pi::service('i18n')->load('lib/Pi/Captcha/Image:captcha', 'en');
 *  // Specified custom component resource
 *  Pi::service('i18n')->load('www/script/mycode/demo:main', 'en');
 *  /**#@-*\/
 *  /**#@+
 *   *  Use current locale
 *   *\/
 *  // Specified module
 *  Pi::service('i18n')->load(array('module/demo', 'block'));
 *  Pi::service('i18n')->load('module/demo:block');
 *  /**#@-*\/
 *  /**#@+
 *   *  Module translation
 *   *\/
 *  // Current module
 *  Pi::service('i18n')->loadModule('block');
 *  // Specified locale
 *  Pi::service('i18n')->loadModule('block', null, 'en');
 *  // Specified module
 *  Pi::service('i18n')->loadModule('block', 'demo');
 *  /**#@-*\/
 *  /**#@+
 *   *  Theme translation
 *   *\/
 *  // Current module
 *  Pi::service('i18n')->loadTheme('main');
 *  // Specified locale
 *  Pi::service('i18n')->loadTheme('main', null, 'en');
 *  // Specified theme
 *  Pi::service('i18n')->loadTheme('main', 'default');
 *  /**#@-*\/
 * </code>
 *
 * 4. Get a path
 * <code>
 *  $path = Pi::service('i18n')->getPath(array('usr', 'mail/template'), 'en');
 *  $path = Pi::service('i18n')->getPath('usr:mail/template', 'en');
 * </code>
 *
 * 5. Translate a message
 * <code>
 *  // From current text domain
 *  __('A test message');
 *  __('A test message', null, 'zh_CN');
 *
 *  // From a specified text domain
 *  Pi::service('i18n')->translator->setTextDomain('module/demo');
 *  __('A test message');
 *
 *  // From a specified text domain and restore previous domain after translation
 *  Pi::service('i18n')->translator->setTextDomain('module/demo');
 *  __('A test message');
 *  Pi::service('i18n')->translator->restoreTextDomain();
 *
 *  // From a specified text domain and locale and restore previous domain/locale after translation
 *  Pi::service('i18n')->translator->setTextDomain('module/demo');
 *  Pi::service('i18n')->translator->setLocale('zh_TW');
 *  __('A test message');
 *  Pi::service('i18n')->translator->restore();
 * </code>
 *
 * 6. Translate a message within a specified domain and locale
 * <code>
 *  __('A test message', 'theme/default', 'en');
 * </code>
 */

namespace Pi\Application\Service
{
    use Pi;
    use Pi\I18n\Translator\LoaderPluginManager;
    use Pi\I18n\Translator\Translator;

    class I18n extends AbstractService
    {
        //const NAMESPACE_GLOBAL = '_usr';
        const DOMAIN_GLOBAL = 'usr';
        const DOMAIN_MODULE = 'module';
        const DOMAIN_THEME = 'theme';
        const DIR_RESOURCE = 'locale';

        const FILE_DEFAULT = 'main';

        protected $fileIdentifier = 'i18n';

        /**
        * Locale
        * @var string
        */
        protected $__locale;
        /**
        * Translator
        */
        protected $__translator;

        /**
        * \Collator
        */
        protected $__collator;
        /*
        * \NumberFormatter
        */
        protected $__numberFormatter;
        /*
        * \MessageFormatter
        */
        protected $__messageFormatter;
        /*
        * \IntlDateFormatter
        */
        protected $__dateFormatter;
        /*
        * \Transliterator
        */
        protected $__transliterator;

        /**
         * Get translator, instantiate it if not available
         *
         * @return Translator
         */
        public function getTranslator()
        {
            if (!$this->__translator) {
                $translator = new Translator;
                if (!empty($this->options['translator']['pluginManager'])) {
                    $pluginManager = new $this->options['translator']['pluginManager'];
                } else {
                    $pluginManager = new LoaderPluginManager;
                }
                $translator->setPluginManager($pluginManager);
                $loader = $pluginManager->get($this->options['translator']['type']);
                $translator->setLoader($loader);
                if (!empty($this->options['translator']['options']) && is_callable(array($loader, 'setOptions'))) {
                    $loader->setOptions($this->options['translator']['options']);
                }
                $this->__translator = $translator;
            }
            return $this->__translator;
        }

        /**
         * Set translator with current locale
         *
         * @param Translator $translator
         * @return I18n
         */
        public function setTranslator(Translator $translator)
        {
            $this->__translator = $translator;
            $this->__translator->setLocale($this->locale);
            return $this;
        }

        /**
         * Set locale and configure Translator
         * @param string $locale
         * @return I18n
         */
        public function setLocale($locale)
        {
            $this->__locale = $locale;
            $this->translator->setLocale($locale);
            return $this;
        }

        /**
         * Get locale
         *
         * @return string
         */
        public function getLocale()
        {
            if (!$this->__locale) {
                $this->__locale = Pi::config('locale');
            }
            return $this->__locale;
        }

        /**
         * Magic method to get variables
         *
         * @param string $name
         * @return mixed
         */
        public function __get($name)
        {
            switch ($name) {
                case 'moduleDomain':
                    return static::DOMAIN_MODULE;
                //case 'golbalNamespace':
                //    return static::NAMESPACE_GLOBAL;
                case 'translator':
                    return $this->getTranslator();
                    break;
                case 'locale':
                    return $this->getLocale();
                    break;
                case 'collator':
                    if (!$this->__collator) {
                        $this->__collator = new \Collator($this->locale);
                    }
                    return $this->__collator;
                    break;
                /**#@+
                * @todo To implement the Intl extensions
                */
                case 'numberFormatter':
                    break;
                case 'messageFormatter':
                    break;
                case 'dateFormatter':
                    break;
                case 'transliterator':
                    break;
                default:
                break;
                /**#@-*/
            }
        }

        /**
        * Normalize domain in Intl resources, including Translator, Locale, Date, NumberFormatter, etc.
        *
        * @param string $domain
        * @return array pair of component and domain
        */
        public function normalizeDomain($rawDomain)
        {
            if (false !== strpos($rawDomain, ':')) {
                list($component, $domain) = explode(':', $rawDomain, 2);
            } else {
                $component = static::DOMAIN_GLOBAL;
                $domain = $rawDomain ?: static::FILE_DEFAULT;
            }
            return array($component, $domain);
        }

        /**
         * Load translation resource, existent data will be flushed
         *
         * @param array|string $domain
         * @param string|null $locale
         * @return Intl
         */
        public function load($domain, $locale = null)
        {
            $domain = is_array($domain) ? $domain : $this->normalizeDomain($domain);
            $locale = $locale ?: $this->locale;

            $this->translator->load($domain, $locale);

            if (Pi::service()->hasService('log')) {
                Pi::service()->getService('log')->info(sprintf('Translation "%s" is loaded', implode(':', $domain)));
            }

            return $this;
        }

        /**
         * Load a module resource
         *
         * @param string $domain
         * @param string $module
         * @param string $locale
         * @return Intl
         */
        public function loadModule($domain, $module = null, $locale = null)
        {
            $module = $module ?: Pi::service('module')->current();
            $component = array('module/' . $module, $domain);

            $this->load($component, $locale);
            return $this;
        }

        /**
         * Load a theme resource
         *
         * @param string $domain
         * @param string $theme
         * @param string $locale
         * @return Intl
         */
        public function loadTheme($domain, $theme = null, $locale = null)
        {
            $theme = $theme ?: Pi::service('theme')->current();
            $component = array('theme/' . $theme, $domain);
            $this->load($component, $locale);
            return $this;
        }

        /**
         * Get resource folder path
         *
         * @param array|string $domain
         * @param string $locale
         * @return string
         */
        public function getPath($domain = '', $locale = null)
        {
            if (is_array($domain)) {
                list($component, $normalizedDomain) = $domain;
            } else {
                list($component, $normalizedDomain) = $this->normalizeDomain($domain);
            }
            $locale = (null === $locale) ? $this->locale : $locale;
            $path = sprintf('%s/%s', Pi::path($component), static::DIR_RESOURCE);
            if ($locale) {
                $path .= '/' . $locale . ($normalizedDomain ? '/' . $normalizedDomain : '');
            }

            return $path;
        }

        /**
         * Clone a translator with specified domain and locale
         *
         * @param string $domain
         * @param string|null $locale
         * @return Translator
         */
        public function translator($domain = '', $locale = null)
        {
            $translator = clone $this->getTranslator();
            $translator->setTextDomain($domain);
            if ($locale) {
                $translator->setLocale($locale);
            }

            return $translator;
        }

        /**
         * Translate a message
         *
         * @param string    $message    The string to be localized
         * @param string    $domain     (optional) textdomain to use
         * @param string    $locale     (optional) Locale/Language to use
         * @return string
         */
        public function translate($message, $domain = null, $locale = null)
        {
            if (null !== $domain) {
                $domain = $this->normalizeDomain($domain);
            }

            return $this->getTranslator()->translate($message, $domain, $locale);
        }
    }
}

/**#@+
 * Syntactic sugar for system API
 */
namespace
{
    /**
     * Translate a message
     *
     * @param string    $message    The string to be localized
     * @param string    $domain     (optional) textdomain to use
     * @param string    $locale     (optional) Locale/Language to use
     * @return string
     */
    function __($message, $domain = null, $locale = null)
    {
        return Pi::service('i18n')->translator->translate($message, $domain, $locale);
    }

    /**
     * Translate and display a message
     *
     * @param string    $message    The string to be localized
     * @param string    $domain     (optional) textdomain to use
     * @param string    $locale     (optional) Locale/Language to use
     */
    function _e($message, $domain = null, $locale = null)
    {
        echo __($message, $domain, $locale);
    }
}
/**#@-*/
