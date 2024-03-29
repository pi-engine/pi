<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         View
 */

namespace Pi\View\Helper;

use Laminas\View\Helper\AbstractHtmlElement;

/**
 * Helper for loading twitter share button
 *
 * Usage inside a phtml template
 *
 * ```
 *  $this->twitter();
 * ```
 *
 * @author Hossein Azizabadi Farahani <hossein@azizabadi.com>
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
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
        $content
            = <<<'EOT'
<a href="https://twitter.com/share" class="twitter-share-button"
    data-lang="en">Tweet</a>
<script>
   !function(d,s,id){
      var js,fjs=d.getElementsByTagName(s)[0];
      if(!d.getElementById(id)){
         js=d.createElement(s);
         js.id=id;
         js.src="https://platform.twitter.com/widgets.js";
         fjs.parentNode.insertBefore(js,fjs);
      }
   }(document,"script","twitter-wjs");
</script>
EOT;

        return $content;
    }
}
