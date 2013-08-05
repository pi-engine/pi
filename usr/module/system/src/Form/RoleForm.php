<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Form;

use Pi;
use Pi\Form\Form as BaseForm;

/**
 * Role form
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class RoleForm extends BaseForm
{
    /** @var string Section name */
    protected $section = 'front';

    /**
     * Constructor
     *
     * @param null|string|int $name Optional name for the form
     * @param string $section
     */
    public function __construct($name = null, $section = 'front')
    {
        $this->section = $section;
        parent::__construct($name);
    }

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->add(array(
            'name'          => 'name',
            'options'       => array(
                'label' => __('Name'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            ),
        ));

        $this->add(array(
            'name'          => 'title',
            'options'       => array(
                'label' => __('Title'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            ),
        ));

        $this->add(array(
            'name'  => 'id',
            'attributes'    => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name'  => 'section',
            'attributes'    => array(
                'type'  => 'hidden',
                'value' => $this->section,
            ),
        ));

        $this->add(array(
            'name'  => 'security',
            'type'  => 'csrf',
        ));

        $this->add(array(
            'name'          => 'submit',
            'type'          => 'submit',
            'attributes'    => array(
                'value' => __('Submit'),
            ),
        ));
    }
}
