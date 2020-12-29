<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;

/**
 * Form service
 *
 * @author Frédéric TISSOT <contact@espritdev.fr>
 */
class Form extends AbstractService
{
    /**
     * Generate Recaptcha element if enabled
     */
    public function getReCaptcha($captchaMode = null)
    {
        $captchaPublicKey  = Pi::config('captcha_public_key');
        $captchaPrivateKey = Pi::config('captcha_private_key');

        $captchaElement = null;

        if ($captchaMode == 1) {
            $captchaElement = [
                'name'       => 'captcha',
                'type'       => 'captcha',
                'options'    => [
                    'label'            => _a('Please type the word.'),
                    'separator'        => '<br />',
                    'captcha_position' => 'append',
                ],
                'attributes' => [
                    'required' => true,
                ],
            ];
        } elseif ($captchaMode == 2 && $captchaPublicKey && $captchaPrivateKey) {
            $captchaElement = [
                'name'    => 'captcha',
                'type'    => 'captcha',
                'options' => [
                    'captcha' => new \LosReCaptcha\Captcha\ReCaptcha(
                        [
                            'site_key'   => $captchaPublicKey,
                            'secret_key' => $captchaPrivateKey,
                        ]
                    ),
                ],
            ];
        } elseif ($captchaMode == 3 && $captchaPublicKey && $captchaPrivateKey) {
            $captchaElement = [
                'name'    => 'captcha',
                'type'    => 'captcha',
                'options' => [
                    'captcha' => new \LosReCaptcha\Captcha\Invisible(
                        [
                            'site_key'   => $captchaPublicKey,
                            'secret_key' => $captchaPrivateKey,
                            'callback'   => 'captchaSubmit', // Callback to submit the form
                            'button_id'  => 'submit-button', // Button id to submit the form
                        ]
                    ),
                ],
            ];
        }

        return $captchaElement;
    }
}
