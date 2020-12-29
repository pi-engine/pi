<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Front;

use Module\User\Form\FindPasswordFilter;
use Module\User\Form\FindPasswordForm;
use Module\User\Form\PasswordFilter;
use Module\User\Form\PasswordForm;
use Module\User\Form\ResetPasswordFilter;
use Module\User\Form\ResetPasswordForm;
use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Password controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class PasswordController extends ActionController
{
    /**
     * Change password for current user
     *
     * @return array|void
     */
    public function indexAction()
    {
        Pi::service('authentication')->requireLogin();
        Pi::api('profile', 'user')->requireComplete();

        $uid = Pi::user()->getId();

        $result = [
            'status'  => 0,
            'message' => __('Reset password failed.'),
        ];

        $form = new PasswordForm('password-change');
        $form->setAttribute('action', '#');

        $uniqueId  = rand();
        $elementId = 'register-' . $uniqueId;

        $form->setAttribute('data-toggle', 'validator');
        $form->setAttribute('data-delay', 1000);
        $form->setAttribute('data-html', true);
        $form->setAttribute('id', $elementId);
        $form->setAttribute('onsubmit', "$('#$elementId').validator('destroy');");


        $passwordConfirmError = __('Whoops, these don\'t match');
        $form->get('credential-confirm')
            ->setAttribute('data-match', '#' . $elementId . ' [name=credential-new]')
            ->setAttribute('data-match-error', $passwordConfirmError);

        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new PasswordFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                /*
                // Verify password
                $row = Pi::model('user_account')->find($uid, 'id');
                if ($row['credential'] == $row->transformCredential($values['credential'])) {
                    //if ($credential == $row->credential) {
                    // Update password
                    Pi::api('user', 'user')->updateAccount(
                        $uid,
                        array(
                            'credential' => $values['credential-new']
                        )
                    );
                    Pi::service('event')->trigger('password_change', $uid);

                    $result['status'] = 1;
                    $result['message'] = __('Reset password successfully.');
                } else {
                    $result['message'] = __('Invalid password.');
                }
                */
                // Update password
                Pi::api('user', 'user')->updateAccount(
                    $uid,
                    [
                        'credential' => $values['credential-new'],
                    ]
                );
                Pi::service('event')->trigger('password_change', $uid);

                $result['status']  = 1;
                $result['message'] = __('Reset password successfully.');
            }

            $this->view()->assign('result', $result);
        }

        // Get side nav items
        //$groups = Pi::api('group', 'user')->getList();
        //$user   = Pi::api('user', 'user')->get($uid, array('uid', 'name'));

        $piConfig           = Pi::user()->config();
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

        $message = __("Password must contain at least one uppercase letter, one lowercase letter and one digit character");

        $script
            = <<<HTML
        
<label>
    <input
        onchange="$('input[name=credential-new], input[name=credential-confirm]').attr('type', function(index, attr){ return attr == 'text' ? 'password' : 'text';})"
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
    
    jQuery('[name="credential"]').tooltip({'trigger':'focus', 'title': "{$message}", 'placement' : 'top'});
