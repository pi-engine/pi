<?php
/**
 * Facebook like helper
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
 * Helper for loading facebook like
 *
 * Usage inside a phtml template:
 * <code>
 *  $this->facebook();
 * </code>
 */
class Facebook extends AbstractHtmlElement
{
    /**
     * Add a facebook Like button
     *
     * @param array $config
     * @return  string
     */
    public function __invoke($config = array())
    {
        $dataSend = isset($config['data-send']) ? $config['data-send'] : false;
        $dataSend = $dataSend ? 'true' : 'false';
        $dataWidth = isset($config['data-width']) ? $config['data-width'] : 120;
        $dataShowFaces = isset($config['data-show-faces']) ? $config['data-show-faces'] : false;
        $dataShowFaces = $dataShowFaces ? 'true' : 'false';

        $content = <<<'EOT'
<div id="fb-root"></div>
<script type="text/javascript">
    (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, "script", "facebook-jssdk"));
</script>
<div class="fb-like" data-send="%s" data-layout="button_count" data-width="%d" data-show-faces="%s"></div>
EOT;
        $content = sprintf($content, $dataSend, $dataWidth, $dataShowFaces);
        return $content;
    }
}
