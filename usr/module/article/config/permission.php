<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * Permission config
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
return array(
    'admin'          => array(
        'article'    => array(
            'title'       => _t('Article management'),
        ),
        // Article author resource
        'author'     => array(
            'title'       => _t('Author management'),
        ),
        // Article category resource
        'category'   => array(
            'title'       => _t('Category management'),
        ),
        // Topic resource
        'topic'      => array(
            'title'       => _t('Topic management'),
        ),
        // Media resource
        'media'      => array(
            'title'       => _t('Media management'),
        ),
        // Article statistics resource
        'stats' => array(
            'title'       => _t('Statistics page view'),
        ),
        // Module permission controller
        'permission' => array(
            'title'       => _t('Permission management'),
        ),
        // Article configuration
        'setup'      => array(
            'title'       => _t('Setup management'),
        ),
        
        // Module resources
        'active'            => array(
            'title'       => _t('Published Active/Deactivate'),
        ),
        'publish-edit'      => array(
            'title'       => _t('Publish Edit'),
        ),
        'publish-delete'    => array(
            'title'       => _t('Published Delete'),
        ),
        'compose'           => array(
            'title'       => _t('Draft Compose'),
        ),
        'rejected-edit'     => array(
            'title'       => _t('Rejected Edit'),
        ),
        'rejected-delete'   => array(
            'title'       => _t('Rejected Delete'),
        ),
        'pending-edit'      => array(
            'title'       => _t('Pending Edit'),
        ),
        'pending-delete'    => array(
            'title'       => _t('Pending Delete'),
        ),
        'approve'           => array(
            'title'       => _t('Pending Approve'),
        ),
        
        'custom'         => 'Module\Article\Api\Permission',
    ),
);
