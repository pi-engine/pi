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

            $minChars = $piConfig['password_min'];
            $maxChars = $piConfig['password_max'];
            $strenghtenPassword = $piConfig['strenghten_password'];

            $showPasswordLabel = __('Show my password');

            $wordLength = __("Your password is too short");
            $wordNotEmail = __("Do not use your email as your password");
            $wordSimilarToUsername = __("Your password cannot contain your username");
            $wordTwoCharacterClasses = __("Use different character classes");
            $wordRepetitions = __("Too many repetitions");
            $wordSequences = __("Your password contains sequences");
            $errorList = __("Errors:");
            $veryWeak = __("Very week");
            $weak = __("Week");
            $normal = __("Normal");
            $medium = __("Medium");
            $strong = __("Strong");
            $veryStrong = __("Very Strong");

            $showPasswordBtn = <<<HTML
<label>
    <input
        onchange="$('input[name=credential], input[name=credential-confirm]').attr('type', function(index, attr){ return attr == 'text' ? 'password' : 'text';})"
        name="show_password"
        type="checkbox"
    />
    
    $showPasswordLabel
</label>

<script>

    function translateThisThing(key){        
        var translations = {
            "wordLength": "{$wordLength}",
            "wordNotEmail": "{$wordNotEmail}",
            "wordSimilarToUsername": "{$wordSimilarToUsername}",
            "wordTwoCharacterClasses": "{$wordTwoCharacterClasses}",
            "wordRepetitions": "{$wordRepetitions}",
            "wordSequences": "{$wordSequences}",
            "errorList": "{$errorList}",
            "veryWeak": "{$veryWeak}",
            "weak": "{$weak}",
            "normal": "{$normal}",
            "medium": "{$medium}",
            "strong": "{$strong}",
            "veryStrong": "{$veryStrong}"
        };
        
        return translations[key];
    };

    $('[name="register"] #credential').not('.pwstrengthEnabled').addClass('pwstrengthEnabled').pwstrength({
        common: {
            minChar: {$minChars}
        },
        rules: {
            scores : {
                wordNotEmail: -100,
                wordLength: -50,
                wordSimilarToUsername: -100,
                wordSequences: -50,
                wordTwoCharacterClasses: 2,
                wordRepetitions: -25,
                wordLowercase: 1,
                wordUppercase: 20,
                wordOneNumber: 20,
                wordThreeNumbers: 5,
                wordOneSpecialChar: 3,
                wordTwoSpecialChar: 5,
                wordUpperLowerCombo: 2,
                wordLetterNumberCombo: 2,
                wordLetterNumberCharCombo: 2
            }
        },
        i18n : {
            t: function (key) {
            var result = translateThisThing(key); // Do your magic here

            return result === key ? '' : result; // This assumes you return the
            // key if no translation was found, adapt as necessary
        }
        }
    });
</script>
HTML;

            $this->get('credential')
                ->setAttribute('description', $showPasswordBtn)
                ->setAttribute('id', 'credential')
                ->setAttribute('pattern', '^.{0,'.$piConfig['password_max'].'}$')
                ->setAttribute('data-pattern-error', sprintf(__("Must be less than %s characters"), $maxChars))
                ->setAttribute('data-minlength', $piConfig['password_min']);

            if($strenghtenPassword){
                $this->get('credential')->setAttribute('data-minlength-error', sprintf(__("Must be more than %s characters"), $minChars))
                    ->setAttribute('data-error', __('Invalid password'))
                    ->setAttribute('data-remote', $url)
                    ->setAttribute('data-remote-error', __('Password must contain at lease one uppercase letter, one lowercase letter and one digit character'))
                ;
            }


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