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
 *
 * 7. Format a date
 * <code>
 *  _date(time(), 'fa-IR', 'long', 'short', 'Asia/Tehran', 'persian');
 *  _date(time(), array('locale' => 'fa-IR', 'datetype' => 'long', 'timetype' => 'short', 'timezone' => 'Asia/Tehran', 'calendar' => 'persian'));
 *
 *  _date(time(), null, 'long', 'short', 'Asia/Tehran', 'persian');
 *  _date(time(), array('datetype' => 'long', 'timetype' => 'short', 'timezone' => 'Asia/Tehran', 'calendar' => 'persian'));
 *
 *  _date(time(), 'fa-IR@calendar=persian', 'long', 'short', 'Asia/Tehran');
 *  _date(time(), array('locale' => 'fa-IR@calendar=persian', 'datetype' => 'long', 'timetype' => 'short', 'timezone' => 'Asia/Tehran'));
 *
 *  _date(time(), null, null, null, null, 'persian');
 *  _date(time(), array('calendar' => 'persian'));
 *
 *  _date(time(), 'fa-IR', null, null, null, null, 'yyyy-MM-dd HH:mm:ss');
 *  _date(time(), array('locale' => 'fa-IR', 'pattern' => 'yyyy-MM-dd HH:mm:ss'));
 *
 *  _date(time(), null, null, null, null, null, 'yyyy-MM-dd HH:mm:ss');
 *  _date(time(), array('pattern' => 'yyyy-MM-dd HH:mm:ss'));
 *
 *  _date(time());
 *
 *  // In case Intl is not available, pass a format string for legacy date() function
 *  _date(time(), 'fa-IR', null, null, null, null, 'yyyy-MM-dd HH:mm:ss', 'Y-m-d H:i:s');
 *  _date(time(), array('locale' => 'fa-IR', 'pattern' => 'yyyy-MM-dd HH:mm:ss', 'format' => 'Y-m-d H:i:s'));
 *  // Format defined in system intl config (Pi::config('date_format', 'intl')) will be used if format is not specified
 *  _date(time(), ...);
 * </code>
 *
 * 8. Format a number
 * <code>
 *  _number(123.4567, 'decimal', '#0.# kg', 'zh-CN', 'default');
 *  _number(123.4567, 'decimal', '#0.# kg', 'zh-CN');
 *  _number(123.4567, 'scientific');
 *  _number(123.4567, 'spellout');
 * </code>
 *
 * 9. Format a currency
 * <code>
 *  _currency(123.45, 'USD', 'en-US');
 *  _currency(123.45, 'USD');
 *  _currency(123.45);
 * </code>
 *
 * 10. Get a date formatter
 * <code>
 *  Pi::service('i18n')->getDateFormatter('fa-IR', 'long', 'short', 'Asia/Tehran', 'persian');
 *  Pi::service('i18n')->getDateFormatter(array('locale' => 'fa-IR', 'datetype' => 'long', 'timetype' => 'short', 'timezone' => 'Asia/Tehran', 'calendar' => 'persian'));
 *
 *  Pi::service('i18n')->getDateFormatter('fa-IR@calendar=persian', 'long', 'short', 'Asia/Tehran');
 *  Pi::service('i18n')->getDateFormatter(array('locale' => 'fa-IR@calendar=persian', 'datetype' => 'long', 'timetype' => 'short', 'timezone' => 'Asia/Tehran'));
 *
 *  Pi::service('i18n')->getDateFormatter(null, null, null, null, null, 'yyyy-MM-dd HH:mm:ss');
 *  Pi::service('i18n')->getDateFormatter(array('pattern' => 'yyyy-MM-dd HH:mm:ss'));
 * </code>
 *
 * 11. Get a number formatter
 * <code>
 *  // Get a number formatter
 *  Pi::service('i18n')->getNumberFormatter('decimal', '#0.# kg', 'zh-CN');
 *  Pi::service('i18n')->getNumberFormatter('decimal', '', 'zh-CN');
 *  Pi::service('i18n')->getNumberFormatter('decimal');
 *  Pi::service('i18n')->getNumberFormatter('scientific');
 *  Pi::service('i18n')->getNumberFormatter('spellout');
 *  // Get a currency formatter
 *  Pi::service('i18n')->getNumberFormatter('currency', '', 'zh-CN');
 *  Pi::service('i18n')->getNumberFormatter('currency');
 * </code>
 */

namespace Pi\Application\Service
{
    use Pi;
    use Pi\I18n\Translator\LoaderPluginManager;
    use Pi\I18n\Translator\Translator;

    /**#@+
     * Internationalization Functions
     * @see http://www.php.net/manual/en/book.intl.php
     */
    use IntlDateFormatter;
    use NumberFormatter;
    use Collator;
    /**#@-*/

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
                case 'numberFormatter':
                    return $this->getNumberFormatter();
                    break;
                case 'dateFormatter':
                    return $this->getDateFormatter();
                    break;
                /**#@+
                * @todo To implement the Intl extensions
                */
                case 'collator':
                    break;
                case 'messageFormatter':
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

