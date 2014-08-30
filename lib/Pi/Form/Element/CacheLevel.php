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
 *  Cache granularity select element
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class CacheLevel extends Select
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
                'whole'     => __('As a whole'),
                'locale'    => __('Per language'),
                'role'      => __('Per role'),
                'guest'     => __('Guest only'),
                //'auth'      => __('Authenticated or not'),
                //'user'      => __('By user'),
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
            $this->label = __('Cache granularity');
        }

        return parent::getLabel();
    }
}
