<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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

        $uniqueId  = rand();
        $elementId = 'register-' . $uniqueId;

        $piConfig = Pi::user()->config();

//        $this->setAttribute('novalidate', 'novalidate');
//        $this->setAttribute('class', 'needs-validation');

        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('data-delay', 1000);
        $this->setAttribute('data-html', true);
        $this->setAttribute('id', $elementId);
        $this->setAttribute('onsubmit', "$('#$elementId').validator('destroy');");

        $url = Pi::url(Pi::service('url')->assemble('user', [
            'module'     => 'user',
            'controller' => 'register',
            'action'     => 'validateInput',
        ]));

        if ($this->has('email')) {

            $passwordLink = Pi::service('user')->getUrl('password');
            $urlLogin = Pi::url(Pi::service('url')->assemble('user', [
                'module'     => 'user',
                'controller' => 'login',
                'action'     => 'index',
            ]));
            $this->get('email')
                ->setAttribute('data-error', __('Invalid email'))
                ->setAttribute('data-remote', $url)
                ->setAttribute('data-remote-error', sprintf(__('Oops. This email address is already taken. Do you want to <a href="#" onclick="%s">login</a> or <a href="%s">recover your password</a> ?'), "$('.toggle-modal-action-login:visible').length ? $('.toggle-modal-action-login:visible').click() : window.location='" . $urlLogin . "';return false;", $passwordLink));
        }

        if ($this->has('identity')) {
            $this->get('identity')
                ->setAttribute('data-error', __('Invalid username'))
                ->setAttribute('data-remote', $url)
                ->setAttribute('data-remote-error', __('Oops. This username is already taken, malformed or forbidden'));
        }

        if ($this->get('credential')) {

            $minChars           = $piConfig['password_min'];
            $maxChars           = $piConfig['password_max'];
            $strenghtenPassword = $piConfig['strenghten_password'];

            $showPasswordLabel = __('Show my password');

            $wordLength              = __("Your password is too short");
            $wordNotEmail            = __("Do not use your email as your password");
            $wordSimilarToUsername   = __("Your password cannot contain your username");
            $wordTwoCharacterClasses = __("Use different character classes");
            $wordRepetitions         = __("Too many repetitions");
            $wordSequences           = __("Your password contains sequences");
            $errorList               = __("Errors:");
            $veryWeak                = __("Very week");
            $weak                    = __("Week");
            $normal                  = __("Normal");
            $medium                  = __("Medium");
            $strong                  = __("Strong");
            $veryStrong              = __("Very Strong");
            $message                 = __("Password must contain at least one uppercase letter, one lowercase letter and one digit character");

            $showPasswordBtn
                = <<<HTML
<label>
    <input
        onchange="$('#$elementId').find('input[name=credential], input[name=credential-confirm]').attr('type', function(index, attr){ return attr == 'text' ? 'password' : 'text';})"
        name="show_password"
        type="checkbox"
    />
    
    $showPasswordLabel
</label>

<script>

    var minChar = {$minChars};
    
    var wordLength = "{$wordLength}";
    var wordNotEmail = "{$wordNotEmail}";
    var wordSimilarToUsername = "{$wordSimilarToUsername}";
    var wordTwoCharacterClasses = "{$wordTwoCharacterClasses}";
    var wordRepetitions = "{$wordRepetitions}";
    var wordSequences = "{$wordSequences}";
    var errorList = "{$errorList}";
    var veryWeak = "{$veryWeak}";
    var weak = "{$weak}";
    var normal = "{$normal}";
    var medium = "{$medium}";
    var strong = "{$strong}";
    var veryStrong = "{$veryStrong}";
    
    $('#$elementId').find('[name="credential"]').tooltip({'trigger':'focus', 'title': "{$message}", 'placement' : 'top'});
</script>
HTML;

            $this->get('credential')
                ->setAttribute('description', $showPasswordBtn)
                ->setAttribute('id', 'credential')
                ->setAttribute('pattern', '^.{0,' . $piConfig['password_max'] . '}$')
                ->setAttribute('data-pattern-error', sprintf(__("Must be less than %s characters"), $maxChars))
                ->setAttribute('data-minlength', $piConfig['password_min']);

            if ($strenghtenPassword) {
                $this->get('credential')->setAttribute('data-minlength-error', sprintf(__("Must be more than %s characters"), $minChars))
                    ->setAttribute('data-error', __('Invalid password'))
                    ->setAttribute('data-remote', $url)
                    ->setAttribute('data-remote-error', __('Password must contain at least one uppercase letter, one lowercase letter and one digit character'));
            }


            $passwordConfirmError = __('Whoops, these don\'t match');
            $this->get('credential-confirm')
                ->setAttribute('data-match', '#' . $elementId . ' [name=credential]')
                ->setAttribute('data-match-error', $passwordConfirmError);
        }

        if (Pi::service('module')->isActive('subscription') && isset($piConfig['register_newsletter_optin']) && $piConfig['register_newsletter_optin'] == 1) {
            $this->add([
                'name'       => 'newsletter',
                'type'       => 'checkbox',
                'attributes' => [
                    'description' => __('Newsletter subscription'),
                ],
            ]);
        }

        $this->add([
            'name' => 'redirect',
            'type' => 'hidden',
        ]);

        $this->get('submit')->setValue(__('Registration'));

        /**
         * For invisible recaptcha, need for button instead of input submit
         */
        if(Pi::user()->config('register_captcha') == 3){
            $this->remove('submit');

            $button = new \Laminas\Form\Element\Button('submit-button');
            $button->setLabel(__('Submit'))->setAttribute('class', 'btn btn-secondary');

            $this->add($button);
        }

    }
}