<?php
/**
 * facebook helper
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
 * @author          Hossein Azizabadi <azizabadi@faragostaresh.com>
 * @since           3.0
 * @package         Pi\View
 * @subpackage      Helper
 * @version         $Id$
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHtmlElement;

/**
 * Helper for loading facebook
 *
 * Usage inside a phtml template:
 * <code>
 *  $this->facebook();
 * </code>
 */
class Facebook extends AbstractHtmlElement
{ 
    /**
     * Make Plusone
     *
     * @param   array
     * @return  Button
     */
    public function __invoke()
    {
         $facebook = '<div id="fb-root"></div>' . self::EOL
						. '<script type="text/javascript">' . self::EOL
						. '(function(d, s, id) {' . self::EOL
						. '   var js, fjs = d.getElementsByTagName(s)[0];' . self::EOL
						. '   if (d.getElementById(id)) return;' . self::EOL
						. '   js = d.createElement(s); js.id = id;' . self::EOL
						. '   js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";' . self::EOL
						. '   fjs.parentNode.insertBefore(js, fjs);' . self::EOL
						. '   }(document, "script", "facebook-jssdk"));' . self::EOL
						. '</script>' . self::EOL
						. '<div class="fb-like" data-send="false" data-layout="button_count" data-width="120" data-show-faces="false"></div>' . self::EOL;
        return $facebook;
    }	
} 
