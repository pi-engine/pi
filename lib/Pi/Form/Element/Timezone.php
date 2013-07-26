<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Form
 * @package         Form
 */

namespace Pi\Form\Element;

use Pi;
use Zend\Form\Element\Select;
use DateTimeZone;

/**
 * Navigation select element
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Timezone extends Select
{
    /** @var array Timezones */
    static protected $timezones = array();

    /**
     * Get options of value select
     *
     * @return array
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            if (!static::$timezones) {
                Pi::service('i18n')->load('timezone');
                foreach (DateTimeZone::listIdentifiers() as $timezone) {
                    static::$timezones[$timezone] = __($timezone);
                }
            }
            $this->valueOptions = static::$timezones;
        }

        return $this->valueOptions;
    }
}
