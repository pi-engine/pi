<?php
// Image CAPTCHA specs

return array(
    'timeout'           => 300,
    'wordLen'           => 6,
    'width'             => 150,
    'height'            => 50,
    'dotNoiseLevel'     => 30,
    'lineNoiseLevel'    => 1,
    'font'              => Pi::path('static') . '/font/Vera.ttf',
    'fsize'             => 24,
    'imgUrl'            => Pi::url('script') . '/captcha.php',
    'useNumbers'        => true,
);
