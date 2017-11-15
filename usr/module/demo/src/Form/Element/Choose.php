<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Form\Element;

use Pi;
use Zend\Form\Element\Select;

class Choose extends Select
{
    /**
     * @return array
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $this->valueOptions = array(
                1       => __('First Param'),
                2       => __('Second Param'),
                3       => __('Third Param'),
            );
        }

        return $this->valueOptions;
    }
}
