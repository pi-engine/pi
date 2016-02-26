<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
namespace Module\Message\Form;

use Pi;
use Pi\Form\Form as BaseForm;

class PruneForm extends BaseForm
{
    protected $options;

    public function __construct($name = null, $options = array())
    {
        parent::__construct($name);
    }

    public function init()
    {
        // date
        $this->add(array(
            'name' => 'date',
            'type' => 'datepicker',
            'options' => array(
                'label' => __('All messages Before'),
                'datepicker' => array(
                    'format' => 'yyyy-mm-dd',
                ),
            ),
            'attributes' => array(
                'id' => 'time-start',
                'required' => true,
                'value' => date('Y-m-d', strtotime("-3 Months")),
            )
        ));
        // read
        $this->add(array(
            'name' => 'read',
            'type' => 'checkbox',
            'options' => array(
                'label' => __('Just read messages by user'),
            ),
            'attributes' => array(
                'value' => 0,
                'description' => __('Remove read messages by user before selected time'),
            )
        ));
        // deleted
        $this->add(array(
            'name' => 'deleted',
            'type' => 'checkbox',
            'options' => array(
                'label' => __('Just deleted messages by user'),
            ),
            'attributes' => array(
                'value' => 0,
                'description' => __('Remove deleted messages by user before selected time'),
            )
        ));
        // Submit
        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'value' => __('Prune'),
            )
        ));
    }
}	