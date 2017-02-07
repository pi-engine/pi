<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Form;

use Pi;

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

        $piConfig = Pi::user()->config();

        $this->setAttribute('data-toggle', 'validator');

        $this->get('email')->setAttribute('data-error', 'Invalid email');

        if($this->get('credential')){


            $showPasswordLabel = __('Show my password');
            $showPasswordBtn = <<<HTML
<label>
    <input
        onchange="$('input[name=credential], input[name=credential-confirm]').attr('type', function(index, attr){ return attr == 'text' ? 'password' : 'text';})"
        name="show_password"
        type="checkbox"
    />
    
    $showPasswordLabel
</label>
HTML;

            $this->get('credential')
                ->setAttribute('description', $showPasswordBtn)
                ->setAttribute('id', 'credential')
                ->setAttribute('pattern', '^.{0,'.$piConfig['password_max'].'}$')
                ->setAttribute('data-pattern-error', __("Must be less than ".$piConfig['password_max']." characters"))
                ->setAttribute('data-minlength', $piConfig['password_min'])
                ->setAttribute('data-minlength-error', __("Must be more than 8 characters"));


            $passwordConfirmError = __('Whoops, these don\'t match');
            $this->get('credential-confirm')
                ->setAttribute('data-match', '#credential')
                ->setAttribute('data-match-error', $passwordConfirmError);
        }

        $this->add(array(
            'name'       => 'redirect',
            'type'       => 'hidden',
        ));
    }
}