<?php
/**
 * Controller plugin Slug class
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
 * @package         Pi\Mvc
 * @version         $Id$
 */

namespace Pi\Mvc\Controller\Plugin;

use Pi;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Call slug helper methods
 * <code>
 *  $this->slug($slug);
 * </code>
 */
class Slug extends AbstractPlugin
{
	public $_search = array("&nbsp;","\t","\r\n","\r","\n",",",".","'",";",":",")",
	                        "(",'"','?','!','{','}','[',']','<','>','/','+','-','_',
	                        '\\','*','=','@','#','$','%','^','&');
   public $_replace = array(' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',
                            ' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',
                            ' ',' ',' ',' ',' ',' ');
	 
    /**
     * Invoke as a functor
     *
     * Get phrase and change it to slug
     *
     * @param  string $slug
     * @return string
     */
	public function __invoke($slug) 
	{
		$slug = strip_tags($slug);
		$slug = strtolower($slug);
		$slug = htmlentities($slug, ENT_COMPAT, 'utf-8');
		$slug = preg_replace('`\[.*\]`U', ' ', $slug);
		$slug = preg_replace('`&(amp;)?#?[a-z0-9]+;`i', ' ', $slug);
		$slug = preg_replace('`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig);`i', '\\1', $slug);
		$slug = str_replace($this->_search, $this->_replace, $slug);
		$slug = explode(' ',$slug);
      foreach($slug as $word) {
			if(!empty($word)) {
				$key[] = $word;
			}
		}
      $slug = implode('-',$key);
		return $slug;
	}
}