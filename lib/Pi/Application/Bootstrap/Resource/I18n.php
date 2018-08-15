<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi;
use Zend\Mvc\MvcEvent;
use Zend\Validator\AbstractValidator;

/**
 * I18n bootstrap
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class I18n extends AbstractResource
{
    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        $this->engine->bootResource('config');

        // Load options for locale and charset
        $locale  = Pi::config('locale') ?: 'auto';
        $charset = Pi::config('charset') ?: 'utf-8';

        if ('auto' == $locale) {
            $locale = Pi::service('i18n')->getClient() ?: 'en';
            Pi::config()->set('locale', $locale);
        }

        // Set default locale and charset
        Pi::service('i18n')->setCharset($charset);
        Pi::service('i18n')->setLocale($locale);
        $locale = Pi::service('i18n')->getLocale();

        /**
         * Get real locale code (iso) for current language
         * Language code is needed for translation
         * Locale code (iso) is needed for PHP time functions - use of setlocale()
         */
        $isoLocale = $this->convertLanguageToLocale($locale);
        setlocale(LC_ALL, $isoLocale);

        // Set encoding for multi-byte handling
        mb_internal_encoding($charset);
        // Set `default_charset` for filters like `FILTER_SANITIZE_FULL_SPECIAL_CHARS`
        @ini_set('default_charset', $charset);

        // Preload translations
        if (!empty($this->options['translator'])) {
            $translator = Pi::service('i18n')->getTranslator();

            // Load global translations
            $global = !empty($this->options['translator']['global'])
                ? $this->options['translator']['global']
                : [];
            if ($global) {
                foreach ($global as $domain) {
                    $translator->load($domain);
                }
            }
            // Register listener to load module translation
            if (!empty($this->options['translator']['module'])) {
                $this->application->getEventManager()->attach(
                    'dispatch',
                    [$this, 'loadTranslator']
                );
            }
        }

        // Set default translator for validators
        AbstractValidator::setDefaultTranslator(
            Pi::service('i18n')->getTranslator(),
            'validator'
        );
    }

    /**
     * Load module translation after module is dispatched
     *
     * @param MvcEvent $e
     * @return void
     */
    public function loadTranslator(MvcEvent $e)
    {
        foreach ((array)$this->options['translator']['module'] as $domain) {
            Pi::service('i18n')->loadModule($domain);
        }
    }

    /**
     * Convert language to locale (iso)
     * @todo need to be completed later
     * @param $language
     * @return mixed
     */
    protected function convertLanguageToLocale($language){

        $mapping = array(
            'en' => 'en_UK',
            'fr' => 'fr_FR',
        );

        return empty($mapping[$language]) ? $language : $mapping[$language];
    }
}
