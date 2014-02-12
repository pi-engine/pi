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
 * Helper for loading "google +1" button
 *
 * Usage inside a phtml template
 *
 * ```
 *  $this->plusone();
 *  $this->plusone(array('datas-size' => 'small'));
 * ```
 *
 * @author Hossein Azizabadi <djvoltan@gmail.com>
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Plusone extends AbstractHtmlElement
{
    /**
     * Add a Google +1 button
     *
     * @param   array $config
     * @return  string
     */
    public function __invoke($config = array())
    {
        $attribs = array();

        // Set size
        if (isset($config['data-size'])
            && in_array($config['data-size'], array('small', 'medium', 'tall'))
        ) {
            $attribs['data-size'] = $config['data-size'];
        }
        // Set annotation
        if (isset($config['data-annotation'])
            && in_array($config['data-annotation'], array('inline', 'none'))
        ) {
            $attribs['data-annotation'] = $config['data-annotation'];
        }
        // Set width
        if (isset($config['data-annotation'], $config['data-width'])
            && $config['data-annotation'] == 'inline'
            && is_numeric($config['data-width'])
        ) {
            $attribs['data-width'] = $config['data-width'];
        }
        $attributeString = $attribs ? $this->htmlAttribs($attribs) : '';

        $content = '<div class="g-plusone"'
                 . ($attributeString ? ' ' . $attributeString : '')
                 . '></div>' . PHP_EOL;
        $content .= <<<'EOT'
<script type="text/javascript">
  (function() {
    var po = document.createElement("script");
    po.type = "text/javascript"; po.async = true;
    po.src = "https://apis.google.com/js/plusone.js";
    var s = document.getElementsByTagName("script")[0];
    s.parentNode.insertBefore(po, s);
  })();
</script>
EOT;

        return $content;
    }
}
