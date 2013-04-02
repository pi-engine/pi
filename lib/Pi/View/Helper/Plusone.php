<?php
/**
 * Plusone helper
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
 * Helper for loading google pliusone
 *
 * Usage inside a phtml template:
 * <code>
 *  $this->plusone();
 *  $this->plusone(array()); 
 * </code>
 */
class Plusone extends AbstractHtmlElement
{ 
    /**
     * Make Plusone
     *
     * @param   array
     * @return  Button
     */
    public function __invoke($setting = '')
    {
        $attribs = array();
        if(isset($setting['data-size']) && in_array($setting['data-size'], array('small', 'medium', 'tall'))) {
		     $attribs['data-size'] = $setting['data-size'];	
		  }	
		  
        if(isset($setting['data-annotation']) && in_array($setting['data-annotation'], array('inline', 'none'))) {
		     $attribs['data-annotation'] = $setting['data-annotation'];
		  }	
		  
        if(isset($setting['data-annotation'], $setting['data-width']) && $setting['data-annotation'] == 'inline' && is_numeric($setting['data-width']) ) {
		     $attribs['data-width'] = $setting['data-width'];
		  }

        return '<div class="g-plusone" ' . $this->htmlAttribs($attribs) . '></div>' . self::EOL
             . '<script type="text/javascript">' . self::EOL
             . '  (function() {' . self::EOL
             . '    var po = document.createElement("script"); po.type = "text/javascript"; po.async = true;' . self::EOL
             . '    po.src = "https://apis.google.com/js/plusone.js";' . self::EOL
             . '    var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(po, s);' . self::EOL
             . '  })();' . self::EOL
             . '</script>';
    }	
}