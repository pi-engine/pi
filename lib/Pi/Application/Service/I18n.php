<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Service
 */

namespace Pi\Application\Service
{
    use Pi;
    use Pi\I18n\Translator\LoaderPluginManager;
    use Pi\I18n\Translator\Translator;

    /**
     * Internationalization Functions
     *
     * @see http://www.php.net/manual/en/book.intl.php
     */
    use IntlDateFormatter;
    use NumberFormatter;
    use Collator;

/**
 * I18n (Internationalization) service
 *
 * Usage:
 *
 * - Translator
 *
 * <code>
 *  Pi::service('i18n')->setTranslator(<translator>);
 *  $translator = Pi::service('i18n')->getTranslator();
 *  $translator = Pi::service('i18n')->translator;
 * </code>
 *
 * - Locale
 *
 * <code>
 *  Pi::service('i18n')->setLocale($locale);
 *  Pi::service('i18n')->getLocale();
 *  $locale = Pi::service('i18n')->locale;
 * </code>
 *
 * - Load a resource file
 *
 *   - Use namespace pair and specified locale
 *
 * <code>
 *  // Specified module
 *  Pi::service('i18n')->load(array('module/demo', 'block'), 'en')
 * ;
 *  // Global resource
 *  Pi::service('i18n')->load('date', 'en');
 *  Pi::service('i18n')->load(array('usr', 'mail/template'), 'en');
 *
 *  // Specified theme
 *  Pi::service('i18n')->load(array('theme/default', 'main'), 'en');
 *
 *  // Editor resource
 *  Pi::service('i18n')->load(array('usr/editor/myeditor', 'toolbar'), 'en');
 *
 *  // Specified lib component resource
 *  Pi::service('i18n')->load(array('lib/Pi/Captcha/Image', 'captcha'), 'en');
 *
 *  // Specified custom component resource
 *  Pi::service('i18n')->load(array('www/script/mycode/demo', 'main'), 'en');
 * </code>
 *
 *   - Use string namespace with delimitor and specified locale
 *
 * <code>
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
 * </code>
 *
 *   - Use current locale
 *
 * <code>
 *  // Specified module
 *  Pi::service('i18n')->load(array('module/demo', 'block'));
 *  Pi::service('i18n')->load('module/demo:block');
 * </code>
 *
 *   - Module translation
 *
 * <code>
 *  // Current module
 *  Pi::service('i18n')->loadModule('block');
 *  // Specified locale of current module
 *  Pi::service('i18n')->loadModule('block', null, 'en');
 *  // Specified module
 *  Pi::service('i18n')->loadModule('block', 'demo');
 * </code>
 *
 *   - Theme translation
 *
 * <code>
 *  // Current theme
 *  Pi::service('i18n')->loadTheme('main');
 *  // Specified locale of current theme
 *  Pi::service('i18n')->loadTheme('main', null, 'en');
 *  // Specified theme
 *  Pi::service('i18n')->loadTheme('main', 'default');
 * </code>
 *
 * - Get a path
 *
 * <code>
 *  $path = Pi::service('i18n')->getPath(array('usr', 'mail/template'), 'en');
 *  $path = Pi::service('i18n')->getPath('usr:mail/template', 'en');
 * </code>
 *
 * - Translate a message
 *
 *   - From current text domain
 *
 * <code>
 *  __('A test message');
 *  __('A test message', null, 'zh_CN');
 * </code>
 *
 *   - From a specified text domain
 *
 * <code>
 *  Pi::service('i18n')->translator->setTextDomain('module/demo');
 *  __('A test message');
 * </code>
 *
 *   - From a specified text domain and restore to previous domain
 *      after translation
 *
 * <code>
 *  Pi::service('i18n')->translator->setTextDomain('module/demo');
 *  __('A test message');
 *  Pi::service('i18n')->translator->restoreTextDomain();
 * </code>
 *
 *   - From a specified text domain and locale and restore
 *      to previous domain/locale after translation
 *
 * <code>
 *  Pi::service('i18n')->translator->setTextDomain('module/demo');
 *  Pi::service('i18n')->translator->setLocale('zh_TW');
 *  __('A test message');
 *  Pi::service('i18n')->translator->restore();
 * </code>
 *
 * - Translate a message within a specified domain and locale
 *
 * <code>
 *  __('A test message', 'theme/default', 'en');
 * </code>
 *
 *
 * - Register a message that will be translated in different lanauges,
 *      but not translated at the place where it is registered
 *
 * <code>
 *  _t('Message to be translated and used later.');
 * </code>
 *
 *  - Use case, register module config
 *
 *    - Registered in a module's config.php
 *
 * <code>
 *  $config['key'] = array(
 *      'title'         => _t('Config Title'),
 *      'description'   => _t('Config hint'),
 *      <...>
 *  );
 * </code>
 *
 *    - Load translated message in the module config setting page:
 *       `module/system/src/Controller/Admin/ConfigController.php`
 *      calls ConfigForm.php:
 *       `Module\System\Form\ConfigForm::addElement()`
 *
 * <code>
 *  protected function addElement($config)
 *  {
 *      // ...
 *      $attributes['description'] = __($config->description);
 *      $options = array(
 *              'label'     => __($config->title),
 *              'module'    => $this->module,
 *      );
 *      // ...
 *  }
 * </code>
 *
 *
 * - Format a date
 *
 * <code>
 *  _date(time(), 'fa-IR', 'long', 'short', 'Asia/Tehran', 'persian');
 *  _date(time(), array(
 *      'locale'    => 'fa-IR',
 *      'datetype'  => 'long',
 *      'timetype'  => 'short',
 *      'timezone'  => 'Asia/Tehran',
 *      'calendar'  => 'persian'
 *  ));
 *
 *  _date(time(), null, 'long', 'short', 'Asia/Tehran', 'persian');
 *  _date(time(), array('datetype' => 'long',
 *      'timetype'  => 'short',
 *      'timezone'  => 'Asia/Tehran',
 *      'calendar'  => 'persian'));
 *
 *  _date(time(), 'fa-IR@calendar=persian', 'long', 'short', 'Asia/Tehran');
 *  _date(time(), array('locale' => 'fa-IR@calendar=persian',
 *      'datetype'  => 'long',
 *      'timetype'  => 'short',
 *      'timezone'  => 'Asia/Tehran'));
 *
 *  _date(time(), null, null, null, null, 'persian');
 *  _date(time(), array('calendar' => 'persian'));
 *
 *  _date(time(), 'fa-IR', null, null, null, null, 'yyyy-MM-dd HH:mm:ss');
 *  _date(time(), array('locale' => 'fa-IR',
 *      'pattern' => 'yyyy-MM-dd HH:mm:ss'));
 *
 *  _date(time(), null, null, null, null, null, 'yyyy-MM-dd HH:mm:ss');
 *  _date(time(), array('pattern' => 'yyyy-MM-dd HH:mm:ss'));
 *
 *  _date(time());
 * </code>
 *
 *   - In case Intl not available,
 *      pass a format string for legacy date() function
 *
 * <code>
 *  _date(time(), 'fa-IR', null, null, null, null,
 *      'yyyy-MM-dd HH:mm:ss', 'Y-m-d H:i:s');
 *  _date(time(), array('locale' => 'fa-IR',
 *      'pattern' => 'yyyy-MM-dd HH:mm:ss', 'format' => 'Y-m-d H:i:s'));
 * </code>
 *
 *   - Format defined in system intl config
 *      (<pre>Pi::config('date_format', 'intl')</pre>)
 *      will be used if format is not specified
 *
 * <code>
 *  _date(time(), ...);
 * </code>
 *
 * - Format a number
 *
 * <code>
 *  _number(123.4567, 'decimal', '#0.# kg', 'zh-CN', 'default');
 *  _number(123.4567, 'decimal', '#0.# kg', 'zh-CN');
 *  _number(123.4567, 'scientific');
 *  _number(123.4567, 'spellout');
 * </code>
 *
 * - Format a currency
 *
 * <code>
 *  _currency(123.45, 'USD', 'en-US');
 *  _currency(123.45, 'USD');
 *  _currency(123.45);
 * </code>
 *
 * - Get a date formatter
 *
 * <code>
 *  Pi::service('i18n')->getDateFormatter('fa-IR', 'long', 'short',
 *      'Asia/Tehran', 'persian');
 *  Pi::service('i18n')->getDateFormatter(array('locale' => 'fa-IR',
 *      'datetype' => 'long', 'timetype' => 'short',
 *      'timezone' => 'Asia/Tehran', 'calendar' => 'persian'));
 *
 *  Pi::service('i18n')->getDateFormatter('fa-IR@calendar=persian',
 *      'long', 'short', 'Asia/Tehran');
 *  Pi::service('i18n')->getDateFormatter(array(
 *      'locale'    => 'fa-IR@calendar=persian',
 *      'datetype'  => 'long',
 *      'timetype'  => 'short',
 *      'timezone'  => 'Asia/Tehran'
 *  ));
 *
 *  Pi::service('i18n')->getDateFormatter(null, null, null, null, null,
 *      'yyyy-MM-dd HH:mm:ss');
 *  Pi::service('i18n')->getDateFormatter(array(
 *      'pattern' => 'yyyy-MM-dd HH:mm:ss'
 *  ));
 * </code>
 *
 * - Get a number formatter
 *
 *   - Get a number formatter
 *
 * <code>
 *  Pi::service('i18n')->getNumberFormatter('decimal', '#0.# kg', 'zh-CN');
 *  Pi::service('i18n')->getNumberFormatter('decimal', '', 'zh-CN');
 *  Pi::service('i18n')->getNumberFormatter('decimal');
 *  Pi::service('i18n')->getNumberFormatter('scientific');
 *  Pi::service('i18n')->getNumberFormatter('spellout');
 * </code>
 *
 *   - Get a currency formatter
 *
 * <code>
 *  Pi::service('i18n')->getNumberFormatter('currency', '', 'zh-CN');
 *  Pi::service('i18n')->getNumberFormatter('currency');
 * </code>
 *
 * @link    http://www.php.net/manual/en/book.intl.php
 * @see     Pi\Application\Service\Asset for component disptach
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
    class I18n extends AbstractService
    {
        /** @var string Global domain */
        const DOMAIN_GLOBAL = 'usr';

