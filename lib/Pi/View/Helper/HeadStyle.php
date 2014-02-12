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
use Zend\View\Helper\HeadStyle as ZendHeadStyle;

/**
 * Helper for setting and retrieving script elements for HTML head section
 *
 * A new use case with raw type content
 *
 * ```
 *  $this->headStyle()->captureStart();
 *  <...>
 * <?php
 *  // Store with name of "MyScript"
 *  $this->headStyle()->captureTo('MyScript');
 * ?>
 * <...>
 * <?php
 *  $this->headStyle()->captureStart();
 * ?>
 * <...>
 * <?php
 *  // Content will be discarded since the name of "MyScript" already exists
 *  $this->headStyle()->captureTo('MyScript');
 * ?>
 * ```
 *
 * @see \Zend\View\Helper\HeadStyle for details.
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class HeadStyle extends ZendHeadStyle
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
}