</script>
HTML;

        $form->get('credential')->setAttribute('id', 'credential-verify');
        $form->get('credential-new')
            ->setAttribute('description', $script);

        if ($strenghtenPassword) {
            $url = Pi::url(
                Pi::service('url')->assemble(
                    'user',
                    [
                        'module'     => 'user',
                        'controller' => 'password',
                        'action'     => 'validateInput',
                    ]
                )
            );

            $form->get('credential-new')->setAttribute('data-minlength-error', sprintf(__("Must be more than %s characters"), $minChars))
                ->setAttribute('data-error', __('Invalid password'))
                ->setAttribute('data-remote', $url)
                ->setAttribute('data-remote-error', __('Password must contain at least one uppercase letter, one lowercase letter and one digit character'));
        }

        $this->view()->assign(
            [
                'form' => $form,
                //'groups'    => $groups,
                //'cur_group' => 'password',
                //'user'      => $user,
            ]
        );

        $this->view()->headTitle(__('Change password'));
        $this->view()->headdescription(__('To ensure your account security, complex password is required.'), 'set');
        $this->view()->headkeywords($this->config('head_keywords'), 'set');
    }

    /**
     * 1. Display find password form
     * 2. Verify email
     * 3. Send verify email
     *
     */
    public function findAction()
    {
        // Check usrr not login
        if (Pi::service('user')->hasIdentity()) {
            $this->redirect()->toUrl(Pi::service('user')->getUrl('profile'));
            return false;
        }

        $redirect = $this->params('redirect');
        $result   = [
            'status'  => 0,
            'message' => __('Find password failed.'),
        ];
        $form     = new FindPasswordForm('find-password');

        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new FindPasswordFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $value = $form->getData();

                // Check if email exists
                if (Pi::user()->config('is_mobile')) {
                    $userRow = Pi::service('user')->getUser($value['identity'], 'identity');
                } else {
                    $userRow = Pi::service('user')->getUser($value['email'], 'email');
                }

                if (!$userRow) {
                    $this->view()->assign(
                        [
                            'form'   => $form,
                            'result' => $result,
                        ]
                    );

                    return;
                }

                // Set user data
                $uid = (int)$userRow->id;

                if (Pi::user()->config('is_mobile')) {
                    // Set params
                    $params = [
                        'uid'      => $uid,
                        'identity' => $value['identity'],
                        'name'     => $userRow->name,
                    ];

                    // reset password and send
                    Pi::api('mobile', 'user')->password($params);

                    // jump
                    $message = __('Your password reset successfully and send as SMS to you, please login to website by your mobile number and new password');
                    $this->jump(['controller' => 'login', 'action' => 'index'], $message);
                } else {
                    $token = $this->createToken($uid, $value['email']);
                    Pi::user()->data()->set(
                        $uid,
                        'find-password',
                        $token,
                        'user',
                        $this->config('email_expiration') * 3600
                    );
                    Pi::user()->data()->set(
                        $uid,
                        'redirect-password',
                        $redirect,
                        'user',
                        $this->config('email_expiration') * 3600
                    );

                    // Send verify email
                    $to   = $userRow->email;
                    $url  = $this->url(
                        '',
                        [
                            'action' => 'process',
                            'token'  => $token,
                        ]
                    );
                    $link = Pi::url($url, true);

                    $params = [
                        'username'          => $userRow->identity,
                        'find_password_url' => $link,
                        'expiration'        => $this->config('email_expiration'),
                    ];

                    // Load from HTML template
                    $data = Pi::service('mail')->template(
                        'find-password-html',
                        $params
                    );

                    // Mail body logging
                    Pi::user()->data()->set(
                        $uid,
                        'find-password-body',
                        $data['body']
                    );

                    // Set subject and body
                    $subject = $data['subject'];
                    $body    = $data['body'];
                    $type    = $data['format'];

                    $message = Pi::service('mail')->message($subject, $body, $type);
                    $message->addTo($to);
                    Pi::service('mail')->send($message);

                    $result['status']  = 1;
                    $result['message'] = __('We sent an email to reset your password. Please check your email and follow the instructions to reset it.');
                }
            }

            $this->view()->assign('result', $result);
        }

        $this->view()->assign('form', $form);
        $this->view()->setTemplate('password-find');

        $this->view()->headTitle(__('Find password'));
        $this->view()->headdescription(__('Find password'), 'set');
        $this->view()->headkeywords($this->config('head_keywords'), 'set');
    }

    /**
     * 1. Verify find password link
     * 2. Update user information
     */
    public function processAction()
    {
        $result = [
            'status'  => 0,
            'message' => __('Invalid token for password reset.'),
        ];
        $token  = _get('token');

        $view     = $this->view();
        $fallback = function () use ($view, $result) {
            $view->assign('result', $result);
        };
        // Verify link invalid
        if (!$token) {
            return $fallback();
        }

        $userData = Pi::user()->data()->find(
            [
                'name'  => 'find-password',
                'value' => $token,
            ]
        );
        if (!$userData) {
            return $fallback();
        }

        /*
        // Check link expire time
        $expire = $this->config('email_expiration');
        if ($expire) {
            $expire  = $userData['time'] + $expire * 3600;
            if (time() > $expire) {
                return $fallback();
            }
        }
        */

        $uid     = (int)$userData['uid'];
        $userRow = $this->getModel('account')->find($uid, 'id');
        if (!$userRow) {
            return $fallback();
        }

        $uid  = $userRow->id;
        $form = new ResetPasswordForm('find-password', 'find');

        $uniqueId  = rand();
        $elementId = 'register-' . $uniqueId;

        $form->setAttribute('data-toggle', 'validator');
        $form->setAttribute('data-delay', 1000);
        $form->setAttribute('data-html', true);
        $form->setAttribute('id', $elementId);
        $form->setAttribute('onsubmit', "$('#$elementId').validator('destroy');");

        $piConfig           = Pi::user()->config();
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

        $message = __("Password must contain at least one uppercase letter, one lowercase letter and one digit character");

        $script
            = <<<HTML
        
<label>
    <input
        onchange="$('input[name=credential-new], input[name=credential-confirm]').attr('type', function(index, attr){ return attr == 'text' ? 'password' : 'text';})"
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
    
    jQuery('[name="credential-new"]').tooltip({'trigger':'focus', 'title': "{$message}", 'placement' : 'top'});
</script>
HTML;

        $form->get('credential-new')
            ->setAttribute('description', $script)
            ->setAttribute('id', 'credential-new')
            ->setAttribute('pattern', '^.{0,' . $piConfig['password_max'] . '}$')
            ->setAttribute('data-pattern-error', sprintf(__("Must be less than %s characters"), $maxChars))
            ->setAttribute('data-minlength', $piConfig['password_min']);

        if ($strenghtenPassword) {
            $url = Pi::url(
                Pi::service('url')->assemble(
                    'user',
                    [
                        'module'     => 'user',
                        'controller' => 'password',
                        'action'     => 'validateInput',
                    ]
                )
            );

            $form->get('credential-new')->setAttribute('data-minlength-error', sprintf(__("Must be more than %s characters"), $minChars))
                ->setAttribute('data-error', __('Invalid password'))
                ->setAttribute('data-remote', $url)
                ->setAttribute('data-remote-error', __('Password must contain at least one uppercase letter, one lowercase letter and one digit character'));
        }

        $passwordConfirmError = __('Whoops, these don\'t match');
        $form->get('credential-confirm')
            ->setAttribute('data-match', '#' . $elementId . ' [name=credential-new]')
            ->setAttribute('data-match-error', $passwordConfirmError);

        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new ResetPasswordFilter('find'));
            $form->setData($data);

            if ($form->isValid()) {
                $values = $form->getData();

                // Update user account data
                Pi::api('user', 'user')->updateAccount(
                    $uid,
                    ['credential' => $values['credential-new']]
                );

                Pi::service('event')->trigger('password_change', $uid);
                // Delete find password verify token
                Pi::user()->data()->delete($uid, 'find-password');
                $redirect = Pi::user()->data()->find(
                    [
                        'name' => 'redirect-password',
                        'uid'  => $uid,
                    ]
                );
                if (!$redirect) {
                    $redirect = Pi::user()->getUrl('profile');
                }
                Pi::user()->data()->delete(redirect, 'find-password');

                $result['message']  = __('Password reset successfully.');
                $result['status']   = 1;
                $result['redirect'] = $redirect['value'];
            } else {
                $form->setData(['token' => $token]);
                $this->view()->assign(
                    [
                        'form' => $form,
                    ]
                );
            }
            $this->view()->assign('result', $result);
        } else {
            $form->setData(['token' => $token]);
            $this->view()->assign(
                [
                    'form' => $form,
                ]
            );
        }
    }

    /**
     * Creates token
     *
     * @param int    $uid
     * @param string $email
     *
     * @return string
     */
    protected function createToken($uid, $email)
    {
        $token = md5($uid . $email . Pi::config('salt') . mt_rand());

        return $token;
    }

    public function validateInputAction()
    {
        Pi::service('log')->mute();

        $data = (array)$this->params()->fromQuery();

        $response = [
            'error'   => false,
            'message' => false,
        ];

        // Get register form
        /* @var $form \Module\User\Form\PasswordForm */
        $form = new PasswordForm('password-change');
        $form->setInputFilter(new PasswordFilter);
        $form->setData($data);

        if ($form->has('captcha')) {
            $form->remove('captcha');
        }

        $messages = [];

        if (!$form->isValid()) {
            $messages = $form->getMessages();
        };


        $dataMessages = array_intersect_key($messages, $data);

        if ($dataMessages) {
            $firstElementMessages = array_shift($dataMessages);

            foreach ($firstElementMessages as $message) {
                $response['message'] = $message;
            }

            $response['error'] = true;

            $this->getResponse()->setStatusCode(404);
        }


        $this->getResponse()->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        ;
        $this->getResponse()->setContent(json_encode($response));

        return $this->getResponse();
    }
}
