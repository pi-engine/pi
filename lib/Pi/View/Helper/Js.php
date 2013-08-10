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
 * Helper for loading JavaScript files
 *
 * Usage inside a phtml template
 *
 * ```
 *  // Load specific file
 *  $this->js('some.js');
 *
 *  // Load specific file with position
 *  $this->js('some.js', 'prepend');
 *
 *  // Load specific file with attributes
 *  $this->js('some.js',
 *            array('conditional' => '...', 'postion' => 'prepend'));
 *
 *  // Load a list of files
 *  $this->js(array(
 *      'a.js',
 *      'b.js',
 *  ));
 *
 *  // Load a list of files with corresponding attributes
 *  $this->js(array(
 *      'a.js' => array('media' => '...', 'conditional' => '...'),
 *      'b.js',
 *  ));
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Js extends AssetCanonize
{
    /**
     * Load JavaScript file
     *
     * @param   string|array $files
     * @param   string|array $attributes
     *      Only applicable when $files is scalar,
     *      default as string for position,
     *      append or prepend, default as 'append'
     * @return  self
     */
    public function __invoke($files = null, $attributes = 'append')
    {
        $files = $this->canonize($files, $attributes);
        $helper = $this->view->headScript();
        foreach ($files as $file => $attrs) {
            $position = isset($attrs['position'])
                ? $attrs['position'] : 'append';
            if ('prepend' == $position) {
                $helper->prependFile($attrs['src'], 'text/javascript', $attrs);
            } else {
                $helper->appendFile($attrs['src'], 'text/javascript', $attrs);
            }
        }

        return $this;
    }
}
