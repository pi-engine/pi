<?php
/**
 * Google +1 helper
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
 * Helper for loading google +1
 *
 * Usage inside a phtml template:
 * <code>
 *  $this->plusone();
 *  $this->plusone(array('datas-size' => 'small'));
 * </code>
 */
class Plusone extends AbstractHtmlElement
{
    /**
     * Add a Google +1 button
     *
     * @param   array
     * @return  string
     */
    public function __invoke($config = array())
    {
        $attribs = array();

        // Set size
        if (isset($config['data-size']) && in_array($config['data-size'], array('small', 'medium', 'tall'))) {
            $attribs['data-size'] = $config['data-size'];
        }
        // Set annotation
        if (isset($config['data-annotation']) && in_array($config['data-annotation'], array('inline', 'none'))) {
            $attribs['data-annotation'] = $config['data-annotation'];
        }
        // Set width
        if (isset($config['data-annotation'], $config['data-width']) && $config['data-annotation'] == 'inline' && is_numeric($config['data-width']) ) {
            $attribs['data-width'] = $config['data-width'];
        }
        $attributeString = $attribs ? $this->htmlAttribs($attribs) : '';

        $content = '<div class="g-plusone"' . ($attributeString ? ' ' . $attributeString : '') . '></div>' . PHP_EOL;
        $content .= <<<'EOT'
<script type="text/javascript">
  (function() {
    var po = document.createElement("script"); po.type = "text/javascript"; po.async = true;
    po.src = "https://apis.google.com/js/plusone.js";
    var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(po, s);
  })();' . self::EOL
</script>
EOT;
         return $content;
    }
}