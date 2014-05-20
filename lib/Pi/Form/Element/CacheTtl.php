<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Form\Element;

use Pi;
use Zend\Form\Element\Select;

/**
 * Cache TTL select element
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class CacheTtl extends Select
{
    /**
     * Get options of value select
     *
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

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        if (null === $this->label) {
            $this->label = __('Cache expiration');
        }

        return parent::getLabel();
    }
}