        /** @var string Module domain */
        const DOMAIN_MODULE = 'module';

        /** @var string Theme domain */
        const DOMAIN_THEME = 'theme';

        /** @var string Dir for i18n resource */
        const DIR_RESOURCE = 'locale';

        /** @var string Default file name */
        const FILE_DEFAULT = 'main';

        /** {@inheritDoc} */
        protected $fileIdentifier = 'i18n';

        /**
         * Locale
         *
         * @var string
         */
        protected $__locale = 'en';

        /**
         * Charset
         *
         * @var string
         */
        protected $__charset;

        /**
         * Translator
         *
         * @var Translator
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
                    $class = $this->options['translator']['pluginManager'];
                    $pluginManager = new $class;
                } else {
                    $pluginManager = new LoaderPluginManager;
                }
                $translator->setPluginManager($pluginManager);
                $loader =
                    $pluginManager->get($this->options['translator']['type']);
                $translator->setLoader($loader);
                if (!empty($this->options['translator']['options'])
                    && is_callable(array($loader, 'setOptions'))
                ) {
                    $loader->setOptions(
                        $this->options['translator']['options']
                    );
                }
                $this->__translator = $translator;
            }

            return $this->__translator;
        }

        /**
         * Set translator with current locale
         *
         * @param Translator $translator
         * @return $this
         */
        public function setTranslator(Translator $translator)
        {
            $this->__translator = $translator;
            $this->__translator->setLocale($this->getLocale());

            return $this;
        }

