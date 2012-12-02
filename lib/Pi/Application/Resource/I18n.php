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

namespace Pi\Application\Resource;

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
        $this->engine->loadResource('config');

        // Loads charset from system config
        $locale = Pi::config('locale');
        // Loads charset from system config
        $charset = Pi::config('charset');

        // Load from options if not set in system config
        $locale = $locale ?: (isset($this->options['locale']) ? $this->options['locale'] : null);
        $charset = $charset ?: (isset($this->options['charset']) ? $this->options['charset'] : null);

        Pi::service('i18n')->setLocale($locale);
        //$locale = new Locale($locale, $charset);
        setlocale(LC_ALL, $locale);
        //Pi::registry('locale', $locale);

        if (!empty($this->options['translator'])) {
            $translator = Pi::service('i18n')->translator;
            if (!empty($this->options['translator']['global'])) {
                foreach ($this->options['translator']['global'] as $domain) {
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
        foreach ($this->options['translator']['module'] as $domain) {
            Pi::service('i18n')->loadModule($domain);
        }
    }
}
