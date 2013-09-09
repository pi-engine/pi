<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * Comment specs
 *
 * @see Pi\Application\Installer\Resource\Comment
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return array(
    'article' => array(
        'title' => __('Article comments'),
        'icon'  => 'icon-post',
        'callback'  => 'Module\Comment\Comment\Article',
        'route' => array(
            'controller'    => 'article',
            'action'        => 'index',
            'item'          => 'id',
            'params'        => array(
                // <param>      => <value>
            ),
        ),
    ),
);