        /**
         * Load date formatter
         *
         * @see IntlDateFormatter
         *
         * @param array|string|null $locale
         * @param string|null $datetype     Valid values: 'NULL', 'FULL', 'LONG', 'MEDIUM', 'SHORT'
         * @param string|null $timetype     Valid values: 'NULL', 'FULL', 'LONG', 'MEDIUM', 'SHORT'
         * @param string|null $timezone
         * @param int|string|null $calendar
         * @param string|null $pattern      Be aware that both datetype and timetype are ignored if the pattern is set.
         * @return IntlDateFormatter
         */
        public function getDateFormatter($locale = null, $datetype = null, $timetype = null, $timezone = null, $calendar = null, $pattern = null)
        {
            if (!class_exists('IntlDateFormatter')) {
                return null;
            }

            if (is_array($locale)) {
                $params = $locale;
                foreach (array('locale', 'datetype', 'timetype', 'timezone', 'calendar', 'pattern') as $key) {
                    ${$key} = isset($params[$key]) ? $params[$key] : null;
                }
            }

            if (!$locale) {
                $locale = $this->getLocale();
            } elseif (strpos($locale, '@')) {
                $calendar = IntlDateFormatter::TRADITIONAL;
            }

            if (null === $calendar) {
                $calendar = Pi::config('date_calendar', 'intl');
                if (!$calendar) {
                    $calendar = IntlDateFormatter::GREGORIAN;
                }
            }
            if ($calendar && !is_numeric($calendar)) {
                $locale .= '@calendar=' . $calendar;
                $calendar = IntlDateFormatter::TRADITIONAL;
            }
            if (null === $calendar) {
                $calendar = IntlDateFormatter::GREGORIAN;
            }

            $datetype = constant('IntlDateFormatter::' . strtoupper($datetype ?: Pi::config('date_datetype', 'intl')));
            $timetype = constant('IntlDateFormatter::' . strtoupper($timetype ?: Pi::config('date_timetype', 'intl')));
            $timezone = $timezone ?: Pi::config('timezone');

            $formatter = new IntlDateFormatter($locale, $datetype, $timetype, $timezone, $calendar);

            if ($pattern) {
                $formatter->setPattern($pattern);
            }

            return $formatter;
        }

        /**
         * Load number formatter
         *
         * @see NumberFormatter
         *
         * @param string|null $style
         * @param string|null $pattern
         * @param string|null $locale
         * @return NumberFormatter
         */
        public function getNumberFormatter($style = null, $pattern = null, $locale = null)
        {
            if (!class_exists('NumberFormatter')) {
                return null;
            }

            $locale = $locale ?: $this->getLocale();
            $style = $style ?: Pi::config('number_style', 'intl');
            $style = $style ? constant('NumberFormatter::' . strtoupper($style)) : NumberFormatter::DEFAULT_STYLE;
            $formatter = new NumberFormatter($locale, $style);

            if ($pattern) {
                $formatter->setPattern($pattern);
            }

            return $formatter;
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

    /**
     * Check if Intl functions are available
     */
    function _intl()
    {
        return extension_loaded('intl') ? true : false;
    }

    /**
     * Locale-dependent formatting/parsing of date-time using pattern strings and/or canned patterns
     *
         * @param array|string|null $locale
         * @param string|null $datetype     Valid values: 'NULL', 'FULL', 'LONG', 'MEDIUM', 'SHORT'
         * @param string|null $timetype     Valid values: 'NULL', 'FULL', 'LONG', 'MEDIUM', 'SHORT'
         * @param string|null $timezone
         * @param int|string|null $calendar
         * @param string|null $pattern      Be aware that both datetype and timetype are ignored if the pattern is set.
     * @param string|null $format           Legacy format for date() in case Intl is not available
     * @return string
     */
    function _date($value = null, $locale = null, $datetype = null, $timetype = null, $timezone = null, $calendar = null, $pattern = null, $format = null)
    {
        $value = intval(null === $value ? time() : $value);
        // Formatted using date() in case Intl is not available
        if (!_intl()) {
            if (is_array($locale)) {
                $format = isset($locale['format']) ? $locale['format'] : $format;
            }
            if (!$format) {
                $format = Pi::config('date_format', 'intl');
            }
            $result = date($format, $value);

            return $result;
        }

        $formatter = Pi::service('i18n')->getDateFormatter($locale, $datetype, $timetype, $timezone, $calendar, $pattern);
        $result = $formatter->format($value);

        return $result;
    }

    /**
     * Locale-dependent formatting/parsing of number using pattern strings and/or canned patterns
     *
     * @param string|null $style
     * @param string|null $pattern
     * @param string|null $locale
     * @param string|null $type
     * @return mixed
     */
    function _number($value, $style = null, $pattern = null, $locale = null, $type = null)
    {
        // Return raw data in case Intl is not available
        if (!_intl()) {
            return $value;
        }

        $formatter = Pi::service('i18n')->getNumberFormatter($style, $pattern, $locale);
        if ($type) {
            $type = constant('NumberFormatter::TYPE_' . strtoupper($type));
            $result = $formatter->format($value, $type);
        } else {
            $result = $formatter->format($value);
        }

        return $result;
    }

    /**
     * Locale-dependent formatting/parsing of number using pattern strings and/or canned patterns
     *
     * @param string|null $currency
     * @param string|null $locale
     * @return string
     */
    function _currency($value, $currency = null, $locale = null)
    {
        if (!_intl()) {
            return false;
        }
        $result = $value;
        $currency = (null === $currency) ? Pi::config('number_currency', 'intl') : $currency;
        if ($currency) {
            $style = 'CURRENCY';
            $formatter = Pi::service('i18n')->getNumberFormatter($style, $locale);
            $result = $formatter->formatCurrency($value, $currency);
        }

        return $result;
    }
}
/**#@-*/