<?php
/**
 * Form element cache TTL class
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

class CacheTtl extends Select
{
    /**
     * @return array
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $this->valueOptions = array(
                0       => __('No cache'),
                30      => sprintf(__('%d seconds'), 30),
                60      => __('1 minute'),
                300     => sprintf(__('%d minutes'), 5),
                1800    => sprintf(__('%d minutes'), 30),
                3600    => __('1 hour'),
                18000   => sprintf(__('%d hours'), 5),
                86400   => __('1 day'),
                604800  => __('1 week'),
                2592000 => __('1 month'),
            );
        }

        return $this->valueOptions;
    }
}
