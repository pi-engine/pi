<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Form\Element;

use Pi;
use Zend\Form\Element\Radio;

/**
 * Form element for controller selection
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class Gender extends Radio
{
    /**
     * Get value options for Radio
     *
     * @return array
     */
    public function getValueOptions()
    {
        $options = array(
            'male' => __('Male'),
            'female' => __('Female'),
            'unknown' => __('Unknown'),
        );

        $this->valueOptions = $options;
        return $this->valueOptions;
    }
}
