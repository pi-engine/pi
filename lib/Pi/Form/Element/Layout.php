<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Form
 * @package         Form
 */

namespace Pi\Form\Element;

use Pi;
use Laminas\Form\Element\Select;

/**
 * Theme layout select element
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Layout extends Select
{
    /**
     * Get options of value select
     *
     * @return array
     */
    public function getValueOptions()
    {
        $theme              = $this->getOption('theme');
        $this->valueOptions = Pi::service('theme')->getLayouts($theme);

        return $this->valueOptions;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        if (null === $this->label) {
            $this->label = __('Layout');
        }

        return parent::getLabel();
    }
}
