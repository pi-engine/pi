<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Zend\Http\Client;

/**
 * Notification service
 *
 * - Pi::service('notification')->send($to, $template, $information, $module, $uid);
 * - Pi::service('notification')->smsToUser($content, $number);
 * - Pi::service('notification')->smsToAdmin($content, $number);
 * - Pi::service('notification')->fcm($notification, $option);
 *
 * - ToDo : user setting for active / inactive push notification on website and mobile
 * - ToDo : improve send sms on notification module and support local
 * - Todo : improve notification module to support custom notification for change module contents
 *
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
class Notification extends AbstractService
{
    /** {@inheritDoc} */
    protected $fileIdentifier = 'notification';

    /**
     * Is notification service available
     *
     * @return bool
     */
    public function active()
    {
        return Pi::service('module')->isActive('notification');
    }

    /**
     * Send mail and message notification
     *
     * @param array|string $to
     * @param string       $template
     * @param array        $information
     * @param string       $module
     * @param int          $uid
     *
     * @return array|string
     */
    public function send($to, $template, $information, $module, $uid = 0)
    {
        // Set template
        $data = Pi::service('mail')->template(
            array(
                'file'      => $template,
                'module'    => $module,
            ),
            $information
        );

        // Set message
        $message = Pi::service('mail')->message(
            $data['subject'],
            $data['body'],
            $data['format']
        );
        $message->addTo($to);
        //$message->setEncoding("UTF-8");

        // Set as notification
        if (Pi::service('module')->isActive('message') && $uid > 0) {
            Pi::api('api', 'message')->notify(
                $uid,
                $data['body'],
                $data['subject']
            );
        }

        // Send mail
        try {
            return Pi::service('mail')->send($message);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get list of active modules
     *
     * @param string       $content
     * @param string       $number
     *
     * @return bool
     */
    public function smsToUser($content, $number = '')
    {
        if (!$this->active()) {
            return;
        }

        return Pi::api('sms', 'notification')->sendTouser($content, $number);
    }

    /**
     * Get list of active modules
     *
     * @param string       $content
     * @param string       $number
     *
     * @return bool
     */
    public function smsToAdmin($content, $number = '')
    {
        if (!$this->active()) {
            return;
        }

        return Pi::api('sms', 'notification')->sendToAdmin($content, $number);
    }

    /**
     * Google Firebase Cloud Messaging
     * more information on : https://firebase.google.com/docs/cloud-messaging
     *
     *
     *
     *
     * Notification is required for send message or notification as array
     *
     * $notification = array(
     *     'id'    => 123,
     *     'title' => 'my title',
     *     'body'  => 'my body',
     * );
     *
     *
     *
     *
     * You need update /var/config/service.notification.php and set server key / token,
     * Option not required , but you can set custom setting if needed
     *
     * $option = array(
     *     'priority'   => 'high',
     *     'serverKey'  => 'SET_SERVER_KEY_HERE',
     *     'token'      => '/topics/news',
     * );
     *
     *
     * @param $notification
     * @param $option
     * @return array
     */
    public function fcm($notification, $option = array())
    {
        // Set result
        $result = array(
            'status'   => 0,
            'message'  => '',
            'fields'   => '',
        );

        // Check option priority
        $option['priority'] = isset($option['priority']) ? $option['priority'] : 'high';

        // Get server key
        $option['serverKey'] = isset($option['serverKey']) ? $option['serverKey'] : $this->getOption('fcm_server_key');
        if (empty($option['serverKey'])) {
            $result['message'] = __('Server key not set');
            return $result;
        }

        // Get token or topic
        $option['token'] = isset($option['token']) ? $option['token'] : $this->getOption('fcm_token');
        if (empty($option['token'])) {
            $result['message'] = __('Token not set');
            return $result;
        }

        // APi url
        $url = 'https://fcm.googleapis.com/fcm/send';

        // Set field
        $fields = array(
            'priority'      => $option['priority'],
            'to'            => $option['token'],
            'notification'  => $notification,
        );

        // Send
        $config = array(
            'adapter'   => 'Zend\Http\Client\Adapter\Curl',
        );
        $client = new Client($url, $config);
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaderLine('Authorization', 'key=' . $option['serverKey']);
        $client->setMethod('POST');
        $client->setEncType('application/json');
        $client->setRawBody(json_encode($fields));
        $client->setHeaders($headers);
        $response = $client->send();
        if ($response->isSuccess()) {
            $result['status']   = 1;
            $result['message']  = __('Notification send successfully');
            $result['fields']   = $fields;
        } else {
            $result['message']  = __('Error to send notification');
        }
        return $result;
    }
}