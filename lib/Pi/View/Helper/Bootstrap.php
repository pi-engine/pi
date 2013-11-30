<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;

/**
 * Helper for loading Bootstrap files
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
     * @param   null|string|array $files
     * @param   array $attributes
     * @param   bool|null $appendVersion
     *
     * @return  $this
     */
    public function __invoke(
        $files = null,
        $attributes = array(),
        $appendVersion = null
    ) {
        $files = $this->canonize($files, $attributes);

        $bootstrap = 'css/bootstrap.min.css';

        if (!static::$rootLoaded) {
            $files = array(
                $bootstrap  => $this->canonizeFile($bootstrap),
            ) + $files;
            static::$rootLoaded = true;
        } else {
            if (isset($files[$bootstrap])) {
                unset($files[$bootstrap]);
            }
        }

        foreach ($files as $file => $attrs) {
            $file = static::DIR_ROOT . '/' . $file;
            $url = Pi::service('asset')->getStaticUrl($file, $appendVersion);
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
                $this->view->jQuery();
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
