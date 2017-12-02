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
use Zend\Http\Client;

/**
 * Notification service
 *
 * - Pi::service('notification')->send($to, $template, $information, $module, $uid);
 * - Pi::service('notification')->smsToUser($content, $number);
 * - Pi::service('notification')->smsToAdmin($content, $number);
 * - Pi::service('notification')->fcm($data, $option);
 * - Pi::service('notification')->apns($data, $option);
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
     * @param string $template
     * @param array $information
     * @param string $module
     * @param int $uid
     *
     * @return array|string
     */
    public function send($to, $template, $information, $module, $uid = 0)
    {
        // Set template
        $data = Pi::service('mail')->template(
            [
                'file'   => $template,
                'module' => $module,
            ],
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
     * @param string $content
     * @param string $number
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
     * @param string $content
     * @param string $number
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
     * Notification is required for send message or notification as array
     *
     * $data = array(
     *     'message'           => 'my message', // Use it
     *     'id'                => 123,
     *     'title'             => 'my title',
     *     'body'              => 'my body',
     *     'registration_ids'  => [], // Array list of device token
     *     'token'             => '/topics/news', // if registration_ids you can send to topic
     * );
     *
     * You need update /var/config/service.notification.php and set server key / token,
     * Option not required , but you can set custom setting if needed
     *
     * $option = array(
     *     'priority'         => 'high',
     *     'serverKey'        => 'SET_SERVER_KEY_HERE',
     * );
     *
     * @param $data
     * @param $option
     * @return array
     */
    public function fcm($data, $option = [])
    {
        // Set result
        $result = [
            'status'  => 0,
            'message' => '',
            'data'    => '',
        ];

        // APi url
        $url = 'https://fcm.googleapis.com/fcm/send';

        // Set id
        $data['id'] = isset($data['id']) ? $data['id'] : uniqid("fcm-");

        // Check option priority
        $option['priority'] = isset($option['priority']) ? $option['priority'] : 'high';

        // Get server key
        $option['serverKey'] = isset($option['serverKey']) ? $option['serverKey'] : $this->getOption('fcm_server_key');
        if (empty($option['serverKey'])) {
            $result['message'] = __('Server key not set');
            return $result;
        }

        // Get registration_ids or topic
        $data['registration_ids'] = isset($data['registration_ids']) ? $data['registration_ids'] : $this->getOption('fcm_registration_ids');
        $data['registration_ids'] = (!is_array($data['registration_ids'])) ? [$data['registration_ids']] : $data['registration_ids'];
        $data['token']            = isset($data['token']) ? $data['token'] : $this->getOption('fcm_token');

        // Set field
        $fields = [
            'priority' => $option['priority'],
            'data'     => $data,
        ];

        if (!empty($data['registration_ids'])) {
            $fields['registration_ids'] = $data['registration_ids'];
        } elseif (!empty($data['token'])) {
            $fields['token'] = $data['token'];
        } else {
            $result['message'] = __('Registration ids not set');
            return $result;
        }

        // Send
        $config  = [
            'adapter' => 'Zend\Http\Client\Adapter\Curl',
        ];
        $client  = new Client($url, $config);
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaderLine('Authorization', 'key=' . $option['serverKey']);
        $client->setMethod('POST');
        $client->setEncType('application/json');
        $client->setRawBody(json_encode($fields));
        $client->setHeaders($headers);
        $response = $client->send();
        if ($response->isSuccess()) {
            $result['status']  = 1;
            $result['message'] = __('Notification send successfully');
            $result['data']    = [
                'response'         => json_decode($response->getBody(), true),
                'priority'         => $option['priority'],
                'registration_ids' => $data['registration_ids'],
            ];
        } else {
            $result['message'] = __('Error to send notification');
        }

        return $result;
    }


    /**
     * Apple Push Notification Service
     *
     * ToDo : finish Apple Push Notification Service
     *
     * @param $data
     * @param $option
     * @return array
     */
    public function apns($data, $option = [])
    {
        // Set result
        $result = [
            'status'  => 0,
            'message' => 'Apple Push Notification Service not finish yet',
            'data'    => $data,
        ];

        return $result;
    }
}