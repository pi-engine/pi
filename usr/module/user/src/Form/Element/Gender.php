<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
     * {@inheritDoc}
     */
    protected $labelAttributes = array(
        'class' => 'radio-inline',
    );

    /**
     * {@inheritDoc}
     */
    public function getValueOptions()
    {
        if (!$this->valueOptions) {
            $this->valueOptions = array(
                'male'      => __('Male'),
                'female'    => __('Female'),
            );
        }

        return $this->valueOptions;
    }
}
