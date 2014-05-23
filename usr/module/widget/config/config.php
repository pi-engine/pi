<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

return array(

    'file_max_size'     => array(
        'title'         => _t('Max file size'),
        'description'   => _t('Allowed image file to upload (in KB), 0 for no limit'),
        'value'         => 1024,
        'filter'        => 'int',
    ),

    'image_extension'   => array(
        'title'         => _t('File extensions'),
        'description'   => _t('Extensions for images to upload, separate `,`.'),
        'value'         => 'jpg,png,gif',
    ),

    'image_max_width'   => array(
        'title'         => _t('Max image width'),
        'description'   => _t('Allowed image width, 0 for no limit'),
        'value'         => 2048,
        'filter'        => 'int',
    ),

    'image_max_height'  => array(
        'title'         => _t('Max image height'),
        'description'   => _t('Allowed image height, 0 for no limit'),
        'value'         => 2048,
        'filter'        => 'int',
    ),

    'image_width_media' => array(
        'title'         => _t('Image width for media'),
        'description'   => _t('Default value for widgets'),
        'value'         => 150,
        'filter'        => 'int',
    ),

    'image_height_media'=> array(
        'title'         => _t('Image height for media'),
        'description'   => _t('Default value for widgets'),
        'value'         => 0,
        'filter'        => 'int',
    ),

    'image_width_carousel' => array(
        'title'         => _t('Image width for carousel'),
        'description'   => _t('Default value for widgets'),
        'value'         => 0,
        'filter'        => 'int',
    ),

    'image_height_carousel'=> array(
        'title'         => _t('Image height for carousel'),
        'description'   => _t('Default value for widgets'),
        'value'         => 200,
        'filter'        => 'int',
    ),

    'image_width_spotlight' => array(
        'title'         => _t('Image width for spotlight'),
        'description'   => _t('Default value for widgets'),
        'value'         => 400,
        'filter'        => 'int',
    ),

    'image_height_spotlight'=> array(
        'title'         => _t('Image height for spotlight'),
        'description'   => _t('Default value for widgets'),
        'value'         => 300,
        'filter'        => 'int',
    ),

    'target_new'        => array(
        'title'         => _t('Open new window'),
        'description'   => _t('Open new window for widgets not specified'),
        'edit'          => 'checkbox',
        'filter'        => 'int',
        'value'         => 0,
    ),
);
