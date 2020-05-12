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

use Pi;
use Laminas\Captcha\AdapterInterface as CaptchaAdapter;
use Laminas\Form\Element\Captcha as ZendCaptcha;

/**
 * CAPTCHA element
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Captcha extends ZendCaptcha
{
    /**
     * Retrieve captcha and instantiate it if not available
     *
     * @return null|CaptchaAdapter
     */
    public function getCaptcha()
    {
        if (!$this->captcha instanceof CaptchaAdapter) {
            $captcha = Pi::captcha()->load();
            $this->setCaptcha($captcha);
        }

        return $this->captcha;
    }
}
