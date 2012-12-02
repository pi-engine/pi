<?php
/**
 * Form element timezone class
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
 * @package         Pi\Form
 * @subpackage      ELement
 * @version         $Id$
 */

namespace Pi\Form\Element;

use Pi;
use Zend\Form\Element\Select;
use DateTimeZone;

class Timezone extends Select
{
    static protected $timezones = array();

    /**
     * @return array
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            if (!static::$timezones) {
                Pi::service('i18n')->load('timezone');
                foreach(DateTimeZone::listIdentifiers() as $timezone) {
                    static::$timezones[$timezone] = __($timezone);
                }
            }
            $this->valueOptions = static::$timezones;
        }

        return $this->valueOptions;
    }
}
