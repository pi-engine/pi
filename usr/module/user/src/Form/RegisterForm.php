<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Form;

/**
 * User registration form
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class RegisterForm extends UserForm
{
    /** {@inheritDoc} */
    protected $configIdentifier = 'register';

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();

        $this->add(array(
            'name'       => 'redirect',
            'type'       => 'hidden',
        ));
    }
}