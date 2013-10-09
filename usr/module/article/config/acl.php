<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

/**
 * Permission config
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
return array(
    /*'roles'      => array(
        // Front role for article module
        'article-manager' => array(
            'title'     => _t('Article Manager'),
        ),
        'contributor'     => array(
            'title'     => _t('Contributor'),
            'section'   => 'admin',
            'parents'   => array('staff'),
        )
    ),*/
    
    'resources'  => array(
        'admin'          => array(
            'article'    => array(
                'module'      => 'article',
                'title'       => _t('Article management'),
            ),
            // Article author resource
            'author'     => array(
                'module'      => 'article',
                'title'       => _t('Author management'),
            ),
            // Article category resource
            'category'   => array(
                'module'      => 'article',
                'title'       => _t('Category management'),
            ),
            // Topic resource
            'topic'      => array(
                'module'      => 'article',
                'title'       => _t('Topic management'),
            ),
            // Media resource
            'media'      => array(
                'module'      => 'article',
                'title'       => _t('Media management'),
            ),
            // Article statistics resource
            'statistics' => array(
                'module'      => 'article',
                'title'       => _t('Statistics page view'),
                'access'      => array(
                    //'contributor'  => 1,
                ),
            ),
            // Module permission controller
            'permission' => array(
                'module'      => 'article',
                'title'       => _t('Permission management'),
            ),
            // Article configuration
            'setup'      => array(
                'module'      => 'article',
                'title'       => _t('Setup management'),
            ),
        ),
    ),
    
    'exception'  => array(
        // AJAX action of author
        array(
            'controller'    => 'author',
            'action'        => 'save-image',
        ),
        array(
            'controller'    => 'author',
            'action'        => 'remove-image',
        ),
        // AJAX action of category
        array(
            'controller'    => 'category',
            'action'        => 'save-image',
        ),
        array(
            'controller'    => 'category',
            'action'        => 'remove-image',
        ),
        // AJAX action of topic
        array(
            'controller'    => 'topic',
            'action'        => 'save-image',
        ),
        array(
            'controller'    => 'topic',
            'action'        => 'remove-image',
        ),
        // AJAX action of media
        array(
            'controller'    => 'media',
            'action'        => 'upload',
        ),
        array(
            'controller'    => 'media',
            'action'        => 'remove',
        ),
        array(
            'controller'    => 'media',
            'action'        => 'save',
        ),
    ),
);
