<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * User profile and resource specs
 *
 * @see Pi\Application\Installer\Resource\User
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return array(
    // Activity
    'activity' => array(
        'post'    => array(
            'title' => __('Comment posts by me'),
            //'link'  => Pi::service('url')->assemble('default', array('module' => 'comment')),
            'icon'  => 'icon-post',
            'callback'  => 'Module\Comment\Comment\Post',
        ),
        'article'   => array(
            'title' => __('Comment posts on my articles'),
            //'link'  => Pi::service('url')->assemble('default', array('module' => 'comment')),
            'icon'  => 'icon-post',
            'callback'  => 'Module\Comment\Comment\Post',
        ),
    ),
);
