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
 * Helper for loading "google +1" button
 *
 * Usage inside a phtml template
 *
 * ```
 *  $this->plusone();
 *  $this->plusone(array('datas-size' => 'small'));
 * ```
 *
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
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
    public function __invoke($config = [])
    {
        $attribs = [];

        // Set size
        if (isset($config['data-size'])
            && in_array($config['data-size'], ['small', 'medium', 'tall'])
        ) {
            $attribs['data-size'] = $config['data-size'];
        }
        // Set annotation
        if (isset($config['data-annotation'])
            && in_array($config['data-annotation'], ['inline', 'none'])
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
        $content
                 .= <<<'EOT'
<script>
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
