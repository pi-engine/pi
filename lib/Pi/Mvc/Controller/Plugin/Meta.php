<?php
/**
 * Controller plugin Meta class
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

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Call keywords helper methods
 * <code>
 * $this->meta()->keywords($keywords);
 * $this->meta()->keywords($keywords, $number, $limit);
 * </code>
 *
 * Call keywords helper methods
 * <code>
 * $this->meta()->description($description);
 * </code>
 */
class Meta extends AbstractPlugin
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
     * Make meta keywords from phrase
     *
     * @param  string $keywords
     * @param  number
     * @param  number
     * @return string
     */
	public function keywords($keywords, $number = '6', $limit = '3') 
	{
		$keywords = strip_tags($keywords);
		$keywords = strtolower($keywords);
		$keywords = htmlentities($keywords, ENT_COMPAT, 'utf-8');
		$keywords = preg_replace('`\[.*\]`U', '', $keywords);
		$keywords = preg_replace('`&(amp;)?#?[a-z0-9]+;`i', '', $keywords);
		$keywords = preg_replace('`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig);`i', '\\1', $keywords);
		$keywords = str_replace($this->_search, $this->_replace, $keywords);
		$keywords = explode(' ',$keywords);
		$keywords = array_unique($keywords);
      foreach($keywords as $keyword) {
			if(mb_strlen($keyword) >= $limit && !empty($keyword) && !is_numeric($keyword)) {
				$key[] = $keyword;
			}
		}
		$key = array_slice($key, 0, $number);
      $keywords = implode(',',$key);
      $keywords = trim($keywords, ',');
		return $keywords;
	}	
	 
    /**
     * Invoke as a functor
     *
     * Make meta description from phrase
     *
     * @param  string $description
     * @return string
     */
	public function description($description) 
	{
		$description = strip_tags($description);
		$description = strtolower($description);
		$description = htmlentities($description, ENT_COMPAT, 'utf-8');
		$description = preg_replace('`\[.*\]`U', '', $description);
		$description = preg_replace('`&(amp;)?#?[a-z0-9]+;`i', '-', $description);
		$description = preg_replace('`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig);`i', '\\1', $description);
		$description = str_replace($this->_search, $this->_replace, $description);
		return $description;
	}	
}