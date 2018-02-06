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
use Zend\View\Helper\HeadScript as ZendHeadScript;

/**
 * Helper for setting and retrieving script elements for HTML head section
 *
 * Note: `defer` attribute is enabled by default for JavaScript.
 * To disable it, specify the attribute explicitly `'defer' => false`
 *
 * A new use case with raw type content
 *
 * ```
 *  <...>
 *  <?php
 *  $this->headScript()->captureStart();
 *  ?>
 *  <...>
 *  <?php
 *  // Store with name of "MyScript"
 *  $this->headScript()->captureTo('MyScript');
 *  ?>
 *  <...>
 *  <?php
 *  $this->headScript()->captureStart();
 *  ?>
 *  <...>
 *  <?php
 *  // Content will be discarded since the name of "MyScript" already exists
 *  $this->headScript()->captureTo('MyScript');
 *  ?>
 * ```
 *
 * @see \Zend\View\Helper\HeadScript for details.
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class HeadScript extends ZendHeadScript
{

    protected $optionalAttributes = array(
        'charset',
        'crossorigin',
        'defer',
        'async',
        'language',
        'src',
    );

    /**#@+
     * Added by Taiwen Jiang
     */
    /** @var string[] Segment names for capture */
    protected static $captureNames = array();
    /**#@-*/

    /**
     * {@inheritDoc}
     *
     * Handles `defer` attribute for JavaScript loading
     */
    public function itemToString($item, $indent, $escapeStart, $escapeEnd)
    {
        if (isset($item->attributes['defer']) && !$item->attributes['defer']) {
            unset($item->attributes['defer']);
        }

        return parent::itemToString($item, $indent, $escapeStart, $escapeEnd);
    }

    /**#@+
     * Added by Taiwen Jiang
     */
    /**
     * End capture action and store after checking against stored scripts.
     * The content will be discarded if content with the name already exists
     *
     * @param string $name
     *
     * @return void
     */
    public function captureTo($name)
    {
        // Skip the script segment if it is already captured
        if (in_array($name, static::$captureNames)) {
            ob_end_clean();
            $this->captureScriptType  = null;
            $this->captureScriptAttrs = null;
            $this->captureLock        = false;

            return;
        }
        static::$captureNames[] = $name;
        $this->captureEnd();
    }
    /**#@-*/
}
