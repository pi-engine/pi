<?php
/**
 * HeadStyle
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
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Pi\View
 * @subpackage      Helper
 * @version         $Id$
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\HeadStyle as ZendHeadStyle;

/**
 * Helper for setting and retrieving script elements for HTML head section
 *
 * @see HeadStyle for details.
 * A new use case with raw type content:
 * <code>
 * <?php
 *  $this->headStyle()->captureStart();
 * ?>
 * <some script>
 * <?php
 *  // Store with name of "MyScript"
 *  $this->headStyle()->captureTo('MyScript');
 * ?>
 *
 * <?php
 *  $this->headStyle()->captureStart();
 * ?>
 * <some script>
 * <?php
 *  // The content will be discarded since the name of "MyScript" already exists
 *  $this->headStyle()->captureTo('MyScript');
 * ?>
 * </code>
 */
class HeadStyle extends ZendHeadStyle
{
    /**#@+
     * Added by Taiwen Jiang
     */
    protected static $captureNames = array();
    /**#@-*/

    /**#@+
     * Added by Taiwen Jiang
     */
    /**
     * End capture action and store after checking against stored scripts. The content will be discarded if content with the name already exists
     *
     * @params string $name
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
