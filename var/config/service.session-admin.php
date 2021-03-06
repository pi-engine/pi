<?php
// Session service configuration for admin

$config                              = include __DIR__ . '/service.session.php';
$config['config']['options']['name'] = 'pisess-admin';
$config['config']['validators']      = [
    'Laminas\Session\Validator\HttpUserAgent',
    'Laminas\Session\Validator\RemoteAddr',
];
if (isset($config['config']['options']['remember_me_seconds'])) {
    unset($config['config']['options']['remember_me_seconds']);
}

return $config;
