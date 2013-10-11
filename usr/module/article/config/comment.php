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
 * @author Zongshu Lin <lin40553024@163.com>
 */
return array(
    'article' => array(
        'title'     => __('Article comments'),
        'icon'      => 'icon-post',
        'callback'  => 'Module\Article\Comment\Article',
        'controller'    => 'article',
        'action'        => 'detail',
        'identifier'    => 'id',
    ),
);
