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

use Zend\Navigation\Navigation as Container;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\Navigation as NavigationHelper;
use Zend\View\Helper\Navigation\AbstractHelper as AbstractNavigationHelper;
use Zend\Navigation\Page\Mvc as MvcPage;

/**
 * Helper for loading global navigation
 *
 * Usage inside a phtml template
 *
 * ```
 *  $this->nav()->render();
 *
 *  $nav = $this->nav('front');
 *  if ($nav) {
 *      $nav->render();
 *  }
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Nav extends Navigation
{
    /**
     * Load a navigation
     *
     * @param string    $name       navigation name
     * @param array     $options
     *      Render options: cache_ttl, cache_level, cache_id
     * @return  self|false
     */
    public function __invoke($name = null, $options = array())
    {
        if (0 == func_num_args()) {
            return $this;
        }

        $config = ('admin' == $name) ? 'nav_admin' : 'nav_front';
        $name = Pi::config($config, '');
        if (!$name) {
            return false;
        }

        parent::__invoke($name, $options);

        return $this;
    }
}
