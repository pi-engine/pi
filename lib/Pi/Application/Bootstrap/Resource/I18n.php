<?php
/**
 * Bootstrap resource
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
 * @subpackage      Resource
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi;
use Locale;
use Zend\Mvc\MvcEvent;

class I18n extends AbstractResource
{
    /**
     * @return void
     */
    public function boot()
    {
        $this->engine->bootResource('config');

        // Load options for locale and charset
        $locale = !empty($this->options['locale']) ? $this->options['locale'] : Pi::config('locale');
        $charset = !empty($this->options['charset']) ? $this->options['charset'] : Pi::config('charset');
        $locale = $locale ?: 'auto';
        $charset = $charset ?: 'utf-8';

        if ('auto' == $locale) {
            $locale = Pi::service('i18n')->getClient() ?: Pi::config('locale');
        }

        // Set default locale
        $locale = Pi::service('i18n')->setLocale($locale);
        $result = setlocale(LC_ALL, $locale);

        // Preload translations
        if (!empty($this->options['translator'])) {
            $translator = Pi::service('i18n')->translator;
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
     */
    public function loadTranslator(MvcEvent $e)
    {
        foreach ((array) $this->options['translator']['module'] as $domain) {
            Pi::service('i18n')->loadModule($domain);
        }
    }
}
