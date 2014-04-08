<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

return array(
    // Module meta
    'meta'  => array(
        // Module title, required
       'title'         => __('SAML Client'),
       // Description, for admin, optional
       'description'   => __('Client for Simplesamlphp client'),
       // Version number, required
       'version'       => '1.0.0',
       // Distribution license, required
       'license'       => 'New BSD',
       'icon'          => 'fa-key',
    ),
    // Author information
    'author'    => array(
       // Author full name, required
       'Dev'      => 'Wen Mingquan; Taiwen Jiang',
       // Email address, optional
       'Email'     => 'taiwenjiang@tsinghua.org.cn',
    ),
    'resource'  => array(
        'navigation'    => array(
            'front' => false,
        ),
    ),
);
