<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Api;

use Pi;
use Pi\Application\AbstractApi;

/**
 * User password manipulation APIs
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class Mail extends AbstractApi
{
    protected $module = 'user';

    /**
     * Send mail
     *
     * @param $to
     * @param $subject
     * @param $body
     * @param $type
     * @return mixed
     */
    public function send($to, $subject, $body, $type)
    {
        $options = $this->getSmtpOptions();
        $message = Pi::service('mail')->message($subject, $body, $type);
        $message->addTo($to);

        $transport = Pi::service('mail')->loadTransport('smtp', $options);
        return $transport->send($message);
    }

    /**
     * Get smtp config params
     *
     * @return mixed
     */
    protected function getSmtpOptions()
    {
        $path = sprintf(
            '%s/extra/%s/config/smtp.php',
            Pi::path('usr'),
            $this->getModule()
        );

        $smtpOptions = include $path;

        return $smtpOptions;
    }
}
