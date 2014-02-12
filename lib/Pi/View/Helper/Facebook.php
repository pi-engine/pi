<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHtmlElement;

/**
 * Helper for loading "facebook like" widget
 *
 * Usage inside a phtml template
 *
 * ```
 *  $this->facebook();
 * ```
 *
 * @author Hossein Azizabadi <djvoltan@gmail.com>
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
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
        $dataWidth = isset($config['data-width'])
            ? $config['data-width'] : 120;
        $dataShowFaces = isset($config['data-show-faces'])
            ? $config['data-show-faces'] : false;
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
<div class="fb-like" data-send="%s" data-layout="button_count" data-width="%d"
    data-show-faces="%s"></div>
EOT;
        $content = sprintf($content, $dataSend, $dataWidth, $dataShowFaces);

        return $content;
    }
}
