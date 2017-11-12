<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Form\View\Helper;

use Zend\Form\View\Helper\FormMultiCheckbox as ZendFormElement;

/**
 * MultiCheckbox element helper
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FormMultiCheckbox extends ZendFormElement
{
    /** @var array Label attributes */
    protected $labelAttributes = array(
        'class' => 'checkbox',
    );
}
