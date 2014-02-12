<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Form
 */

namespace Pi\Form\Element;

use Pi;
use Zend\Form\Element\Csrf as ZendCsrf;
use Zend\Form\FormInterface;

/**
 * CSRF element
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Csrf extends ZendCsrf
{
    /**
     * Prepare the form element
     *
     * Skip duplicated hash generation
     * {@inheritDoc}
     */
    public function prepareElement(FormInterface $form)
    {
        return;
    }
}
