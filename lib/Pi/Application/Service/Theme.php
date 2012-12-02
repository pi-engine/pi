<?php
/**
 * Pi Engine theme service
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

namespace Pi\Application\Service;
use Pi;

class Theme extends AbstractService
{
    const DEFAULT_THEME = 'default';
    protected $currentTheme;

    /**
     * Set current active theme
     *
     * @param sring $theme
     * @return Theme
     */
    public function setTheme($theme)
    {
        $this->currentTheme = $theme;
        return $this;
    }

    /**
     * Get current active theme
     *
     * @return string
     */
    public function current()
    {
        if (!$this->currentTheme) {
            $this->currentTheme = ('front' == Pi::engine()->section()) ? Pi::config('theme') : Pi::config('theme_admin');
            $this->currentTheme = $this->currentTheme ?: 'default';
        }
        return $this->currentTheme;
    }

    /**
     * Load theme configuration from file
     *
     * @param string $theme
     * @return array
     */
    public function loadConfig($theme)
    {
        //Pi::service('i18n')->translator->load(sprintf('theme/%s:meta', $theme));
        $configFile = sprintf('%s/config.php', $this->path($theme));
        if (file_exists($configFile)) {
            $config = include $configFile;
        } else {
            $config = array();
        }
        return $config;
    }

    /**
     * Get path to theme location
     *
     * @param string $theme
     * @return string
     */
    public function path($theme)
    {
        $path = Pi::path('theme') . '/' . $theme;
        return $path;
    }
}
