<?php

$customFile = Pi::path('custom/saml/authsources.php');
if (is_readable($customFile)) {
    include $customFile;
    return;
}

include __DIR__ . '/authsources.default.php';
return;
