<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * Comment specs
 *
 * @see Pi\Application\Installer\Resource\Comment
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return array(
    'article' => array(
        'title'         => _a('Article comments'),
        'icon'          => 'icon-post',
        'callback'      => 'Module\Comment\Api\Article',
        'locator'       => array(
            'controller'    => 'demo',
            'action'        => 'index',
            'identifier'    => 'id',
            'params'        => array(
                'enable'    => 'yes',
            ),
        ),
    ),
    'custom' => array(
        'title'     => _a('Custom comments'),
        'icon'      => 'icon-post',
        'callback'  => 'Module\Comment\Api\Custom',
        'locator'   => 'Module\Comment\Api\Custom',
    ),
);
