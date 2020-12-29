<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;

/**
 * Helper for loading Bootstrap files
 *
 * Theme specific bootstrap customization is supported with file skeleton
 * `usr/theme/<theme-name>/asset/vendor/bootstrap/css/bootstrap.min.css`
 *
 *
 * Usage inside a phtml template
 *
 * ```
 *  // Load basic bootstrap css
 *  $this->bootstrap();
 *
 *  // Load specific file
 *  $this->bootstrap('some.css');
 *
 *  // Load specific file with attributes
 *  $this->bootstrap('some.js',
 *                   array('conditional' => '...', 'position' => 'prepend'));
 *
 *  // Load a list of files
 *  $this->bootstrap(array(
 *      'some.css',
 *      'some.js',
 *  ));
 *
 *  // Load a list of files with corresponding attributes
 *  $this->bootstrap(array(
 *      'some.css' => array('media' => '...', 'conditional' => '...'),
 *      'some.js',
 *  ));
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Bootstrap extends AssetCanonize
{
    /** @var string Root dir of Bootstrap */
    const DIR_ROOT = 'vendor/bootstrap';

    /** @var bool Bootstrap basic file is loaded */
    protected static $rootLoaded;

    /**
     * Load bootstrap files
     *
     * @param null|string|array $files
     * @param array             $attributes
     * @param bool|null         $appendVersion
     *
     * @return  $this
     */
    public function __invoke(
        $files = null,
        $attributes = [],
        $appendVersion = null,
        $rootLoaded = true,
        $deferBootstrapCss = false
    ) {
        $files = $this->canonize($files, $attributes);

        $bootstrap = 'css/bootstrap.css';

        if (!static::$rootLoaded && $rootLoaded) {
            $file = static::DIR_ROOT . '/' . $bootstrap;

            // Lookup in theme custom bootstrap
            $theme  = Pi::service('theme')->current();
            $custom = Pi::service('asset')->getAssetPath('theme/' . $theme, $file);
            if (is_readable($custom)) {
                $url = Pi::service('asset')->getThemeAsset($file, $theme, $appendVersion);
            // Load original bootstrap
            } else {
                $url = Pi::service('asset')->getPublicUrl($file, $appendVersion);
            }
            $attrs         = $this->canonizeFile($bootstrap);
            $attrs['href'] = $url;

            if ($deferBootstrapCss) {
                $attrs['defer'] = 'defer';
            }

            $position = isset($attrs['position']) ? $attrs['position'] : 'append';
            if ('prepend' == $position) {
                $this->view->headLink()->prependStylesheet($attrs);
            } else {
                $this->view->headLink()->appendStylesheet($attrs);
            }
            static::$rootLoaded = true;
        }
        if (isset($files[$bootstrap])) {
            unset($files[$bootstrap]);
        }

        foreach ($files as $file => $attrs) {
            $file     = static::DIR_ROOT . '/' . $file;
            $url      = Pi::service('asset')->getPublicUrl($file, $appendVersion);
            $position = isset($attrs['position'])
                ? $attrs['position'] : 'append';
            if ('css' == $attrs['ext']) {
                $attrs['href'] = $url;
                if ('prepend' == $position) {
                    $this->view->headLink()->prependStylesheet($attrs);
                } else {
                    $this->view->headLink()->appendStylesheet($attrs);
                }
            } else {
                if ('prepend' == $position) {
                    $this->view->headScript()
                        ->prependFile($url, 'text/javascript', $attrs);
                } else {
                    $this->view->headScript()
                        ->appendFile($url, 'text/javascript', $attrs);
                }
            }
        }

        return $this;
    }
}
