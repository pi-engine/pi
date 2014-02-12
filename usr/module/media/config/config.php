<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
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
            'title'       => _t('List Page Limitation'),
            'description' => _t('Maximum count of medias in a list page.'),
            'value'       => 20,
            'filter'      => 'number_int',
        ),
        'default_image'   => array(
            'category'    => 'general',
            'title'       => _t('Default Media Image'),
            'description' => _t('Path to default media image.'),
            'value'       => 'image/default-image.png',
        ),
        
        // Media
        'extension'       => array(
            'category'    => 'validator',
            'title'       => _t('Media Extension'),
            'description' => _t('Media types which can be uploaded.'),
            'value'       => 'pdf,rar,zip,doc,txt,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif',
        ),
        'max_size'        => array(
            'category'    => 'validator',
            'title'       => _t('Max Media Size'),
            'description' => _t('Max media size'),
            'value'       => 2097152,
            'filter'      => 'number_int',
        ),
        'image_width'     => array(
            'category'    => 'validator',
            'title'       => _t('Image Width'),
            'description' => _t('Max allowed image width'),
            'value'       => 1000,
            'filter'      => 'number_int',
        ),
        'image_height'    => array(
            'category'    => 'validator',
            'title'       => _t('Image Height'),
            'description' => _t('Max allowed image height'),
            'value'       => 1000,
            'filter'      => 'number_int',
        ),
    ),
);
