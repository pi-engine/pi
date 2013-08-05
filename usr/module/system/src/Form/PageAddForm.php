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
 * Page adding form
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PageAddForm extends BaseForm
{
    /** @var string Module name */
    protected $module;

    /**
     * Constructor
     *
     * @param null|string|int $name Optional name for the form
     * @param string $module Page module
     */
    public function __construct($name = null, $module = null)
    {
        $this->module = $module;
        parent::__construct($name);
    }

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->add(array(
            'name'          => 'title',
            'options'       => array(
                'label' => __('Title'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name'          => 'section',
            'attributes'    => array(
                'type'      => 'hidden',
                'value'     => 'front',
            ),
        ));

        $this->add(array(
            'name'          => 'controller',
            'options'       => array(
                'label'     => __('Controller'),
                'module'    => $this->module,
            ),
            'type'          => 'Module\\System\\Form\\Element\\Controller',
        ));

        $this->add(array(
            'name'          => 'action',
            'options'       => array(
                'label' => __('Action'),
            ),
        ));

        $this->add(array(
            'name'          => 'cache_ttl',
            'type'          => 'cacheTtl',
            'options'       => array(
                'label' => __('Cache TTL'),
            ),
        ));

        $this->add(array(
            'name'          => 'cache_level',
            'type'          => 'cacheLevel',
            'options'       => array(
                'label' => __('Cache level'),
            ),
        ));

        $this->add(array(
            'name'  => 'security',
            'type'  => 'csrf',
        ));

        $this->add(array(
            'name'  => 'module',
            'attributes'    => array(
                'type'  => 'hidden',
                'value' => $this->module,
            ),
        ));

        $this->add(array(
            'name'          => 'submit',
            'type'          => 'submit',
            'attributes'    => array(
                'value' => __('Submit'),
            )
        ));
    }
}
