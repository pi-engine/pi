<?php
/**
 * Add page form
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Module\System
 * @subpackage      Form
 * @version         $Id$
 */

namespace Module\System\Form;

use Pi;
use Pi\Form\Form as BaseForm;

class PageAddForm extends BaseForm
{
    protected $module;

    /**
     * Constructor
     *
     * @param null|string|int $name Optional name for the element
     * @param string $module Page module
     */
    public function __construct($name = null, $module = null)
    {
        $this->module = $module;
        parent::__construct($name);
    }

    /*
    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new PageAddFilter;
        }
        return $this->filter;
    }
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

        /*
        $this->add(array(
            'name'          => 'action',
            'type'  => 'select',
            'options'       => array(
                'label' => __('Action'),
                'options'   => array(
                    'index' => 'index',
                ),
            ),
        ));
        */

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
