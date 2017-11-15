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
        $captchaPublicKey = Pi::config('captcha_public_key');
        $captchaPrivateKey = Pi::config('captcha_private_key');

        $captchaElement = null;

        if($captchaMode == 1){
            $captchaElement = array(
                'name'          => 'captcha',
                'type'          => 'captcha',
                'options'       => array(
                    'label'     => _a('Please type the word.'),
                    'separator'         => '<br />',
                    'captcha_position'  => 'append',
                ),
                'attributes'    => array(
                    'required' => true,
                ),
            );
        } elseif($captchaMode == 2 && $captchaPublicKey && $captchaPrivateKey){
            $captchaElement = array(
                'name'          => 'captcha',
                'type'          => 'captcha',
                'options'       => array(
                    'captcha' => new \LosReCaptcha\Captcha\ReCaptcha(array(
                            'site_key' => $captchaPublicKey,
                            'secret_key' => $captchaPrivateKey,
                        )
                    ),
                ),
            );
        }

        return $captchaElement;
    }
}