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

    'watermark'     => Pi::path('static/image/watermark.png'),
);
