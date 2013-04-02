<?php
/**
 * Alert helper
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
 * @author          Hossein Azizabadi <hossein.azizabadi@gmail.com>
 * @since           3.0
 * @package         Pi\View
 * @subpackage      Helper
 * @version         $Id$
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHtmlElement;

/**
 * Helper for loading Alert
 *
 * Usage inside a phtml template:
 * <code>
 *  $this->alert('title', 'body');
 *  $this->alert('title', 'body', 'class');
 * </code>
 */
class Alert extends AbstractHtmlElement
{
    /**
     * Make Button
     *
     * @param   string
     * @param   string
     * @param   string
     * @return  Alert
     */
    public function __invoke($title, $body, $class = null)
    {
		 
		 if(!in_array($class, array('alert-success','alert-error','alert-info'))) {
			  $class = '';	
		 }	

		 return '<div class="alert alert-block ' . $class . '">' . self::EOL
            . '<a class="close" data-dismiss="alert" href="#">×</a>' . self::EOL
            . '<h4 class="alert-heading">' . $title . '</h4>' . self::EOL
            .  $body . self::EOL
            . '</div>' . self::EOL
            . '<script type="text/javascript" >' . self::EOL
	         . '  $(".alert").alert();' . self::EOL
            . '</script>' . self::EOL;
    }
}