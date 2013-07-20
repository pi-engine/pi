<?php
/**
 * Bootstrap resource
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi;
use Locale;
use Zend\Mvc\MvcEvent;

class I18n extends AbstractResource
{
    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        $this->engine->bootResource('config');

        // Load options for locale and charset
        //$locale = !empty($this->options['locale']) ? $this->options['locale'] : Pi::config('locale');
        //$charset = !empty($this->options['charset']) ? $this->options['charset'] : Pi::config('charset');
        $locale = Pi::config('locale') ?: 'auto';
        $charset = Pi::config('charset') ?: 'utf-8';

        if ('auto' == $locale) {
            $locale = Pi::service('i18n')->getClient() ?: Pi::config('locale');
        }

        // Set default locale and charset
        Pi::service('i18n')->setCharset($charset);
        Pi::service('i18n')->setLocale($locale);
        $locale = Pi::service('i18n')->getLocale();
        setlocale(LC_ALL, $locale);

        // Preload translations
        if (!empty($this->options['translator'])) {
            $translator = Pi::service('i18n')->getTranslator();
            if (!empty($this->options['translator']['global'])) {
                foreach ((array) $this->options['translator']['global'] as $domain) {
                    $translator->load($domain);
                }
            }
            // Register listener to load module translation
            if (!empty($this->options['translator']['module'])) {
                $this->application->getEventManager()->attach('dispatch', array($this, 'loadTranslator'));
            }
        }
    }

    /**
     * Load module translation after module is dispatched
     *
     * @param MvcEvent $e
     * @return void
     */
    public function loadTranslator(MvcEvent $e)
    {
        foreach ((array) $this->options['translator']['module'] as $domain) {
            Pi::service('i18n')->loadModule($domain);
        }
    }
}
