<?php
// User avatar service configuration

return array(
    // GD 2
    'driver'        => 'gd',
    // ImageMagicK
    'driver'        => 'imagick',
    // GraphicMagicK
    'driver'        => 'gmagick',
    // Auto detected
    'driver'        => 'auto',

    // Source image for watermark generation
    'watermark'     => Pi::path('static/image/logo.png'),

    // Auto create path to save image
    'auto_mkdir'    => true,
);
