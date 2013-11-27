<?php
// Event service configuration

$config = array(
    // Listener list
    'listener' => array(
        array(
            // event info: module, event name
            'event'     => array('user', 'user_activate'),
            // listener callback: class, method
            'callback'  => array('Custom\User\Event', 'joinCommunity'),
        ),
    ),
);
return $config;
