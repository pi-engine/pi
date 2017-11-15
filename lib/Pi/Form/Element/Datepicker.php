<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Form\Element;

use Zend\Form\Element;

/**
 * Element to render bootstrap datepicker
 *
 * usage
 *
 * ```
 *  $form->add(array(
 *      'name'      => 'date',
 *      'type'      => 'datepicker',
 *      'options'   => array(
 *          'label'         => __('Date'),
 *          'datepicker'    => array(
 *              'format'        => 'yyyy-mm-dd',
 *              'start_date'    => '1900-01-01',
 *              'end_date'      => '2020-12-31',
 *          ),
 *      ),
 *      'attributes'    => array(
 *          'id'    => 'demo-date',
 *          'value' => '2014-11-08',
 *      ),
 *  ));
 * ```
 *
 * @see http://bootstrap-datepicker.readthedocs.org/en/release/
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Datepicker extends Element
{
    /**
     * {@inheritDoc}
     */
    protected $attributes = array(
        'type'  => 'datepicker',
    );
}
