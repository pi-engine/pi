<?php
/**
 *  encryption service configuration
 */

return [
    'method'       => 'Blowfish',
    'key'          => '2468abcd',
    'iv'           => '2468abcd',
    'length'       => 128,
    'block_length' => 128,
    'mode'         => 'CBC',
];
