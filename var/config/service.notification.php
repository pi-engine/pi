<?php
/**
 * Notification service configuration
 */

return [
    /**
     * Google Firebase Cloud Messaging
     * more information on : https://firebase.google.com/docs/cloud-messaging
     * Get server key from : Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
     */

    // server key
    'fcm_server_key'       => '',

    // Token ro topic name like : '/topics/TOPIC_NAME'
    'fcm_token'            => '',

    // Single device tokens as array
    'fcm_registration_ids' => [],
];
