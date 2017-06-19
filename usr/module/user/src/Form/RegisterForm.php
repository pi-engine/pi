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

        $uniqueId = rand();
        $elementId = 'register-' . $uniqueId;

        $piConfig = Pi::user()->config();

        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('data-html', true);
        $this->setAttribute('id', $elementId);
        $this->setAttribute('onsubmit', "$('#$elementId').validator('destroy');");

        $url = Pi::url(Pi::service('url')->assemble('user', array(
            'module' => 'user',
            'controller' => 'register',
            'action' => 'validateInput',
        )));

        if($this->has('email')){

            $passwordLink = Pi::service('user')->getUrl('password');

            $this->get('email')
                ->setAttribute('data-error', __('Invalid email'))
                ->setAttribute('data-remote', $url)
                ->setAttribute('data-remote-error', sprintf(__('Oops. This email address is already taken. Do you want to <a href="#" onclick="%s">login</a> or <a href="%s">recover your password</a> ?'), "$('.toggle-modal-action-login').click();return false;", $passwordLink));
        }

        if($this->has('identity')){
            $this->get('identity')
                ->setAttribute('data-error', __('Invalid username'))
                ->setAttribute('data-remote', $url)
                ->setAttribute('data-remote-error', __('Oops. This username is already taken, malformed or forbidden'));
        }

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
                ->setAttribute('data-pattern-error', sprintf(__("Must be less than %s characters"), $piConfig['password_max']))
                ->setAttribute('data-minlength', $piConfig['password_min'])
                ->setAttribute('data-minlength-error', sprintf(__("Must be more than %s characters"), $piConfig['password_min']));


            $passwordConfirmError = __('Whoops, these don\'t match');
            $this->get('credential-confirm')
                ->setAttribute('data-match', '#'.$elementId. ' [name=credential]')
                ->setAttribute('data-match-error', $passwordConfirmError);
        }

        if(Pi::service('module')->isActive('subscription') && isset($piConfig['register_newsletter_optin']) && $piConfig['register_newsletter_optin'] == 1){
            $this->add(array(
                'name'       => 'newsletter',
                'type'      => 'checkbox',
                'attributes' => array(
                    'description' => __('Newsletter subscription')
                )
            ));
        }

        $this->add(array(
            'name'       => 'redirect',
            'type'       => 'hidden',
        ));
    }
}