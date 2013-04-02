<?php
/**
 * Pager helper
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
 * Helper for loading Pager
 *
 * Usage inside a phtml template:
 * <code>
 *  $this->pager(array, false);
 * </code>
 */
class Pager extends AbstractHtmlElement
{
    /**
     * Make Button
     *
     * @param   string
     * @param   string
     * @param   string
     * @return  Button
     */
    public function __invoke($link, $align = false)
    {
		 
		 if($align) {
			  $larr = '&larr;';
			  $rarr = '&rarr;';
		 } else {
			  $larr = '';
			  $rarr = '';
		 }		
		 
		 if(!empty($link['previous'])) {
			  $previous = '<a title="'. __('Older') .'" href="' . $link['previous'] . '">' . $larr . ' ' . __('Older') . '</a>';
			  $previous = '<li class="previous">' . $previous . '</li>' . self::EOL;
		 } else {
		 	  $previous = '';
		 }
		 	
		 if(!empty($link['next'])) {
			  $next = '<a title="'. __('Newer') .'" href="' . $link['next'] . '">' . __('Newer') . ' ' . $rarr . '</a>';
			  $next = '<li class="next">' . $next . '</li>' . self::EOL;
		 } else {
		 	  $next = ''; 
		 }
		 
		 return '<ul class="pager">' . self::EOL . $previous . $next . '</ul>';
    }
}