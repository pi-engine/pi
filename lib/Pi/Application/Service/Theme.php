<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;

/**
 * Theme handling service
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Theme extends AbstractService
{
    /** @var string Default theme name */
    const DEFAULT_THEME = 'default';

    /** @var string Current theme name */
    protected $currentTheme;

    /**
     * Set current active theme
     *
     * @param string $theme
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
            $this->currentTheme = ('front' == Pi::engine()->section())
                    ? Pi::config('theme') : Pi::config('theme_admin');
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
    public function path($theme = '')
    {
        $theme = $theme ?: $this->current();
        $path = Pi::path('theme') . '/' . $theme;

        return $path;
    }

    /**
     * Get parent theme
     *
     * @param string $theme
     * @return string
     */
    public function getParent($theme = null)
    {
        $theme = $theme ?: $this->current();
        $config = $this->loadConfig($theme);
        $parent = !empty($config['parent'])
            ? $config['parent']
            : ($theme == static::DEFAULT_THEME ? '' : static::DEFAULT_THEME);

        return $parent;
    }

    /**
     * Get list of themes
     *
     * @param string $type
     *
     * @return array
     */
    public function getThemes($type = 'front')
    {
        $list = array();
        $themes = Pi::registry('theme')->read($type);
        foreach($themes as $name => $theme) {
            $list[$name] = $theme['title'];
        }

        return $list;
    }

    /**
     * Get layout list of a theme
     *
     * @param string $theme
     *
     * @return array
     */
    public function getLayouts($theme = '')
    {
        $list = array(
            'layout-front'      => __('Full layout (header/footer and blocks)'),
            'layout-simple'     => __('Simple layout (header/footer)'),
            'layout-style'      => __('Content only (with style)'),
            'layout-content'    => __('Raw content (no style)'),
        );
        if ($theme) {
            Pi::service('i18n')->loadTheme('default', $theme);
            $config = $this->loadConfig($theme);
            if (!empty($config['layout'])) {
                $list += $config['layout'];
            }
        }

        return $list;
    }
}
