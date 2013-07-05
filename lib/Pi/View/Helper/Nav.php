<?php
/**
 * Global navigation helper
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
 * @since           3.0
 * @package         Pi\View
 * @subpackage      Helper
 * @version         $Id$
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
 * Usage inside a phtml template:
 * <code>
 *  $this->nav()->render();
 *
 *  $nav = $this->nav('front');
 *  if ($nav) {
 *      $nav->render();
 *  }
 * </code>
 */
class Nav extends Navigation
{
    /**
     * Load a navigation
     *
     * @param string    $name       navigation name
     * @param array     $options    Render options: cache_ttl, cache_level, cache_id
     * @return  Nav
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
