<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * Permission config
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
return [
    'admin' => [
        'article'         => [
            'title' => _t('Article management'),
        ],
        // Article author resource
        'author'          => [
            'title' => _t('Author management'),
        ],
        // Article category resource
        'category'        => [
            'title' => _t('Category management'),
        ],
        // Topic resource
        'topic'           => [
            'title' => _t('Topic management'),
        ],
        // Media resource
        'media'           => [
            'title' => _t('Media management'),
        ],
        // Article statistics resource
        'stats'           => [
            'title' => _t('Statistics page view'),
        ],
        // Module permission controller
        'permission'      => [
            'title' => _t('Permission management'),
        ],
        // Article configuration
        'setup'           => [
            'title' => _t('Setup management'),
        ],

        // Module resources
        'active'          => [
            'title' => _t('Published Active/Deactivate'),
        ],
        'publish-edit'    => [
            'title' => _t('Publish Edit'),
        ],
        'publish-delete'  => [
            'title' => _t('Published Delete'),
        ],
        'compose'         => [
            'title' => _t('Draft Compose'),
        ],
        'rejected-edit'   => [
            'title' => _t('Rejected Edit'),
        ],
        'rejected-delete' => [
            'title' => _t('Rejected Delete'),
        ],
        'pending-edit'    => [
            'title' => _t('Pending Edit'),
        ],
        'pending-delete'  => [
            'title' => _t('Pending Delete'),
        ],
        'approve'         => [
            'title' => _t('Pending Approve'),
        ],

        'custom' => 'Module\Article\Api\Permission',
    ],
];
