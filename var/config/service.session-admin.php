<?php
// Session service configuration for admin

$config = include __DIR__ . '/service.session.php';
$config['config']['options']['name'] = 'pisess-admin';
$config['config']['validators'] = array(
    'Zend\Session\Validator\HttpUserAgent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null,
    'Zend\Session\Validator\RemoteAddr'    => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
);
if (isset($config['config']['options']['remember_me_seconds'])) {
    unset($config['config']['options']['remember_me_seconds']);
}

return $config;
