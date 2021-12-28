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
use Laminas\Http\Client;

/**
 * Notification service
 *
 * - Pi::service('notification')->send($to, $template, $information, $module, $uid);
 * - Pi::service('notification')->smsToUser($content, $number);
 * - Pi::service('notification')->smsToAdmin($content, $number);
 * - Pi::service('notification')->fcm($params, $option);
 *
 * - ToDo : user setting for active / inactive push notification on website and mobile
 * - ToDo : improve send sms on notification module and support local
 * - Todo : improve notification module to support custom notification for change module contents
 *
 * @author Hossein Azizabadi Farahani <hossein@azizabadi.com>
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
     * $params = array(
     *     'title'             => 'title,         // required
     *     'body'              => 'my message',   // required
     *     'image'             => 'image_url',    // Not required, set image as url
     *     'registration_ids'  => [],             // Array list of device token. use it or token
     *     'topic'             => '/topics/news', // if registration_ids you can send to topic
     *     'priority'          => 'high',         // Not required
     * );
     *
     * You need update /var/config/service.notification.php and set server key / token,
     * Option not required , but you can set custom setting if needed
     *
     * $option = array(
     *     'serverKey'        => 'SET_SERVER_KEY_HERE',
     * );
     *
     * @param $params
     * @param $option
     *
     * @return array
     */
    public function fcm($params, $option = [])
    {
        // Set result
        $result = [
            'status'   => 0,
            'message'  => '',
            'response' => [],
        ];

        // APi url
        $url = 'https://fcm.googleapis.com/fcm/send';

        // Get server key
        $option['serverKey'] = isset($option['serverKey']) ? $option['serverKey'] : $this->getOption('fcm_server_key');
        if (!isset($option['serverKey']) || empty($option['serverKey'])) {
            $result['message'] = __('Server key not set');
            return $result;
        }

        // Set id
        $params['id'] = isset($params['id']) ? $params['id'] : uniqid("fcm-");

        // Set priority
        $params['priority'] = isset($params['priority']) ? $params['priority'] : 'high';

        // Set ttl
        $params['ttl'] = isset($params['ttl']) ? $params['ttl'] : '86400';

        // Set registration_ids
        $params['registration_ids'] = isset($params['registration_ids']) ? $params['registration_ids'] : $this->getOption('fcm_registration_ids');
        $params['registration_ids'] = (!is_array($params['registration_ids'])) ? [$params['registration_ids']] : $params['registration_ids'];

        // Set topic
        $params['topic'] = isset($params['topic']) ? $params['topic'] : $this->getOption('fcm_token');

        // Set request
        $request = [
            'notification'      => [
                'title' => $params['title'],
                'body'  => $params['body'],
            ],
            'data'              => [
                'title' => $params['title'],
                'body'  => $params['body'],
            ],
            'content_available' => true,
            'priority'          => $params['priority'],
            'android'           => [
                'ttl'      => $params['ttl'] . 's',
                'priority' => $params['priority'],
            ],
            'apns'              => [
                'headers' => [
                    'apns-expiration' => '1604750400',
                    'apns-priority'   => ($params['priority'] === 'high') ? '10' : '5',
                ],
            ],
            'webpush'           => [
                'headers' => [
                    'TTL'     => $params['ttl'],
                    'Urgency' => $params['priority'],
                ],
            ],
        ];

        // Set image
        if (isset($params['image']) && !empty($params['image'])) {
            $request['data']['image']         = $params['image'];
            $request['notification']['image'] = $params['image'];
        }

        // Set data array if set
        if (isset($params['data']) && !empty($params['data'])) {
            $request['data'] = array_unique(array_merge($request['data'], $params['data']));
        }

        // Set registration_ids or to
        if (!empty($params['registration_ids'])) {
            $request['registration_ids'] = $params['registration_ids'];
        } elseif (!empty($params['topic'])) {
            $request['to'] = $params['topic'];
        } else {
            $result['message'] = __('Registration ids not set');
            return $result;
        }

        // Send
        $config  = [
            'adapter' => 'Laminas\Http\Client\Adapter\Curl',
        ];
        $client  = new Client($url, $config);
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaderLine('Authorization', 'key=' . $option['serverKey']);
        $client->setMethod('POST');
        $client->setEncType('application/json');
        $client->setRawBody(json_encode($request));
        $client->setHeaders($headers);
        $response = $client->send();

        // Check result
        if ($response->isSuccess()) {
            $result['status']   = 1;
            $result['message']  = __('Notification send successfully');
            $result['request']  = $request;
            $result['response'] = json_decode($response->getBody(), true);
        } else {
            $result['message']  = __('Error to send notification');
            $result['params']   = $params;
            $result['option']   = $option;
            $result['request']  = $request;
            $result['response'] = $response;
        }

        return $result;
    }
}
