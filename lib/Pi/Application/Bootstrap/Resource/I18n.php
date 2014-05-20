<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi;
use Locale;
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
        $locale = Pi::config('locale') ?: 'auto';
        $charset = Pi::config('charset') ?: 'utf-8';

        if ('auto' == $locale) {
            $locale = Pi::service('i18n')->getClient() ?: 'en';
            Pi::config()->set('locale', $locale);
        }

        // Set default locale and charset
        Pi::service('i18n')->setCharset($charset);
        Pi::service('i18n')->setLocale($locale);
        $locale = Pi::service('i18n')->getLocale();
        setlocale(LC_ALL, $locale);
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
                : array();
            if ($global) {
                foreach ($global as $domain) {
                    $translator->load($domain);
                    // Custom translations
                    //$translator->load('custom:' . $domain);
                }
            }
            // Register listener to load module translation
            if (!empty($this->options['translator']['module'])) {
                $this->application->getEventManager()->attach(
                    'dispatch',
                    array($this, 'loadTranslator')
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
        //$module = Pi::service('module')->current();
        foreach ((array) $this->options['translator']['module'] as $domain) {
            Pi::service('i18n')->loadModule($domain);
            //Pi::service('i18n')->load('custom/' . $module . ':' . $domain);
        }
    }
}
