<?php

$customFile = Pi::path('config/custom/saml/metadata/saml20-idp-remote.php');
if (is_readable($customFile)) {
    include $customFile;
    return;
}

include __DIR__ . '/saml20-idp-remote.default.php';
return;