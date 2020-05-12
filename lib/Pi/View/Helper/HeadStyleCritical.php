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

use Pi;
use Laminas\View\Helper\HeadStyle as ZendHeadStyle;
use stdClass;
use Laminas\View;

/**
 * Helper for setting and retrieving script elements for HTML head section
 * BEFORE LINKS (for critical css, in order to be overriden by links css and normal inline css
 *
 * A new use case with raw type content
 *
 * ```
 *  $this->HeadStyleCritical()->captureStart();
 *  <...>
 * <?php
 *  // Store with name of "MyScript"
 *  $this->HeadStyleCritical()->captureTo('MyScript');
 * ?>
 * <...>
 * <?php
 *  $this->HeadStyleCritical()->captureStart();
 * ?>
 * <...>
 * <?php
 *  // Content will be discarded since the name of "MyScript" already exists
 *  $this->HeadStyleCritical()->captureTo('MyScript');
 * ?>
 * ```
 *
 * @see \Laminas\View\Helper\HeadStyle for details.
 * @author Frédéric TISSOT
 */
class HeadStyleCritical extends ZendHeadStyle
{
    /**#@+
     * Added by Taiwen Jiang
     */
    /** @var string[] Segment names for catch */
    protected static $captureNames = array();
    /**#@-*/

    /**#@+
     * Added by Taiwen Jiang
     */
    /**
     * End capture action and store after checking against stored scripts.
     * The content will be discarded if content with the name already exists
     *
     * @param string $name
     * @return void
     */
    public function captureTo($name)
    {
        if (in_array($name, static::$captureNames)) {
            ob_get_clean();
            $this->captureAttrs = null;
            $this->captureLock  = false;
            return;
        }
        static::$captureNames[] = $name;
        $this->captureEnd();
    }
    /**#@-*/

    /**
     * Convert content and attributes into valid style tag
     *
     * @param  stdClass $item   Item to render
     * @param  string   $indent Indentation to use
     * @return string
     */
    public function itemToString(stdClass $item, $indent)
    {
        $attrString = '';
        if (!empty($item->attributes)) {
            $enc = 'UTF-8';
            if ($this->view instanceof View\Renderer\RendererInterface
                && method_exists($this->view, 'getEncoding')
            ) {
                $enc = $this->view->getEncoding();
            }
            $escaper = $this->getEscaper($enc);
            foreach ($item->attributes as $key => $value) {
                if (!in_array($key, $this->optionalAttributes)) {
                    continue;
                }
                if ('media' == $key) {
                    if (false === strpos($value, ',')) {
                        if (!in_array($value, $this->mediaTypes)) {
                            continue;
                        }
                    } else {
                        $mediaTypes = explode(',', $value);
                        $value = '';
                        foreach ($mediaTypes as $type) {
                            $type = trim($type);
                            if (!in_array($type, $this->mediaTypes)) {
                                continue;
                            }
                            $value .= $type .',';
                        }
                        $value = substr($value, 0, -1);
                    }
                }
                $attrString .= sprintf(' %s="%s"', $key, $escaper->escapeHtmlAttr($value));
            }
        }

        $escapeStart = $indent . '<!--' . PHP_EOL;
        $escapeEnd = $indent . '-->' . PHP_EOL;
        if (isset($item->attributes['conditional'])
            && !empty($item->attributes['conditional'])
            && is_string($item->attributes['conditional'])
        ) {
            $escapeStart = null;
            $escapeEnd = null;
        }

        $html = '<style ' . $attrString . '>' . PHP_EOL
             . $indent . $item->content . PHP_EOL
            . '</style>';

        if (null == $escapeStart && null == $escapeEnd) {
            // inner wrap with comment end and start if !IE
            if (str_replace(' ', '', $item->attributes['conditional']) === '!IE') {
                $html = '<!-->' . $html . '<!--';
            }
            $html = '<!--[if ' . $item->attributes['conditional'] . ']>' . $html . '<![endif]-->';
        }

        return $html;
    }
}