        /**
         * Set locale and configure Translator
         *
         * @param string $locale
         * @return $this
         */
        public function setLocale($locale)
        {
            $locale = $this->canonize($locale);
            if ($locale) {
                $this->__locale = $locale;
                $this->getTranslator()->setLocale($locale);
            }

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
         * Set charset
         *
         * @param string $charset
         * @return $this
         */
        public function setCharset($charset)
        {
            $this->__charset = $charset;

            return $this;
        }

        /**
         * Get charset
         *
         * @return string
         */
        public function getCharset()
        {
            if (!$this->__charset) {
                $this->__charset = Pi::config('charset') ?: 'utf-8';
            }

            return $this->__charset;
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
                case 'charset':
                    return $this->getCharset();
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
         * Normalize domain in Intl resources,
         * including Translator, Locale, Date, NumberFormatter, etc.
         *
         * @param string $rawDomain
         * @return string[] Pair of component and domain
         */
        public function normalizeDomain($rawDomain)
        {
            if (false !== strpos($rawDomain, ':')) {
                list($component, $domain) = explode(':', $rawDomain, 2);
            } else {
                $component = static::DOMAIN_GLOBAL;
                $domain = (null !== $rawDomain)
                    ? $rawDomain : static::FILE_DEFAULT;
            }

            return array($component, $domain);
        }

        /**
         * Load translation resource, existent data will be flushed
         *
         * @param array|string $domain
         * @param string|null $locale
         * @return $this
         */
        public function load($domain, $locale = null)
        {
            $domain = is_array($domain)
                ? $domain : $this->normalizeDomain($domain);
            $locale = $locale ?: $this->getLocale();
            $result = $this->getTranslator()->load($domain, $locale);

            if (Pi::service()->hasService('log')) {
                $message = $result
                    ? sprintf(
                        'Translation "%s.%s" is loaded.',
                        implode(':', $domain),
                        $locale
                      )
                    : sprintf(
                        'Translation "%s.%s" is empty.',
                        implode(':', $domain),
                        $locale
                      );
                Pi::service()->getService('log')->info($message);
            }

            return $this;
        }

        /**
         * Load a module resource
         *
         * @param string $domain
         * @param string $module
         * @param string $locale
         * @return $this
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
         * @return $this
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
         * @param array|string|null $domain
         * @param string $locale
         * @return string
         */
        public function getPath($domain = null, $locale = null)
        {
            if (is_array($domain)) {
                list($component, $normalizedDomain) = $domain;
            } else {
                list($component, $normalizedDomain) =
                    $this->normalizeDomain($domain);
            }
            $locale = (null === $locale) ? $this->getLocale() : $locale;
            $path = sprintf(
                '%s/%s',
                Pi::path($component),
                static::DIR_RESOURCE
            );
            if ($locale) {
                $path .= '/' . $locale
                       . ($normalizedDomain ? '/' . $normalizedDomain : '');
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

            return $this->getTranslator()->translate(
                $message,
                $domain,
                $locale
            );
        }

        /**
         * Load date formatter
         *
         * @see IntlDateFormatter
         *
         * @param array|string|null $locale
         * @param string|null $datetype
         *      Valid values: 'NULL', 'FULL', 'LONG', 'MEDIUM', 'SHORT'
         * @param string|null $timetype
         *      Valid values: 'NULL', 'FULL', 'LONG', 'MEDIUM', 'SHORT'
         * @param string|null $timezone
         * @param int|string|null $calendar
         * @param string|null $pattern
         *      Be aware that both datetype and timetype are ignored
         *      if the pattern is set.
         * @return IntlDateFormatter|null
         */
        public function getDateFormatter(
            $locale     = null,
            $datetype   = null,
            $timetype   = null,
            $timezone   = null,
            $calendar   = null,
            $pattern    = null
        ) {
            if (!class_exists('IntlDateFormatter')) {
                return null;
            }

            if (is_array($locale)) {
                $params = $locale;
                foreach (array(
                    'locale',
                    'datetype',
                    'timetype',
                    'timezone',
                    'calendar',
                    'pattern'
                ) as $key) {
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

            $datetype = constant(
                'IntlDateFormatter::'
                . strtoupper($datetype ?: Pi::config('date_datetype', 'intl'))
            );
            $timetype = constant(
                'IntlDateFormatter::'
                . strtoupper($timetype ?: Pi::config('date_timetype', 'intl'))
            );
            $timezone = $timezone ?: Pi::config('timezone');

            $formatter = new IntlDateFormatter(
                $locale,
                $datetype,
                $timetype,
                $timezone,
                $calendar
            );

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
         * @return NumberFormatter|null
         */
        public function getNumberFormatter(
            $style = null,
            $pattern = null,
            $locale = null
        ) {
            if (!class_exists('NumberFormatter')) {
                return null;
            }

            $locale = $locale ?: $this->getLocale();
            $style = $style ?: Pi::config('number_style', 'intl');
            $style = $style
                ? constant('NumberFormatter::' . strtoupper($style))
                : NumberFormatter::DEFAULT_STYLE;
            $formatter = new NumberFormatter($locale, $style);

            if ($pattern) {
                $formatter->setPattern($pattern);
            }

            return $formatter;
        }

        /**
         * Canonize locale name based on locales supported by Pi
         *
         * @param string $locale
         * @param bool $checkParent
         * @return string
         */
        public function canonize($locale, $checkParent = false)
        {
            $canonizedLocale = '';
            $locale = strtolower($locale);
            $localePath = $this->getPath('', $locale);
            $status = is_readable($localePath);
            if ($status) {
                $canonizedLocale = $locale;
            } elseif ($checkParent) {
                $pos = strpos($locale, '-');
                if (false !== $pos) {
                    $locale = substr($locale, 0, $pos);
                    $localePath = $this->getPath('', $locale);
                    $status = is_readable($localePath);
                    if ($status) {
                        $canonizedLocale = $locale;
                    }
                }
            }

            return $canonizedLocale;
        }

        /**
         * Auto detect client supported language(s)
         * from browser request header 'Accept-Language'
         *
         * @return string
         */
        public function getClient()
        {
            $accepted = '';
            $acceptedLanguage = isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])
                ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
            $matched = preg_match_all(
                '/([a-z]{2,8}(-[a-z]{2,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i',
                $acceptedLanguage,
                $matches
            );
            if ($matched) {
                foreach ($matches[1] as $language) {
                    $canonized = $this->canonize($language);
                    if ($canonized) {
                        $accepted = $canonized;
                        break;
                    }
                }
            }

            return $accepted;
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
        return Pi::service('i18n')->translator
            ->translate($message, $domain, $locale);
    }

    /**
     * Translate and display a message
     *
     * @param string    $message    The string to be localized
     * @param string    $domain     (optional) textdomain to use
     * @param string    $locale     (optional) Locale/Language to use
     * @return void
     */
    function _e($message, $domain = null, $locale = null)
    {
        echo __($message, $domain, $locale);
    }

    /**
     * Register a message to translation queue
     *
     * @param string    $message    The string to be localized
     * @return string
     */
    function _t($message)
    {
        return $message;
    }

    /**
     * Check if Intl functions are available
     *
     * @return bool
     */
    function _intl()
    {
        return extension_loaded('intl') ? true : false;
    }

    /**
     * Locale-dependent formatting/parsing of date-time
     * using pattern strings and/or canned patterns
     *
     * @param int|null          $value
     * @param array|string|null $locale
     * @param string|null       $datetype
     *      Valid values: 'NULL', 'FULL', 'LONG', 'MEDIUM', 'SHORT'
     * @param string|null       $timetype
     *      Valid values: 'NULL', 'FULL', 'LONG', 'MEDIUM', 'SHORT'
     * @param string|null       $timezone
     * @param int|string|null   $calendar
     * @param string|null       $pattern
     *      Be aware that both datetype and timetype are ignored
     *      if the pattern is set.
     * @param string|null       $format
     *      Legacy format for date() in case Intl is not available
     *
     * @return string
     */
    function _date(
        $value      = null,
        $locale     = null,
        $datetype   = null,
        $timetype   = null,
        $timezone   = null,
        $calendar   = null,
        $pattern    = null,
        $format     = null
    ) {
        $value = intval(null === $value ? time() : $value);
        // Formatted using date() in case Intl is not available
        if (!_intl()) {
            if (is_array($locale)) {
                $format = isset($locale['format'])
                    ? $locale['format'] : $format;
            }
            if (!$format) {
                $format = Pi::config('date_format', 'intl');
            }
            $result = date($format, $value);

            return $result;
        }

        $formatter = Pi::service('i18n')->getDateFormatter(
            $locale, $datetype,
            $timetype, $timezone, $calendar, $pattern
        );
        $result = $formatter->format($value);

        return $result;
    }

    /**
     * Locale-dependent formatting/parsing of number
     * using pattern strings and/or canned patterns
     *
     * @param int|float   $value
     * @param string|null $style
     * @param string|null $pattern
     * @param string|null $locale
     * @param string|null $type
     *
     * @return mixed
     */
    function _number(
        $value,
        $style      = null,
        $pattern    = null,
        $locale     = null,
        $type       = null
    ) {
        // Return raw data in case Intl is not available
        if (!_intl()) {
            return $value;
        }

        $formatter = Pi::service('i18n')->getNumberFormatter(
            $style,
            $pattern,
            $locale
        );
        if ($type) {
            $type = constant('NumberFormatter::TYPE_' . strtoupper($type));
            $result = $formatter->format($value, $type);
        } else {
            $result = $formatter->format($value);
        }

        return $result;
    }

    /**
     * Locale-dependent formatting/parsing of number
     * using pattern strings and/or canned patterns
     *
     * @param int|float $value
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
        $currency = (null === $currency)
            ? Pi::config('number_currency', 'intl') : $currency;
        if ($currency) {
            $style = 'CURRENCY';
            $formatter = Pi::service('i18n')->getNumberFormatter(
                $style,
                $locale
            );
            $result = $formatter->formatCurrency($value, $currency);
        }

        return $result;
    }
}
/**#@-*/
