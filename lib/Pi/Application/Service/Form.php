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
use Laminas\Captcha\ReCaptcha;
use Laminas\ReCaptcha\ReCaptcha as LaminasReCaptcha;
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
            $recaptcha = new ReCaptcha();
            $recaptcha->setSiteKey($captchaPublicKey);
            $recaptcha->setSecretKey($captchaPrivateKey);

            $captchaElement = [
                'type'    => 'captcha',
                'name'    => 'captcha',
                'options' => [
                    'captcha' => $recaptcha,
                ],
            ];
        }

        return $captchaElement;
    }

    public function verifyReCaptcha($data = [])
    {
        $captchaPublicKey  = Pi::config('captcha_public_key');
        $captchaPrivateKey = Pi::config('captcha_private_key');

        $recaptcha = new LaminasReCaptcha();
        $recaptcha->setSiteKey($captchaPublicKey);
        $recaptcha->setSecretKey($captchaPrivateKey);

        $result = $recaptcha->verify($data['g-recaptcha-response']);
        if ($result->isValid()) {
            return true;
        }

        return false;
    }
}
