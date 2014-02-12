<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
