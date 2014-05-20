<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * Module config
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
return array(
    'category' => array(
        array(
            'name'  => 'general',
            'title' => _t('General'),
        ),
        array(
            'name'  => 'validator',
            'title' => _t('Validator'),
        ),
    ),

    'item' => array(
        // General
        'page_limit'      => array(
            'category'    => 'general',
            'title'       => _t('List page limit'),
            'description' => _t('Maximum count of media resources on a list page.'),
            'value'       => 20,
            'filter'      => 'int',
        ),
            /*
        'default_image'   => array(
            'category'    => 'general',
            'title'       => _t('Default media image'),
            'description' => _t('Path to default media image.'),
            'value'       => 'image/default-image.png',
        ),
        */
        
        // Media
        'extension'       => array(
            'category'    => 'validator',
            'title'       => _t('File extension'),
            'description' => _t('Extensions for files allowed to upload.'),
            'value'       => 'pdf,rar,zip,doc,txt,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif',
        ),
        'max_size'        => array(
            'category'    => 'validator',
            'title'       => _t('Max file size'),
            'description' => _t('Maximum size for files allowed to upload (in KB).'),
            'value'       => 2048,
            'filter'      => 'int',
        ),
        'image_width'     => array(
            'category'    => 'validator',
            'title'       => _t('Image width'),
            'description' => _t('Maximum image width for image files allowed to upload.'),
            'value'       => 1000,
            'filter'      => 'int',
        ),
        'image_height'    => array(
            'category'    => 'validator',
            'title'       => _t('Image height'),
            'description' => _t('Maximum image height for image files allowed to upload.'),
            'value'       => 1000,
            'filter'      => 'int',
        ),
    ),
);
