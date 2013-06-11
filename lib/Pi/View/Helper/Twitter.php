<?php
/**
 * Twitter share helper
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
 * Helper for loading twitter
 *
 * Usage inside a phtml template:
 * <code>
 *  $this->twitter();
 * </code>
 */
class Twitter extends AbstractHtmlElement
{
    /**
     * Add a Twitter share button
     *
     * @return  string
     */
    public function __invoke()
    {
        $content = <<<'EOT'
<a href="https://twitter.com/share" class="twitter-share-button" data-lang="en">Tweet</a>
<script type="text/javascript">
   !function(d,s,id){
      var js,fjs=d.getElementsByTagName(s)[0];
      if(!d.getElementById(id)){
         js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);
      }
   }(document,"script","twitter-wjs");
</script>
EOT;
        return $content;
    }
}