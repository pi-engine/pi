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
 * Helper for loading CSS files
 *
 * Usage inside a phtml template
 *
 * ```
 *  // Load specific file
 *  $this->css('some.css');
 *
 *  // Load specific file with position
 *  $this->css('some.css', 'prepend');
 *
 *  // Load specific file with attributes
 *  $this->css('some.css',
 *             array('conditional' => '...', 'postion' => 'prepend'));
 *
 *  // Load a list of files
 *  $this->css(array(
 *      'a.css',
 *      'b.css',
 *  ));
 *
 *  // Load a list of files with corresponding attributes
 *  $this->css(array(
 *      'a.css' => array('media' => '...', 'conditional' => '...'),
 *      'b.css',
 *  ));
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Css extends AssetCanonize
{
    /**
     * Load CSS files
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
        $helper = $this->view->headLink();
        foreach ($files as $file => $attrs) {
            $position = isset($attrs['position'])
                ? $attrs['position'] : 'append';
            if ('prepend' == $position) {
                $helper->prependStylesheet($attrs);
            } else {
                $helper->appendStylesheet($attrs);
            }
        }

        return $this;
    }
}
