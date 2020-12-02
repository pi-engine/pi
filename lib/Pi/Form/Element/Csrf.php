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

use Laminas\Form\Element\Csrf as LaminasCsrf;
use Laminas\Form\FormInterface;

/**
 * CSRF element
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Csrf extends LaminasCsrf
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
