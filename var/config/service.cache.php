<?php
// Cache service configuration

// Set up cache engine key, the key must match one of $config keys
$cache = 'filesystem';

if ('apc' == $cache && !extension_loaded('apc')) {
    $cache = 'filesystem';
}

$config = include __DIR__ . '/cache.' . $cache . '.php';
return $config;
