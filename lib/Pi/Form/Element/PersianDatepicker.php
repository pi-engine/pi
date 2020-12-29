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

use Laminas\Form\Element;

/**
 * Element to render persian date picker
 *
 * usage
 * Set two form objects :
 *    - one of them just for date picker
 *    - other one is hidden and for get timestamp value
 *    - on controller just get timestamp value and use it
 *    - for set value on edit option set value for both of form objects
 *
 * ```
 *  // Set persian date picker
 *  $form->add([
 *      'name'      => 'date',
 *      'type'      => 'persianDatepicker',
 *      'options'   => array(
 *          'label'         => __('Date'),
 *          'datepicker'    => array(
 *              'altField'      => '.setTimeStamp',
 *              'format'        => 'YYYY/MM/DD HH:mm:ss',
 *              'calendarType'  => 'gregorian',
 *              'autoClose'     => 'true',
 *              'timePicker'    => [
 *                  'enabled'  => 'true',
 *                  'meridiem' => [
 *                      'enabled' => 'true',
 *                  ],
 *              ],
 *              'toolbox' => [
 *                  'enabled'     => 'true',
 *                  'todayButton' => [
 *                      'enabled' => 'true',
 *                  ],
 *              ],
 *          ),
 *      ),
 *      'attributes'    => array(
 *          'id'    => 'demo-date',
 *          'value' => date('Y/m/d HH:mm:ss', strtotime('+1 weeks')),
 *      ),
 *  ]);
 *  // Set time stamp
 *  $this->add([
 *     'name'       => 'setTimeStamp',
 *     'attributes' => [
 *         'type'  => 'hidden',
 *         'class' => 'setTimeStamp',
 *     ],
 *  ]);
 * ```
 *
 * {@inheritDoc}
 * @see    https://babakhani.github.io/PersianWebToolkit
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PersianDatepicker extends Element
{
    /**
     * {@inheritDoc}
     */
    protected $attributes
        = [
            'type' => 'persiandatepicker',
        ];
}
