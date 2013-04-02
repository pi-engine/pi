<?php
/**
 * Controller plugin Alias class
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
 * @package         Pi\Mvc
 * @version         $Id$
 */

namespace Pi\Mvc\Controller\Plugin;

use Pi;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\MvcEvent;

class Alias extends AbstractPlugin
{
	public $_search = array("&nbsp;","\t","\r\n","\r","\n",",",".","'",";",":",")",
	                        "(",'"','?','!','{','}','[',']','<','>','/','+','-','_',
	                        '\\','*','=','@','#','$','%','^','&');
   public $_replace = array(' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',
                            ' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',
                            ' ',' ',' ',' ',' ','');
   
   /*
	 * $this->alias($alias);
	 */
	public function __invoke($alias, $id, $model) 
	{
      $alias = $this->setAlias($alias);
      $alias = $this->checkAlias($alias, $id, $model);
	   return $alias;
	}
	
	/**
     * Returns the alias
     *
     * @return boolean
     */
	public function setAlias($alias)
	{
		$alias = strip_tags($alias);
		$alias = strtolower($alias);
		$alias = htmlentities($alias, ENT_COMPAT, 'utf-8');
		$alias = preg_replace('`\[.*\]`U', ' ', $alias);
		$alias = preg_replace('`&(amp;)?#?[a-z0-9]+;`i', ' ', $alias);
		$alias = preg_replace('`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig);`i', '\\1', $alias);
		$alias = str_replace($this->_search, $this->_replace, $alias);
		$alias = explode(' ',$alias);
      foreach($alias as $word) {
			if(!empty($word)) {
				$key[] = $word;
			}
		}
      $alias = implode('-',$key);
		return $alias;
	}

	/**
     * Check alias exit ot not
     *
     * @return boolean
     */
	public function checkAlias($alias, $id, $model) 
	{
      if (empty($id)) {
	       $select = $model->select()->columns(array('id', 'alias'))->where(array('alias' => $alias));
      } else {
	    	 $select = $model->select()->columns(array('id', 'alias'))->where(array('alias' => $alias, 'id != ?' => $id));
      }
      $rowset = $model->selectWith($select);
      if($rowset->count()) {
      	 /*
      	  * This part need improvment
      	  * replace rand function whit method for add number
      	  */
	       $alias = $this->setAlias($alias . ' ' . rand(1, 9999));
	       $alias = $this->checkAlias($alias, $id, $model);
	   }
	   return $alias;
	}	
}	